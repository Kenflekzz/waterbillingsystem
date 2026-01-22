<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt #{{ $payment->id }}</title>
    <style>
        body {
            font-family: monospace;
            font-size: 12px;
            line-height: 1.2;
        }
        .receipt {
            width: 300px;
            margin: 0 auto;
            padding: 10px;
            border: 1px dashed #000;
        }
        .center {
            text-align: center;
        }
        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
        }
        .bold {
            font-weight: bold;
        }
        .right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center bold">
            PAYMENT RECEIPT
        </div>

        <div class="line"></div>

        <table>
            <tr>
                <td>Receipt ID:</td>
                <td class="right">{{ $payment->id }}</td>
            </tr>
            <tr>
                <td>Date:</td>
                <td class="right">{{ $date }}</td>
            </tr>
            <tr>
                <td>Bill Number:</td>
                <td class="right">{{ $billing->bill_number }}</td>
            </tr>
            <tr>
                <td>Payment Method:</td>
                <td class="right">{{ ucfirst($payment->payment_type) }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <table>
            <tr class="bold">
                <td>Month</td>
                <td class="right">Current</td>
                <td class="right">Arrears</td>
                <td class="right">Penalty</td>
                <td class="right">Total</td>
            </tr>
            <tr>
                <td>{{ $billing->billing_month ?? 'N/A' }}</td>
                <td class="right">₱{{ number_format($billing->current_bill,2) }}</td>
                <td class="right">₱{{ number_format($billing->arrears,2) }}</td>
                <td class="right">₱{{ number_format($billing->penalty,2) }}</td>
                <td class="right">₱{{ number_format($payment->partial_payment_amount,2) }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <table>
            <tr>
                <td class="bold">Name:</td>
                <td class="right">{{ $user->name }}</td>
            </tr>
            <tr>
                <td class="bold">Email:</td>
                <td class="right">{{ $user->email }}</td>
            </tr>
            <tr>
                <td class="bold">Status:</td>
                <td class="right">{{ ucfirst($payment->status) }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <div class="center">
            Thank you for your payment!
        </div>
    </div>
</body>
</html>
