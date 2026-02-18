<!DOCTYPE html>
<html>
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
<head>
    <title>Subscribers Report</title>
    <style>
        body { 
            font-family: Arial, "DejaVu Sans"; 
            font-size: 12px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            border: 1px solid #000; 
            padding: 5px; 
            text-align: center; 
        }
        th { 
            background: #f0f0f0; 
        }
    </style>
    
    {{-- Auto-trigger browser print on load --}}
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>
<body>

<h3 style="text-align:center">Subscribers Report</h3>

<p>
    @if($currentFilter != 'all')
        <strong>Status:</strong> {{ $currentFilter }}
    @else
        <strong>Status:</strong> All
    @endif
</p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Meter No.</th>
            <th>Barangay</th>
            <th>Purok</th>
            <th>Contact</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($subscribers as $subscriber)
        <tr>
            <td>{{ $subscriber->id }}</td>
            <td>{{ $subscriber->full_name }}</td>
            <td>{{ $subscriber->meter_no }}</td>
            <td>{{ $subscriber->barangay }}</td>
            <td>{{ $subscriber->purok }}</td>
            <td>{{ $subscriber->contact_number ?? 'N/A' }}</td>
            <td>{{ $subscriber->status }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7">No records found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>