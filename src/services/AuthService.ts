import { sign, verify } from "hono/jwt";
import { db } from "../config/database";
import { eq, sql } from "drizzle-orm";
import * as bcrypt from "bcryptjs";
import { customers } from "../database/migrations/schema";
import { env } from "../config/env";
import { CreateCustomerDtoType, CustomerResponse } from "../types/customer";
import { Context } from "hono";
import { getCookie, setCookie } from "hono/cookie";
import { clearAuthCookie } from "../utils/clearAuthCookie";
import {
  PasswordUpdateDtoType,
  LocationUpdateDtoType,
  ProfileUpdateDtoType,
} from "../dtos/UserDTO";
import { getToken } from "../utils/getToken";
import { redis } from "../config/redis"; // Import Redis client
import { EmailService } from "./EmailService"; // Import EmailService
import { inject, injectable } from "inversify"; // Import injectable
import { ResetPasswordDtoType, SocialLoginDtoType } from "../dtos/auth-dto"; // Import ResetPasswordDtoType

import { OAuth2Client } from "google-auth-library";
import appleSigninAuth from "apple-signin-auth";
import axios from "axios";
import cloudinary from "../config/cloudinary";
import { uploadAndTransformImage } from "../utils/cloudinaryUtils";

const googleClient = new OAuth2Client(env("GOOGLE_CLIENT_ID"));

const JWT_SECRET = env("JWT_SECRET", "9jahotel");
const COOKIE_NAME = "auth_token";
const COOKIE_MAX_AGE = 60 * 60 * 24; // 24 hours in seconds
const OTP_EXPIRY_SECONDS = 60 * 10; // 10 minutes
const OTP_KEY_PREFIX = "otp:"; // Prefix for Redis keys

@injectable()
export class AuthService {
  constructor(@inject(EmailService) private emailService: EmailService) {} // Inject EmailService
  private _generateRandomCode(length: number): string {
    const characters = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
    let result = "";
    const charactersLength = characters.length;
    for (let i = 0; i < length; i++) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
  }

  private _generateOtp(length: number = 6): string {
    // Generate a simple numeric OTP
    const digits = "0123456789";
    let otp = "";
    for (let i = 0; i < length; i++) {
      otp += digits[Math.floor(Math.random() * 10)];
    }
    return otp;
  }

  async register(data: CreateCustomerDtoType, c?: Context) {
    try {
      // Hash password and replace $2b$ with $2y$ for Laravel compatibility
      let hashedPassword = bcrypt.hashSync(data.password, 12);
      if (hashedPassword.startsWith("$2b$")) {
        hashedPassword = "$2y$" + hashedPassword.slice(4);
      }

      // Generate referral code once
      const referralCode = this._generateRandomCode(8);

      // Insert customer
      const result = await db.insert(customers).values({
        firstname: data.firstname,
        lastname: data.lastname,
        phone: data.phone,
        email: data.email,
        password: hashedPassword,
        parent_id: data.referral_code,
        referral_code: referralCode,
      });

      // newly created customerId
      const customerId = result[0].insertId;

      // Get the created customer to verify
      const customer = await AuthService.getUserById(customerId);

      // Generate token and set cookie if context is provided
      if (customer && c) {
        const token = await this.generateToken(customer);
        this.setAuthCookie(c, token);
      }

      return customer;
    } catch (error) {
      console.error("Failed to register customer:", error);
      throw new Error(
        `Registration failed: ${
          error instanceof Error ? error.message : "Unknown error"
        }`
      );
    }
  }

  async login(email: string, password: string, c?: Context) {
    try {
      const [user] = await this.getUserByEmail(email);

      if (!user || !(await bcrypt.compare(password, user.password as string))) {
        throw new Error("Incorrect password");
      }

      // Increment token_version for this user on login
      await db
        .update(customers)
        .set({ token_version: sql.raw("token_version + 1") })
        .where(eq(customers.id, user.id));

      // Fetch the updated user with new token_version
      const updatedUser = await AuthService.getUserById(user.id);

      // Generate token with updated token version
      const token = await this.generateToken(updatedUser);

      // Set cookie if context is provided
      if (c) {
        this.setAuthCookie(c, token);
      }

      return { user: updatedUser, token };
    } catch (error: any) {
      throw new Error(error.message);
    }
  }

