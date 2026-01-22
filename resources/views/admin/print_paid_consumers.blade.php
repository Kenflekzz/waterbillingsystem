<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Total Paid Consumers</title>
    <style>
        body {
            font-family: Arial, "DejaVu Sans";
            font-size: 10px;
            color: #333;
        }

        h2, h3 {
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .filters {
            margin-bottom: 15px;
        }

        .filters strong {
            display: inline-block;
            width: 120px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #444;
        }

        th, td {
            padding: 6px 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
        color: #000;                 /* black text */
        background-color: transparent; /* NO colour */
    }

    /* we keep the classes only for the logic – no colours */
    .bg-success,
    .bg-info,
    .bg-secondary {
        background-color: transparent !important;
        color: #000 !important;
    }

        /* ---------- no colours in print/pdf ---------- */
     @media print {
        .bg-success, .bg-info, .bg-secondary {
            background-color: transparent !important;
            color: #000 !important;          /* black text */
        }
     }
    </style>
</head>
<body>
    <div class="header">
        <h2>Total Paid Consumers Report</h2>
        <h3>{{ \Carbon\Carbon::now()->format('F d, Y') }}</h3>
    </div>

    <div class="filters">
        @if(request('name'))
            <div><strong>Consumer Name:</strong> {{ request('name') }}</div>
        @endif

        @if(request('billing_month'))
            <div><strong>Billing Month:</strong> {{ \Carbon\Carbon::parse(request('billing_month').'-01')->format('M Y') }}</div>
        @endif

        @if(request('status'))
            <div><strong>Status:</strong> 
                @if(request('status') === 'gcash')
                    Paid via GCash
                @else
                    {{ ucfirst(request('status')) }}
                @endif
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Client Full Name</th>
                <th>Barangay</th>
                <th>Purok</th>
                <th>Billing Month</th>
                <th>Current Bill</th>
                <th>Arrears</th>
                <th>Partial Payment Amount</th>
                <th>Payment Type</th>
                <th>Penalty</th>
                <th>Total Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($totalPaid as $payment)
            <tr>
                <td>{{ $payment->client->full_name ?? 'N/A' }}</td>
                <td>{{ $payment->client->barangay ?? 'N/A' }}</td>
                <td>{{ $payment->client->purok ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($payment->billing_month)->format('M Y') }}</td>
                <td>₱{{ number_format($payment->current_bill, 2) }}</td>
                <td>₱{{ number_format($payment->arrears ?? 0, 2) }}</td>
                <td>₱{{ number_format($payment->partial_payment_amount ?? 0, 2) }}</td>
                <td>{{ $payment->payment_type_label }}</td>
                <td>₱{{ number_format($payment->penalty, 2) }}</td>
                <td>₱{{ number_format($payment->total_amount, 2) }}</td>
                <td>
                    @if($payment->payment_type === 'gcash')
                        <span class="badge bg-info">Paid via GCash</span>
                    @elseif($payment->status === 'paid')
                        <span class="badge bg-success">Paid</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11">No records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
