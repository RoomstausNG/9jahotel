import * as nodemailer from "nodemailer";
import { readFileSync } from "fs";
import * as path from "path";
import * as Handlebars from "handlebars";
import { env } from "../config/env";
import { queueTemplateEmail } from "../jobs/email-queue";

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
      pool: true,
      host: env("SMTP_HOST"),
      port: 465,
      secure: true, // Use TLS
      maxConnections: 5, // Best practice default
      maxMessages: 100, // Best practice default
      auth: {
        user: env("SMTP_USER"),
        pass: env("SMTP_PASSWORD"),
      },
      tls: { rejectUnauthorized: false },
    } as nodemailer.TransportOptions);

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
      // Always add current year to context
      let contextWithYear = {
        ...options.context,
        year: new Date().getFullYear(),
      };
      const html = template(contextWithYear);

      // Send the email
      const result = await this.transporter.sendMail({
        from: '"9jahotel" <noreply@9jahotel.com>',
        to: options.to,
        subject: options.subject,
        html,
        envelope: {
          from: '"9jahotel" <noreply@9jahotel.com>',
          to: options.to,
        },
      });
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
   * Queues an email to be sent asynchronously (recommended for better performance)
   * @param options Email options including template name and context
   * @returns Promise resolving when email is added to queue
   */
  async queueTemplateEmail(options: EmailOptions) {
    await queueTemplateEmail(options);
    console.log(
      `📧 Email queued for ${options.to} with template ${options.templateName}`
    );
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
