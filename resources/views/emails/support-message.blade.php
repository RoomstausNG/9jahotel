<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <title>New Support Request - 9jahotel</title>
    <style>
        body {
            font-family: 'Lato', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #f8f9fa;
            color: #222;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        .email-wrapper {
            background-color: #F4F4F4;
            padding: 20px 0;
        }

        .email-container {
            background: #fff;
            max-width: 600px;
            margin: 30px auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .email-header {
            background-color: #0C6E45;
            padding: 40px 30px 25px;
            text-align: center;
        }

        .email-header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
            line-height: 1.3;
        }

        .email-body {
            padding: 40px 30px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-section img {
            max-width: 220px;
            height: auto;
        }

        .support-title {
            color: #0C6E45;
            font-size: 22px;
            font-weight: 600;
            margin: 0 0 20px 0;
            text-align: center;
        }

        .support-intro {
            color: #666666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            text-align: center;
        }

        .support-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            border-left: 4px solid #0C6E45;
        }

        .detail-row {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }

        .message-section {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .message-label {
            font-weight: 600;
            color: #0C6E45;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .message-content {
            color: #333;
            font-size: 15px;
            line-height: 1.6;
            white-space: pre-line;
        }

        .email-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .footer-text {
            color: #888;
            font-size: 13px;
            margin: 0;
        }

        .action-note {
            background: #e7f3ff;
            border: 1px solid #b6d7ff;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #0066cc;
            font-size: 14px;
            text-align: center;
        }

        /* Responsive Design */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 0;
            }

            .email-header,
            .email-body {
                padding: 20px 15px;
            }

            .support-details {
                padding: 15px;
            }

            .support-title {
                font-size: 18px;
            }

            .email-header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <h1>New Support Request Received</h1>
            </div>

            <!-- Body -->
            <div class="email-body">
                <div class="logo-section">
                    <img src="https://9jahotel.com/img/logo.png" alt="9jahotel Logo">
                </div>

                <h2 class="support-title">Customer Support Request</h2>

                <p class="support-intro">
                    A new support request has been submitted through the website contact form.
                    Please review the details below and respond accordingly.
                </p>

                <div class="action-note">
                    <strong>Action Required:</strong> Please respond to this support request within 24 hours.
                </div>

                <!-- Customer Details -->
                <div class="support-details">
                    <div class="detail-row">
                        <div class="detail-label">Customer Name</div>
                        <div class="detail-value">{{ $name }}</div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Email Address</div>
                        <div class="detail-value">{{ $email }}</div>
                    </div>

                    @if ($phone)
                        <div class="detail-row">
                            <div class="detail-label">Phone Number</div>
                            <div class="detail-value">{{ $phone }}</div>
                        </div>
                    @endif

                    <div class="detail-row">
                        <div class="detail-label">Submitted On</div>
                        <div class="detail-value">{{ date('F j, Y \a\t g:i A') }}</div>
                    </div>
                </div>

                <!-- Message Content -->
                <div class="message-section">
                    <div class="message-label">Customer Message:</div>
                    <div class="message-content">{{ $messageText }}</div>
                </div>

                <p style="color: #666; font-size: 14px; margin-top: 30px; text-align: center;">
                    <em>This message was sent automatically from the 9jahotel support form.
                        Please do not reply directly to this email. Contact the customer using the provided email
                        address.</em>
                </p>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p class="footer-text">
                    &copy; {{ date('Y') }} 9jahotel. All rights reserved.<br>
                    This is an automated message from the support system.
                </p>
            </div>
        </div>
    </div>
</body>

</html>
