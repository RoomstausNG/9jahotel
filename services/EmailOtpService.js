import crypto from "crypto";
import nodemailer from "nodemailer";
import { createClient } from "redis";

export class EmailOtpService {
  /**
   * @param {object} options
   * @param {object} options.redisConfig - Redis client config (host, port, etc)
   * @param {object} options.mailConfig - Mailer config (transport options)
   */
  constructor({ redisConfig = {}, mailConfig = {} }) {
    // Setup Redis
    this.redis = createClient(redisConfig);
    this.redis.connect();

    // Setup Nodemailer
    this.transporter = nodemailer.createTransport(mailConfig);
    // OTP expiry in seconds (5 minutes)
    this.OTP_EXPIRY = 300;
  }

  /**
   * Generate a random 6-digit OTP
   * @returns {string}
   */
  generateOtp() {
    return "" + Math.floor(100000 + Math.random() * 900000);
  }

  /**
   * Send OTP to user's email
   * @param {string} email
   */
  async sendOtp(email) {
    const otp = this.generateOtp();
    const redisKey = `otp:${email}`;

    // Store OTP in Redis
    await this.redis.setEx(redisKey, this.OTP_EXPIRY, otp);

    // Send email
    const mailOptions = {
      from: this.transporter.options.auth.user,
      to: email,
      subject: "Your One-Time Password (OTP)",
      text: `Your OTP is: ${otp}\nIt is valid for 5 minutes.`,
    };
    await this.transporter.sendMail(mailOptions);
  }

  /**
   * Verify provided OTP for given email
   * @param {string} email
   * @param {string} otp
   * @returns {Promise<boolean>}
   */
  async verifyOtp(email, otp) {
    const redisKey = `otp:${email}`;
    const storedOtp = await this.redis.get(redisKey);
    if (!storedOtp) return false;
    if (storedOtp === otp) {
      // Remove OTP from Redis (OTP can be used only once)
      await this.redis.del(redisKey);
      return true;
    }
    return false;
  }
}

/* 
// Example usage (in your Honojs controller/service):
import { EmailOtpService } from './services/EmailOtpService.js';

const emailOtpService = new EmailOtpService({
  redisConfig: { url: "redis://localhost:6379" },
  mailConfig: {
    service: "Gmail",
    auth: {
      user: "your_email@gmail.com",
      pass: "your_app_password"
    }
  }
});
// To send OTP:
await emailOtpService.sendOtp('user@example.com');
// To verify OTP:
const isValid = await emailOtpService.verifyOtp('user@example.com', '123456');
*/
