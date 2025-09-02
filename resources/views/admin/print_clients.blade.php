<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print Clients</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .status-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Client Records</h2>
    <div class="status-title">
        @if(request('status'))
            Status: {{ strtoupper(request('status')) }} Clients
        @else
            All Clients 
        @endif
    </div>

    @foreach($clients as $chunk)
    <table>
        <thead>
            <tr>
                <th>Group</th>
                <th>Meter No.</th>
                <th>Client Name</th>
                <th>Barangay</th>
                <th>Purok</th>
                <th>Contact Number</th>
                <th>Install Date</th>
                <th>Meter Series</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach($chunk as $client)
            <tr>
                <td>{{ $client->group }}</td>
                <td>{{ $client->meter_no }}</td>
                <td>{{ $client->full_name }}</td>
                <td>{{ $client->barangay }}</td>
                <td>{{ $client->purok }}</td>
                <td>{{ $client->contact_number }}</td>
                <td>{{ \Carbon\Carbon::parse($client->installation_date)->format('M d, Y') }}</td>
                <td>{{ $client->meter_series }}</td>
                <td>{{ strtoupper($client->status ?? 'N/A') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if (!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach

</body>
</html>