  static async getAuthUser(tokenOrContext: string | Context) {
    try {
      let token: string;
      if (typeof tokenOrContext === "string") {
        token = tokenOrContext;
      } else {
        token = getToken(tokenOrContext) as string;
        if (!token) return null;
      }
      const payload = await verify(token, JWT_SECRET);
      const customer = await AuthService.getUserById(payload.id as number);

      // Debug: log both versions for troubleshooting
      console.log(
        "Token tokenVersion:",
        payload.tokenVersion,
        "DB tokenVersion:",
        customer?.tokenVersion
      );

      // This check is CRUCIAL! It ensures tokens are invalidated when tokenVersion changes
      if (
        !customer ||
        Number(customer.tokenVersion) !== Number(payload.tokenVersion)
      ) {
        console.log(
          "Token invalidated due to version mismatch or missing customer"
        );
        return null;
      }
      return customer;
    } catch (error) {
      console.error("Auth token verification failed:", error);
      return null;
    }
  }
  async logout(c: Context) {
    try {
      // Try to get token from cookie
      let token = getCookie(c, COOKIE_NAME) || getToken(c);

      if (token) {
        const payload = await verify(token, JWT_SECRET);
        // Increment token_version for this user
        await db
          .update(customers)
          .set({ token_version: sql.raw("token_version + 1") })
          .where(eq(customers.id, payload.id as number));
      } else {
        console.warn(
          "No token found in cookie or Authorization header during logout."
        );
      }

      // Robustly clear the auth cookie
      clearAuthCookie(c, COOKIE_NAME);

      return { success: true };
    } catch (error) {
      console.error("Logout failed:", error);
      throw new Error("Logout failed");
    }
  }

  async updatePassword(c: Context, data: PasswordUpdateDtoType) {
    try {
      const token = getToken(c);
      const user =
        c.get("user") ?? (await AuthService.getAuthUser(token as string));

      if (!user) {
        throw new Error("User not authenticated");
      }

      // 1. Fetch the user's current hashed password from DB
      const [dbUser] = await db
        .select({ password: customers.password })
        .from(customers)
        .where(eq(customers.id, user.id))
        .limit(1);

      if (!dbUser) {
        throw new Error("User not found");
      }

      // 2. Compare provided currentPassword with hashed password
      const isMatch = await bcrypt.compare(
        data.currentPassword,
        dbUser.password as string
      );

      if (!isMatch) {
        throw new Error("Current password is incorrect");
      }

      // Hash and update new password, replace $2b$ with $2y$ for Laravel compatibility
      let hashedPassword = bcrypt.hashSync(data.newPassword, 12);
      if (hashedPassword.startsWith("$2b$")) {
        hashedPassword = "$2y$" + hashedPassword.slice(4);
      }

      await db
        .update(customers)
        .set({ password: hashedPassword })
        .where(eq(customers.id, user.id));

      return user;
    } catch (error) {
      throw new Error(
        `Failed to update password: ${
          error instanceof Error ? error.message : "Unknown error"
        }`
      );
    }
  }

  async updateLocation(data: LocationUpdateDtoType, c: Context) {
    try {
      const token = getToken(c);
      const user =
        c.get("user") ?? (await AuthService.getAuthUser(token as string));

      if (!user) {
        throw new Error("User not authenticated");
      }

      const update = await db
        .update(customers)
        .set({
          longitude: data.longitude as unknown as string,
          latitude: data.latitude as unknown as string,
        })
        .where(eq(customers.id, user.id));

      const updatedUser = await AuthService.getUserById(user.id);

      console.log([data, update[0], updatedUser]);
      c.set("user", updatedUser);
      return updatedUser;
    } catch (error) {
      throw new Error(
        `Failed to update password: ${
          error instanceof Error ? error.message : "Unknown error"
        }`
      );
    }
  }

