import * as nodemailer from "nodemailer";
import { readFileSync } from "fs";
import * as path from "path";
import * as Handlebars from "handlebars";
import { env } from "../config/env";

interface EmailOptions {
  to: string;
  subject: string;
  templateName: string;
  context: Record<string, any>;
}

export class EmailService {
  private transporter: nodemailer.Transporter;
  private templatesDir: string;

  constructor() {
    // Initialize the email transporter
    this.transporter = nodemailer.createTransport({
      host: env("SMTP_HOST", "smtp.example.com"),
      port: parseInt(env("SMTP_PORT", "587")),
      secure: env("SMTP_SECURE", "false"),
      auth: {
        user: env("SMTP_USER", ""),
        pass: env("SMTP_PASSWORD", ""),
      },
    });

    // Set the templates directory path
    this.templatesDir = path.join(process.cwd(), "src/templates/emails");
  }

  /**
   * Sends an email using a template
   * @param options Email options including template name and context
   * @returns Promise resolving to the nodemailer send info
   */
  async sendTemplateEmail(options: EmailOptions) {
    try {
      // Load the template file
      const templatePath = path.join(
        this.templatesDir,
        `${options.templateName}.hbs`
      );
      const templateSource = readFileSync(templatePath, "utf8");

      // Compile the template with Handlebars
      const template = Handlebars.compile(templateSource);
      const html = template(options.context);

      // Send the email
      const result = await this.transporter.sendMail({
        from: env("EMAIL_FROM", "noreply@example.com"),
        to: options.to,
        subject: options.subject,
        html,
      });

      console.log(`Email sent to ${options.to}: ${result.messageId}`);
      return result;
    } catch (error) {
      console.error("Failed to send email:", error);
      throw new Error(
        `Failed to send email: ${
          error instanceof Error ? error.message : "Unknown error"
        }`
      );
    }
  }

  /**
   * Sends a welcome email to a newly registered user
   * @param email User's email address
   * @param firstName User's first name
   * @returns Promise resolving to the nodemailer send info
   */
  async sendWelcomeEmail(email: string, firstName: string) {
    return this.sendTemplateEmail({
      to: email,
      subject: "Welcome to 9jaHotel!",
      templateName: "welcome",
      context: {
        firstName,
        loginUrl: env("FRONTEND_URL", "https://example.com") + "/login",
      },
    });
  }

  /**
   * Sends a password reset email
   * @param email User's email address
   * @param resetToken Password reset token
   * @returns Promise resolving to the nodemailer send info
   */
  async sendPasswordResetEmail(email: string, resetToken: string) {
    const resetUrl = `${env(
      "FRONTEND_URL",
      "https://example.com"
    )}/reset-password?token=${resetToken}`;

    return this.sendTemplateEmail({
      to: email,
      subject: "Reset Your Password",
      templateName: "password-reset",
      context: {
        resetUrl,
        expiryHours: 24, // Token validity in hours
      },
    });
  }
}
