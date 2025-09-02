<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print Reports</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        h2, .status-title { text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Report Records</h2>

    <div class="status-title">
        @if(isset($status) && $status)
            Status: {{ ucfirst($status) }} Consumers
        @else
            All Consumers
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Billing ID</th>
                <th>Meter No.</th>
                <th>Client Name</th>
                <th>Billing Date</th>
                <th>Previous</th>
                <th>Present</th>
                <th>Consumed</th>
                <th>Current Bill</th>
                <th>Arrears</th>
                <th>Penalty</th>
                <th>Total Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                <tr>
                    <td>{{ $report->billing_id }}</td>
                    <td>{{ $report->meter_no }}</td>
                    <td>{{ $report->full_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($report->billing_date)->format('M d, Y') }}</td>
                    <td>{{ $report->previous_reading }}</td>
                    <td>{{ $report->present_reading }}</td>
                    <td>{{ $report->consumed }}</td>
                    <td>₱{{ number_format($report->current_bill, 2) }}</td>
                    <td>₱{{ number_format($report->arrears ?? 0, 2) }}</td>
                    <td>₱{{ number_format($report->total_penalty ?? 0, 2) }}</td>
                    <td>₱{{ number_format($report->total_amount, 2) }}</td>
                    <td>{{ ucfirst($report->status ?? 'N/A') }}</td>
                </tr>
            @empty
                <tr><td colspan="12">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