  // Renamed and implemented method for avatar upload
  async updateAvatar(file: any, c: Context) {
    try {
      const token = getToken(c);
      const user =
        c.get("user") ?? (await AuthService.getAuthUser(token as string));

      if (!user) {
        throw new Error("User not authenticated");
      }

      let fileBuffer: Buffer;
      if (file.stream) {
        // Adjust based on actual file object structure
        const chunks: Buffer[] = [];
        for await (const chunk of file.stream()) {
          chunks.push(Buffer.from(chunk));
        }
        fileBuffer = Buffer.concat(chunks);
      } else if (file.buffer) {
        fileBuffer = file.buffer;
      } else {
        throw new Error("Unsupported file format received from middleware.");
      }

      // Upload options with transformations - Use a STABLE public_id
      const uploadOptions = {
        folder: `avatars/${user.id}`,
        public_id: user.referralCode ?? user.id + "_avatar",
        overwrite: true,
        transformation: [
          { width: 300, height: 300, gravity: "face", crop: "thumb" },
          { quality: "auto:good" },
          { fetch_format: "auto" },
        ],
      };

      // Perform the upload using upload_stream for buffers
      // const uploadResult = await new Promise<any | undefined>(
      //   (resolve, reject) => {
      //     const uploadStream = cloudinary.uploader.upload_stream(
      //       uploadOptions,
      //       (error, result) => {
      //         if (error) {
      //           console.error("Cloudinary Upload Error:", error);
      //           return reject(
      //             new Error("Failed to upload image to Cloudinary.")
      //           );
      //         }
      //         resolve(result);
      //       }
      //     );
      //     uploadStream.end(fileBuffer);
      //   }
      // );

      // if (!uploadResult || !uploadResult.secure_url) {
      //   throw new Error(
      //     "Cloudinary upload failed or did not return a secure URL."
      //   );
      // }

      const cloudinaryResponse = await uploadAndTransformImage(fileBuffer, {
        folder: `avatars/${user.id}`,
        public_id: user.referralCode ?? user.id + "_avatar",
      });

      const avatarUrl = cloudinaryResponse.secure_url;

      await db
        .update(customers)
        .set({
          avatar: avatarUrl,
          updated_at: new Date().toISOString().slice(0, 19).replace("T", " "), // Update timestamp
        })
        .where(eq(customers.id, user.id));

      // Fetch updated user data
      const updatedUser = await AuthService.getUserById(user.id);
      c.set("user", updatedUser);

      console.log(`Avatar updated for user ${user.id}: ${avatarUrl}`);
      return updatedUser; // Return updated user profile
    } catch (error) {
      console.error("Failed to update avatar:", error);
      throw new Error(
        `Failed to update avatar: ${
          error instanceof Error ? error.message : "Unknown error"
        }`
      );
    }
  }

  async updateProfile(data: Partial<ProfileUpdateDtoType>, c: Context) {
    // Use Partial<> to reflect optional nature
    try {
      const token = getToken(c);
      const user =
        c.get("user") ?? (await AuthService.getAuthUser(token as string));

      if (!user) {
        throw new Error("User not authenticated");
      }

      // Prepare the update payload dynamically
      const updatePayload: Record<string, any> = {}; // Use a flexible record type

      // Conditionally add fields to the payload if they are defined in 'data'
      if (data.firstname !== undefined)
        updatePayload.firstname = data.firstname;
      if (data.lastname !== undefined) updatePayload.lastname = data.lastname;
      if (data.gender !== undefined) updatePayload.gender = data.gender;
      if (data.address !== undefined) updatePayload.address = data.address;
      if (data.cityId !== undefined) updatePayload.city_id = data.cityId;
      if (data.idType !== undefined) updatePayload.id_type = data.idType;
      if (data.idNumber !== undefined) updatePayload.id_number = data.idNumber;
      // Restore latitude/longitude handling if they are part of ProfileUpdateDto
      if (data.latitude !== undefined)
        updatePayload.latitude = data.latitude as unknown as string;
      if (data.longitude !== undefined)
        updatePayload.longitude = data.longitude as unknown as string;

      // Handle date formatting specifically
      if (data.dob instanceof Date) {
        // Format Date to 'YYYY-MM-DD' string only if it's a valid Date
        updatePayload.date_of_birth = data.dob.toISOString().split("T")[0];
      } else if (data.dob === null) {
        // Allow explicitly setting dob to null if needed
        updatePayload.date_of_birth = null;
      }
      // If data.dob is undefined, it's skipped

      // Check if there's anything to update
      if (Object.keys(updatePayload).length === 0) {
        console.log("No profile fields provided for update.");
        // Return the current user data as nothing changed
        return await AuthService.getUserById(user.id);
      }

      // Add updated_at timestamp
      updatePayload.updated_at = new Date()
        .toISOString()
        .slice(0, 19)
        .replace("T", " ");

      // Perform the update only with the provided fields
      const updateResult = await db
        .update(customers)
        .set(updatePayload) // Use the dynamically built payload
        .where(eq(customers.id, user.id))
        .limit(1);

      const updatedUser = await AuthService.getUserById(user.id);

      c.set("user", updatedUser);
      return updatedUser;
    } catch (error) {
      console.error("Failed to update profile:", error); // More specific logging
      throw new Error(
        `Failed to update profile: ${
          // More specific error message
          error instanceof Error ? error.message : "Unknown error"
        }`
      );
    }
  }

