// ...existing code...

// Delete account route

import { zValidator } from "./../validators/validator-wrapper";
import { Hono } from "hono";
import { AuthController } from "../controllers/AuthController";
import { container } from "../container";
import {
  authMiddleware,
  currentUserMiddleware,
} from "../middlewares/auth-middleware";

import {
  CreateUserDto,
  LoginDto,
  PasswordUpdateDto,
  ProfileUpdateDto,
  SocialLoginDto,
} from "../dtos/UserDTO";
import { SendOtpDto, VerifyOtpDto, ResetPasswordDto } from "../dtos/auth-dto";

const authRoutes = new Hono();

const authController = container.get(AuthController);

// Customer registration and authentication routes
authRoutes.post("/register", zValidator("json", CreateUserDto), (c) =>
  authController.register(c)
);

authRoutes.delete("/delete-account", authMiddleware, (c) =>
  authController.deleteAccount(c)
);

authRoutes.post("/login", zValidator("json", LoginDto), (c) =>
  authController.login(c)
);

authRoutes.post("/logout", authMiddleware, (c) => authController.logout(c));

authRoutes.get("/profile", authMiddleware, (c) => authController.profile(c));

authRoutes.patch(
  "/profile/update",
  authMiddleware,
  zValidator("json", ProfileUpdateDto),
  (c) => authController.updateProfile(c)
);

authRoutes.patch(
  "/update-password",
  authMiddleware,
  zValidator("json", PasswordUpdateDto),
  (c) => authController.updatePassword(c)
);

// Change route for avatar update
authRoutes.post(
  "/profile/avatar",
  authMiddleware,
  (c) => authController.updateAvatar(c) // Point to the new controller method
);

authRoutes.post("/social-login", zValidator("json", SocialLoginDto), (c) =>
  authController.socialLogin(c)
);

// Route to request password reset OTP
authRoutes.post("/send-otp", zValidator("json", SendOtpDto), (c) =>
  authController.sendOtp(c)
);

// New route to verify OTP
authRoutes.post("/verify-otp", zValidator("json", VerifyOtpDto), (c) =>
  authController.verifyOtp(c)
);

// Route to reset password (now without OTP)
authRoutes.patch("/reset-password", zValidator("json", ResetPasswordDto), (c) =>
  authController.resetPassword(c)
);

export default authRoutes;
// Delete account route
authRoutes.delete("/delete-account", authMiddleware, (c) =>
  authController.deleteAccount(c)
);
