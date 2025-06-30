<!-- Responsive Email Styles - Include in email templates -->
<style type="text/css">
    /* Base styles */
    body {
        font-family: Arial, sans-serif;
        background: #fff;
        padding: 0;
        margin: 0;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }

    /* Container */
    .email-container {
        max-width: 650px;
        margin: 0 auto;
        background: #fff;
        padding: 32px;
    }

    /* Typography */
    h1 {
        color: #d60000;
        text-align: center;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .booking-number {
        text-align: center;
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
    }

    .booking-number span {
        color: #d60000;
        font-weight: bold;
    }

    .thank-you {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    /* Booking details */
    .booking-details {
        display: flex;
        gap: 24px;
        align-items: flex-start;
    }

    .booking-details-col {
        flex: 1;
    }

    .booking-detail-item {
        margin-bottom: 10px;
    }

    .booking-detail-label {
        font-weight: bold;
        display: block;
    }

    /* Tables */
    .booking-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .booking-table th,
    .booking-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .booking-table th {
        font-weight: bold;
        background-color: #f9f9f9;
    }

    /* Mobile Responsive Styles */
    @media screen and (max-width: 600px) {
        .email-container {
            width: 100% !important;
            padding: 15px !important;
        }

        h1 {
            font-size: 1.5rem !important;
        }

        .booking-details {
            display: block !important;
        }

        .booking-details-col {
            width: 100% !important;
            margin-bottom: 20px !important;
        }

        .booking-table {
            font-size: 14px !important;
        }

        /* Convert tables to blocks on mobile */
        .booking-table,
        .booking-table thead,
        .booking-table tbody,
        .booking-table th,
        .booking-table td,
        .booking-table tr {
            display: block !important;
        }

        .booking-table thead tr {
            position: absolute !important;
            top: -9999px !important;
            left: -9999px !important;
        }

        .booking-table tr {
            margin-bottom: 15px !important;
            border: 1px solid #eee !important;
        }

        .booking-table td {
            border: none !important;
            position: relative !important;
            padding-left: 50% !important;
            text-align: right !important;
            min-height: 30px !important;
        }

        .booking-table td:before {
            position: absolute !important;
            top: 10px !important;
            left: 10px !important;
            width: 45% !important;
            padding-right: 10px !important;
            white-space: nowrap !important;
            font-weight: bold !important;
            text-align: left !important;
        }

        /* Add data labels for mobile view */
        .booking-table td:nth-of-type(1):before {
            content: "Room Type";
        }

        .booking-table td:nth-of-type(2):before {
            content: "Check-in";
        }

        .booking-table td:nth-of-type(3):before {
            content: "Check-out";
        }

        .booking-table td:nth-of-type(4):before {
            content: "Guests";
        }

        .booking-table td:nth-of-type(5):before {
            content: "Price";
        }
    }
</style>
