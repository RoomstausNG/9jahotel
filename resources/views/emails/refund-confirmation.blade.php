<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Request Received</title>
    <style>
        body {
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px #e9ecef;
        }

        .email-header {
            background: #0C6E45;
            color: #fff;
            padding: 32px 30px 18px 30px;
            text-align: center;
        }

        .email-header img {
            max-width: 120px;
            margin-bottom: 10px;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .email-body {
            padding: 32px 30px 24px 30px;
            color: #333;
        }

        .email-body p {
            font-size: 16px;
            line-height: 1.7;
            margin: 0 0 18px 0;
        }

        .panel {
            background: #f8f9fa;
            border-left: 4px solid #0C6E45;
            padding: 18px 20px;
            margin: 24px 0;
            border-radius: 6px;
        }

        .panel-title {
            font-weight: 700;
            color: #0C6E45;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .panel-list {
            margin: 0;
            padding: 0 0 0 18px;
            font-size: 15px;
        }

        .email-footer {
            background: #f4f4f4;
            color: #888;
            text-align: center;
            padding: 18px 30px;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 0;
            }

            .email-header,
            .email-body,
            .email-footer {
                padding: 18px 10px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <img src="https://9jahotel.com/img/9jahotel_logo.png" alt="9jaHotel Logo">
            <h1>Refund Request Received</h1>
        </div>
        <div class="email-body">
            <p>Dear {{ $customer->name }},</p>
            <p>We have received your refund request for your booking at <strong>{{ $hotel->name }}</strong> (Reference:
                <strong>{{ $invoice->reference }}</strong>).</p>
            <p>Our customer care team is processing your request and your refund will be sent to the specified account
                within 12 hours of completing the refund process.</p>
            <p>If you have any questions, please reply to this email or contact our support team.</p>
            <div class="panel">
                <div class="panel-title">Booking Details:</div>
                <ul class="panel-list">
                    <li><strong>Hotel:</strong> {{ $hotel->name }}</li>
                    <li><strong>Check-in:</strong> {{ $invoice->checked_in->format('M d, Y') }}</li>
                    <li><strong>Check-out:</strong> {{ $invoice->checked_out->format('M d, Y') }}</li>
                    <li><strong>Amount Paid:</strong> ₦{{ number_format($invoice->amount) }}</li>
                </ul>
            </div>
            <p>Thank you for choosing 9jaHotel.</p>
            <p style="margin-top: 32px;">Best regards,<br>9jaHotel Customer Care</p>
        </div>
        <div class="email-footer">
            &copy; {{ date('Y') }} 9jaHotel. All rights reserved.
        </div>
    </div>
</body>

</html>
