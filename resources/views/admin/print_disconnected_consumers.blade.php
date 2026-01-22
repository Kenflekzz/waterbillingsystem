<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Disconnected Consumers Report</title>
    <style>
    body{
        font-family: Arial, "DejaVu Sans", sans-serif;
        font-size: 9pt;
        color:#000;
        margin: 5px auto;   /* 1 */
        width: 98%;         /* 1 */
    }
    h2,h3{ margin: 0; }
    .header{ text-align: center; margin-bottom: 12px; }
    .filters{ margin-bottom: 10px; font-size: 8pt; }
    .filters strong{ display:inline-block; width: 120px; }

    table{
        margin: 8px auto 0; /* 2 */
        width: 96%;         /* 2 */
        border-collapse: collapse;
    }
    th,td{
        border: 1px solid #444;
        padding: 4px 5px;
        text-align: center;
        white-space: nowrap;
    }
    th{
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .w-name{ text-align: left; }
    tr{ page-break-inside: avoid; }
</style>
</head>
<body>
    <div class="header">
        <h2>DISCONNECTED CONSUMERS REPORT</h2>
        <h3>{{ now()->format('F d, Y - h:i A') }}</h3>
    </div>

    {{-- FILTER CRITERIA (only show when supplied) --}}
    <div class="filters">
        @if(request('name'))
            <div><strong>Consumer Name:</strong> {{ request('name') }}</div>
        @endif
        @if(request('billing_month'))
            <div><strong>Billing Month:</strong>
                {{ \Carbon\Carbon::parse(request('billing_month').'-01')->format('F Y') }}
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="w-name">Client Full Name</th>
                <th>Barangay</th>
                <th>Purok</th>
                <th>Billing Month</th>
                <th>Current Bill</th>
                <th>Arrears</th>
                <th>Partial Payment</th>
                <th>Payment Type</th>
                <th>Penalty</th>
                <th>Total Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($totalDisconnected as $p)
                <tr>
                    <td class="w-name">{{ $p->client->full_name ?? 'N/A' }}</td>
                    <td>{{ $p->client->barangay ?? '' }}</td>
                    <td>{{ $p->client->purok ?? '' }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->billing_month)->format('M Y') }}</td>
                    <td>₱{{ number_format($p->current_bill,2) }}</td>
                    <td>₱{{ number_format($p->arrears,2) }}</td>
                    <td>₱{{ number_format($p->partial_payment_amount??0,2) }}</td>
                    <td>{{ $p->payment_type_label }}</td>
                    <td>₱{{ number_format($p->penalty,2) }}</td>
                    <td><strong>₱{{ number_format($p->total_amount,2) }}</strong></td>
                    <td>{{ ucfirst($p->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">No disconnected records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>