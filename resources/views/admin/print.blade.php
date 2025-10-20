<!DOCTYPE html>
<html>
<head>
    <title>Billing Statement</title>
    <link rel="icon" href="{{ asset($homepage->favicon ?? 'images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
    @vite('resources/css/print.css')
</head>
<body onload="window.print()">
<div class="bill-container">
    <div class="header">
        <img src="{{ asset('/images/MAGALLANES_LOGO.png') }}" class="logo">
        <div class="center">
            <h3>MAGALLANES WATER PROVIDER</h3>
            <p><strong>SPECIAL REVENUE UNIT</strong></p>
            <p>Caravallo Street, Brgy. Poblacion, Magallanes, Agusan Del Norte<br>Telephone #: 8060269</p>
        </div>
        <div class="right">
            <img src="{{ asset('/images/TREE_LOGO.png') }}">
        </div>
    </div>

    <div class="bill-title">BILL STATEMENT</div>

    <table class="no-border">
        <tr>
            <td><strong>NAME:</strong> {{ strtoupper($billing->client->full_name) }}</td>
            <td><strong>PUROK/BRGY:</strong> {{ strtoupper($billing->client->purok) }}, {{ strtoupper($billing->client->barangay) }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td><strong>BILLING DATE</strong></td>
            <td><strong>DATE CREATED</strong></td>
            <td><strong>METER TYPE</strong></td>
            <td><strong>METER NO.</strong></td>
        </tr>
        <tr>
            <td>{{ \Carbon\Carbon::parse($billing->billing_date)->format('M-Y') }}</td>
            <td>{{ $billing->created_at->format('M d, Y') }}</td>
            <td>-</td>
            <td>{{ $billing->client->meter_no }}</td>
        </tr>
        <tr>
            <td><strong>READING DATE</strong></td>
            <td><strong>PREVIOUS READING</strong></td>
            <td><strong>PRESENT READING</strong></td>
            <td><strong>CU.M CONSUMED</strong></td>
        </tr>
        <tr>
            <td>{{ \Carbon\Carbon::parse($billing->reading_date)->format('M d, Y') }}</td>
            <td>{{ $billing->previous_reading }}</td>
            <td>{{ $billing->present_reading }}</td>
            <td>{{ $billing->consumed }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <td><strong>CURRENT BILLING</strong></td>
            <td class="right-align">₱{{ number_format($billing->current_bill,2) }}</td>
        </tr>
        <tr>
            <td><strong>TOTAL PRIOR UNPAID (ARREARS)</strong></td>
            <td class="right-align">₱{{ number_format($arrears,2) }}</td>
        </tr>
        <tr>
            <td><strong>PENALTY (₱0.005/day)</strong></td>
            <td class="right-align">₱{{ number_format($penalty,2) }}</td>
        </tr>
        <tr>
            <td><strong>MAINTENANCE COST</strong></td>
            <td class="right-align">₱{{ number_format($billing->maintenance_cost,2) }}</td>
        </tr>
        <tr>
            <td><strong>INSTALLATION COST</strong></td>
            <td class="right-align">₱{{ number_format($billing->installation_fee,2) }}</td>
        </tr>
        <tr>
            <td><strong>EXCESS HOSE</strong></td>
            <td class="right-align">₱0.00</td>
        </tr>
        <tr>
            <td class="highlight">TOTAL AMOUNT DUE</td>
            <td class="right-align"><strong>₱{{ number_format(
                $billing->current_bill + $arrears + $penalty + 
                $billing->maintenance_cost + $billing->installation_fee,2) }}</strong></td>
        </tr>
    </table>

    {{-- Breakdown of Arrears --}}
    @if(!empty($arrearsBreakdown))
    <table>
        <tr class="section-title">
            <td colspan="2">Breakdown of Arrears</td>
        </tr>
        @foreach($arrearsBreakdown as $item)
            <tr>
                <td>{{ \Carbon\Carbon::parse($item['billing_month'])->format('F Y') }}</td>
                <td class="right-align">₱{{ number_format($item['current_bill'],2) }}</td>
            </tr>
        @endforeach
    </table>
    @endif

    {{-- Breakdown of Penalty --}}
    @if(!empty($penaltyBreakdown))
    <table>
        <tr class="section-title">
            <td colspan="4">Breakdown of Penalty</td>
        </tr>
        <tr>
            <th>Billing Month</th>
            <th>Due Date</th>
            <th>Days Late</th>
            <th class="right-align">Partial Penalty (₱)</th>
        </tr>
        @foreach($penaltyBreakdown as $item)
            <tr>
                <td>{{ \Carbon\Carbon::parse($item['billing_month'])->format('F Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($item['due_date'])->format('M d, Y') }}</td>
                <td>{{ $item['days_late'] }}</td>
                <td class="right-align">₱{{ number_format($item['partial_penalty'],2) }}</td>
            </tr>
        @endforeach
    </table>
    @endif

    <div class="note-section">
        <p><strong>1.</strong> Two (2) Consecutive UNPAID Billings follow the <span class="highlight">DISCONNECTION</span>.</p>
        <p><strong>2.</strong> This account must be settled on or before: <strong>{{ \Carbon\Carbon::parse($billing->billing_date)->addDays(14)->format('M d, Y') }}</strong> to avoid penalty charges.</p>
        <p><strong>3.</strong> Water service will be disconnected without prior notice.</p>
    </div>

    <table class="footer-table">
        <tr>
            <td><strong>Prepared by:</strong><br>GERLIE AGUELO<br>(SIGNATURE)</td>
            <td class="right-align"><strong>Received by:</strong><br>{{ strtoupper($billing->client->full_name) }}<br>(SIGNATURE)</td>
        </tr>
    </table>

    <div class="consumer-copy">CONSUMER'S COPY</div>
</div>
</body>
</html>