  // get user by Id
  static async getUserById(id: number) {
    try {
      const [customer] = await db
        .select({
          id: customers.id,
          firstName: customers.firstname,
          lastName: customers.lastname,
          referralCode: customers.referral_code,
          email: customers.email,
          phone: customers.phone,
          parentId: customers.parent_id,
          avatar: customers.avatar,
          address: customers.address,
          latitude: customers.latitude,
          longitude: customers.longitude,
          tokenVersion: customers.token_version,
        })
        .from(customers)
        .where(eq(customers.id, Number(id)))
        .limit(1);

      return customer || null;
    } catch (error) {
      throw new Error(
        `Failed to retrieve customer: ${
          error instanceof Error ? error.message : "Unknown error"
        }`
      );
    }
  }
  // Helper method to generate JWT token
  private async generateToken(customer: any) {
    // Calculate expiration time (1 day from now)
    const expirationTime = Math.floor(Date.now() / 1000) + COOKIE_MAX_AGE;

    const payload = {
      id: customer.id,
      email: customer.email,
      tokenType: "Bearer",
      tokenVersion: customer.tokenVersion || 0, // Always use camelCase
      exp: expirationTime,
    };
    // Generate JWT token with customer data
    return sign(payload, JWT_SECRET);
  }

  async getUserByEmail(email: string) {
    return await db
      .select()
      .from(customers)
      .where(eq(customers.email, email))
      .limit(1);
  }

  // Helper method to set auth cookie
  private setAuthCookie(c: Context, token: string) {
    setCookie(c, COOKIE_NAME, token, {
      maxAge: COOKIE_MAX_AGE,
      path: "/",
      httpOnly: true, // Prevents JavaScript access
      secure: process.env.NODE_ENV === "production", // HTTPS only in production
      sameSite: "strict", // Prevents CSRF
    });
  }

  async sendOtp(email: string): Promise<{ success: boolean; message: string }> {
    try {
      const [user] = await this.getUserByEmail(email);
      if (!user) {
        // Avoid enumeration attacks by pretending success
        console.warn(`OTP request for non-existent email: ${email}`);
        return {
          success: true,
          message: "If your email is registered, you will receive an OTP.",
        };
      }

      const otp = this._generateOtp();
      const redisKey = `${OTP_KEY_PREFIX}${email}`;

      // Store OTP in Redis with expiry
      await redis.set(redisKey, otp, "EX", OTP_EXPIRY_SECONDS);

      // Send OTP email using EmailService
      await this.emailService.sendTemplateEmail({
        to: email,
        subject: "Your Password Reset OTP",
        templateName: "otp-verification", // Ensure this template exists
        context: {
          otp,
          expiryMinutes: OTP_EXPIRY_SECONDS / 60,
        },
      });

      console.log(`OTP sent to ${email}`);
      return { success: true, message: "OTP sent successfully." };
    } catch (error) {
      console.error(`Failed to send OTP to ${email}:`, error);
      throw new Error("Failed to process OTP request.");
    }
  }

