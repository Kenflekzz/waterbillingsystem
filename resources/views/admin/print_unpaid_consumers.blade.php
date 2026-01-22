<!DOCTYPE html>
<html>
<head>
    <title>Unpaid Consumers Report</title>
    <style>
        body { font-family: Arial, "DejaVu Sans"; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>

<h3 style="text-align:center">Unpaid Consumers Report</h3>

<p>
    @if(request('name')) <strong>Name:</strong> {{ request('name') }} <br> @endif
    @if(request('billing_month'))
        <strong>Billing Month:</strong>
        {{ \Carbon\Carbon::parse(request('billing_month'))->format('F Y') }}
    @endif
</p>

<table>
    <thead>
        <tr>
            <th>Client Name</th>
            <th>Barangay</th>
            <th>Purok</th>
            <th>Billing Month</th>
            <th>Total Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($unpaid as $payment)
        <tr>
            <td>{{ $payment->client->full_name }}</td>
            <td>{{ $payment->client->barangay }}</td>
            <td>{{ $payment->client->purok }}</td>
            <td>{{ \Carbon\Carbon::parse($payment->billing_month)->format('M Y') }}</td>
            <td>₱{{ number_format($payment->total_amount, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
