<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt #{{ $payment->id }}</title>
    
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        @page {
            size: 226.77pt 350pt; /* 80mm width, fixed height */
            margin: 0;
        }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 9pt;
            line-height: 1.2;
            /* CRITICAL: Account for padding in total width */
            /* 226.77pt - 20pt (10pt padding each side) = 206.77pt content width */
            width: 206.77pt;
            margin: 0 auto;
            padding: 10pt;
            color: #000;
        }
        
        .receipt-container {
            width: 100%;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 5pt;
        }
        
        .receipt-title {
            font-size: 11pt; /* Slightly smaller to fit */
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .receipt-subtitle { 
            font-size: 8pt; 
            margin-top: 2pt; 
        }
        
        .receipt-divider {
            border-top: 1px dashed #000;
            margin: 5pt 0;
            width: 100%;
        }
        
        .receipt-section { 
            margin: 5pt 0; 
        }
        
        /* CRITICAL: Table must fit within body width (206.77pt) */
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Force fixed layout */
        }
        
        .receipt-table td {
            padding: 2pt 0;
            font-size: 9pt;
            /* Prevent text overflow */
            word-wrap: break-word;
            overflow: hidden;
        }
        
        .receipt-label { 
            text-align: left; 
            width: 40%; /* Fixed width for labels */
        }
        
        .receipt-value { 
            text-align: right; 
            width: 60%; /* Fixed width for values */
        }
        
        .receipt-bold { font-weight: bold; }
        .receipt-uppercase { text-transform: uppercase; }
        
        .receipt-items-header td {
            border-bottom: 1px solid #000;
            font-weight: bold;
            padding-bottom: 3pt;
            font-size: 8pt;
        }
        
        .receipt-items-row td { 
            padding: 3pt 0; 
        }
        
        /* Description column narrower, amount column wider */
        .col-description { width: 45%; }
        .col-amount { width: 55%; }
        
        .receipt-total-section {
            margin-top: 5pt;
            border-top: 1px solid #000;
            padding-top: 5pt;
        }
        
        .receipt-total-row td {
            font-weight: bold;
            font-size: 10pt;
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 10pt;
            font-size: 8pt;
        }
        
        .receipt-footer-note {
            margin-top: 5pt;
            font-size: 7pt;
        }
        
        .receipt-cut-line {
            text-align: center;
            margin-top: 10pt;
            font-size: 7pt;
            border-top: 1px dashed #ccc;
            padding-top: 5pt;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <div class="receipt-title">PAYMENT RECEIPT</div>
            <div class="receipt-subtitle">Official Receipt</div>
        </div>

        <div class="receipt-divider"></div>

        <!-- Transaction Info -->
        <div class="receipt-section">
            <table class="receipt-table">
                <tr>
                    <td class="receipt-label">Receipt #:</td>
                    <td class="receipt-value">{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <td class="receipt-label">Date:</td>
                    <td class="receipt-value">{{ $date }}</td>
                </tr>
                <tr>
                    <td class="receipt-label">Bill #:</td>
                    <td class="receipt-value">{{ $billing->bill_number }}</td>
                </tr>
                <tr>
                    <td class="receipt-label">Method:</td>
                    <td class="receipt-value receipt-uppercase">{{ $payment->payment_type }}</td>
                </tr>
            </table>
        </div>

        <div class="receipt-divider"></div>

        <!-- Customer Info -->
        <div class="receipt-section">
            <table class="receipt-table">
                <tr>
                    <td class="receipt-label">Customer:</td>
                    <td class="receipt-value">{{ $user->name }}</td>
                </tr>
                <tr>
                    <td class="receipt-label">Email:</td>
                    <td class="receipt-value" style="font-size: 8pt;">{{ $user->email }}</td>
                </tr>
            </table>
        </div>

        <div class="receipt-divider"></div>

        <!-- Billing Details -->
        <div class="receipt-section">
            <table class="receipt-table">
                <tr class="receipt-items-header">
                    <td class="col-description">Description</td>
                    <td class="receipt-value col-amount">Amount</td>
                </tr>
                
                @if($billing->current_bill > 0)
                <tr class="receipt-items-row">
                    <td>Current Bill</td>
                    <td class="receipt-value">PHP {{ number_format($billing->current_bill, 2) }}</td>
                </tr>
                @endif
                
                @if($billing->arrears > 0)
                <tr class="receipt-items-row">
                    <td>Arrears</td>
                    <td class="receipt-value">PHP {{ number_format($billing->arrears, 2) }}</td>
                </tr>
                @endif
                
                @if($billing->penalty > 0)
                <tr class="receipt-items-row">
                    <td>Penalty</td>
                    <td class="receipt-value">PHP {{ number_format($billing->penalty, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="receipt-total-section">
            <table class="receipt-table">
                <tr class="receipt-total-row">
                    <td class="receipt-label">TOTAL PAID:</td>
                    <td class="receipt-value">PHP {{ number_format($payment->partial_payment_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="receipt-label">Status:</td>
                    <td class="receipt-value receipt-uppercase">{{ $payment->status }}</td>
                </tr>
            </table>
        </div>

        <div class="receipt-divider"></div>

        <!-- Footer -->
        <div class="receipt-footer">
            <div>Thank you for your payment!</div>
            <div class="receipt-footer-note">
                This serves as your official receipt.<br>
                Keep this for your records.
            </div>
        </div>

        <div class="receipt-cut-line">
            ---------------- CUT HERE ----------------
        </div>
    </div>
</body>
</html>