  async resetPassword(
    data: ResetPasswordDtoType
  ): Promise<{ success: boolean; message: string }> {
    try {
      const { email, otp, password } = data;
      const redisKey = `${OTP_KEY_PREFIX}${email}`;

      // 1. Verify OTP
      const storedOtp = await redis.get(redisKey);
      if (!storedOtp) {
        throw new Error("OTP expired or invalid.");
      }
      if (storedOtp !== otp) {
        throw new Error("Incorrect OTP.");
      }

      // 2. Find user by email
      const [user] = await this.getUserByEmail(email);
      if (!user) {
        // Should ideally not happen if OTP was valid, but check anyway
        throw new Error("User not found.");
      }

      // 3. Hash the new password and replace $2b$ with $2y$ for Laravel compatibility
      let hashedPassword = bcrypt.hashSync(password, 12);
      if (hashedPassword.startsWith("$2b$")) {
        hashedPassword = "$2y$" + hashedPassword.slice(4);
      }

      // 4. Update password and increment token_version
      await db
        .update(customers)
        .set({
          password: hashedPassword,
          token_version: sql.raw("token_version + 1"), // Invalidate old sessions
        })
        .where(eq(customers.id, user.id));

      // 5. Delete OTP from Redis now that it's used
      await redis.del(redisKey);

      console.log(`Password reset successfully for ${email}`);
      return { success: true, message: "Password reset successfully." };
    } catch (error) {
      console.error(`Failed to reset password for user}:`, error);
      throw new Error(
        `Password reset failed: ${
          error instanceof Error ? error.message : "Unknown error"
        }`
      );
    }
  }

  async socialLogin(data: SocialLoginDtoType, c?: Context) {
    try {
      let userInfo;
      switch (data.provider) {
        case "google":
          userInfo = await AuthService.verifyGoogleToken(data.token);
          break;
        case "facebook":
          userInfo = await AuthService.verifyFacebookToken(data.token);
          break;
        case "apple":
          userInfo = await AuthService.verifyAppleToken(data.token);
          break;
        default:
          throw new Error("Invalid provider");
      }

      console.log(userInfo);

      // Check if user exists
      let [user] = await this.getUserByEmail(userInfo.email);

      if (!user) {
        throw new Error("User not registered. Please sign up first.");
      }

      // Generate token and set cookie
      const token = await this.generateToken(user);
      if (c) {
        this.setAuthCookie(c, token);
      }

      return { user, token };
    } catch (error) {
      throw new Error(
        `Social login failed: ${
          error instanceof Error ? error.message : "Unknown error"
        }`
      );
    }
  }
  static async verifyGoogleToken(token: string) {
    try {
      const ticket = await googleClient.verifyIdToken({
        idToken: token,
        audience: env("GOOGLE_CLIENT_ID"),
      });

      const payload = ticket.getPayload();
      if (!payload) throw new Error("Invalid Google token");

      return {
        id: payload.sub,
        email: payload.email,
        name: payload.name,
        picture: payload.picture,
      };
    } catch {
      throw new Error("Failed to verify Google token");
    }
  }

  static async verifyFacebookToken(token: string) {
    try {
      const appAccessToken = `${env("FB_APP_ID")}|${env("FB_APP_SECRET")}`;
      const debugUrl = `https://graph.facebook.com/debug_token?input_token=${token}&access_token=${appAccessToken}`;
      const debugRes = await axios.get(debugUrl);

      const data = debugRes.data.data;
      if (!data.is_valid || data.app_id !== env("FB_APP_ID")) {
        throw new Error("Invalid Facebook token");
      }

      const userInfoUrl = `https://graph.facebook.com/me?fields=id,name,email,picture&access_token=${token}`;
      const userInfoRes = await axios.get(userInfoUrl);

      return {
        id: userInfoRes.data.id,
        email: userInfoRes.data.email,
        name: userInfoRes.data.name,
        picture: userInfoRes.data.picture?.data?.url,
      };
    } catch {
      throw new Error("Failed to verify Facebook token");
    }
  }

  static async verifyAppleToken(token: string) {
    try {
      const payload = await appleSigninAuth.verifyIdToken(token, {
        audience: env("APPLE_CLIENT_ID"),
        ignoreExpiration: false,
      });

      return {
        id: payload.sub,
        email: payload.email,
      };
    } catch {
      throw new Error("Failed to verify Apple token");
    }
  }
}

// You might need a helper function like this if your middleware provides a stream
// Place this in a utils file, e.g., src/utils/streamToBuffer.ts
/*
import { Readable } from 'stream';

export async function streamToBuffer(stream: Readable): Promise<Buffer> {
  return new Promise((resolve, reject) => {
    const chunks: Buffer[] = [];
    stream.on('data', (chunk) => chunks.push(chunk));
    stream.on('error', reject);
    stream.on('end', () => resolve(Buffer.concat(chunks)));
  });
}
*/
