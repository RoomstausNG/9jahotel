<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupon - {{ $coupon->code }}</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .coupon-container {
            max-width: 600px;
            margin: 0 auto;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .coupon-container::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.1) 10px,
                rgba(255,255,255,0.1) 20px
            );
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .coupon-content {
            position: relative;
            z-index: 1;
        }
        
        .coupon-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .coupon-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .coupon-subtitle {
            font-size: 1.2rem;
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .coupon-code {
            background: white;
            color: #333;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            letter-spacing: 3px;
            border: 3px dashed #667eea;
        }
        
        .coupon-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        
        .detail-item {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .detail-label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1.1rem;
            font-weight: bold;
        }
        
        .coupon-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }
        
        .print-button {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin: 20px 0;
        }
        
        .print-button:hover {
            background: #218838;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .status-active {
            background: #28a745;
            color: white;
        }
        
        .status-inactive {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" class="print-button">Print Coupon</button>
        <button onclick="window.close()" class="print-button" style="background: #6c757d; margin-left: 10px;">Close</button>
    </div>

    <div class="coupon-container">
        <div class="coupon-content">
            <div class="coupon-header">
                <h1 class="coupon-title">DISCOUNT COUPON</h1>
                <p class="coupon-subtitle">{{ config('app.name') }}</p>
            </div>
            
            <div class="coupon-code">
                {{ strtoupper($coupon->code) }}
            </div>
            
            <div class="coupon-details">
                <div class="detail-item">
                    <div class="detail-label">Discount</div>
                    <div class="detail-value">{{ $coupon->percent }}% OFF</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Type</div>
                    <div class="detail-value">{{ strtoupper($coupon->type) }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Valid From</div>
                    <div class="detail-value">{{ $coupon->valid_from->format('M d, Y') }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Valid Until</div>
                    <div class="detail-value">{{ $coupon->valid_till->format('M d, Y') }}</div>
                </div>
                
                @if($coupon->highest_value)
                <div class="detail-item">
                    <div class="detail-label">Max Discount</div>
                    <div class="detail-value">₦{{ number_format($coupon->highest_value, 2) }}</div>
                </div>
                @endif
                
                <div class="detail-item">
                    <div class="detail-label">Usage Limit</div>
                    <div class="detail-value">{{ $coupon->limit_usage == -1 ? 'Unlimited' : $coupon->limit_usage }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Times Used</div>
                    <div class="detail-value">{{ $coupon->usage_count }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span class="status-badge {{ $coupon->status ? 'status-active' : 'status-inactive' }}">
                            {{ $coupon->status ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                    </div>
                </div>
            </div>
            
            @if($coupon->owner)
            <div style="text-align: center; margin: 20px 0;">
                <div class="detail-label">Assigned to</div>
                <div class="detail-value">{{ $coupon->owner->name }}</div>
            </div>
            @endif
            
            @if($coupon->hotel)
            <div style="text-align: center; margin: 20px 0;">
                <div class="detail-label">Hotel</div>
                <div class="detail-value">{{ $coupon->hotel->name }}</div>
            </div>
            @endif
            
            <div class="coupon-footer">
                <p style="margin: 0; font-size: 0.9rem; opacity: 0.8;">
                    Use this code during checkout to enjoy your discount!
                </p>
                <p style="margin: 10px 0 0 0; font-size: 0.8rem; opacity: 0.7;">
                    Generated on {{ now()->format('M d, Y H:i') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
