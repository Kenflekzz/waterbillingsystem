<!DOCTYPE html>
<html>
<head>
    <title>Billing Statement</title>
    <link rel="icon" href="{{ asset($homepage->favicon ?? 'images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
    @vite('resources/css/print.css')
</head>
<body>
<div class="bill-container">

    <!-- Header -->
    <div class="header">
        <img src="{{ asset('/images/MAGALLANES_LOGO.png') }}" class="logo">
        <div class="center">
            <h3>MAGALLANES WATER PROVIDER</h3>
            <p><strong>SPECIAL REVENUE UNIT</strong></p>
            <p>
                Caravallo Street, Brgy. Poblacion, Magallanes, Agusan Del Norte<br>
                Telephone #: 8060269
            </p>
        </div>
        <div class="right">
            <img src="{{ asset('/images/TREE_LOGO.png') }}">
        </div>
    </div>

    <div class="bill-title">BILL STATEMENT</div>

    <!-- User Account Info -->
    <table class="no-border">
        <tr>
            <td><strong>NAME:</strong> {{ strtoupper($billing->user->first_name . ' ' . $billing->user->last_name) }}</td>
            <td><strong>PUROK/BRGY:</strong> 
                {{ strtoupper($billing->user->client->purok ?? 'N/A') }},
                {{ strtoupper($billing->user->client->barangay ?? 'N/A') }}
            </td>
        </tr>
    </table>

    <!-- Billing Details -->
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
            <td>{{ $billing->user->meter_number ?? $billing->user->client->meter_no ?? 'N/A' }}</td>
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

    <!-- MAIN BILLING TABLE -->
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
            <td class="right-align">₱{{ number_format($billing->maintenance_cost ?? 0,2) }}</td>
        </tr>
        <tr>
            <td><strong>INSTALLATION COST</strong></td>
            <td class="right-align">₱{{ number_format($billing->installation_fee ?? 0,2) }}</td>
        </tr>
        <tr class="highlight">
            <td class="highlight">TOTAL AMOUNT DUE</td>
            <td class="right-align">
                <strong>₱{{ number_format($totalAmount,2) }}</strong>
            </td>
        </tr>
    </table>

    <!-- SEPARATE EXCESS HOSE SECTION -->
    <table class="excess-hose-table">
        <tr>
            <td><strong>EXCESS HOSE</strong></td>
            <td class="right-align">₱{{ number_format($billing->excess_hose ?? 0, 2) }}</td>
        </tr>
    </table>

    <!-- Arrears Breakdown -->
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

    <!-- Penalty Breakdown -->
    @if(!empty($penaltyBreakdown))
        <table style="border: 2px solid #000; margin-top: 10px; width: 100%; border-collapse: collapse;">
            <tr class="section-title">
                <td colspan="4" style="background-color: #f4c6c6; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">Breakdown of Penalty</td>
            </tr>
            <tr style="background-color: #eee;">
                <th style="border: 1px solid #000; padding: 4px;">Billing Month</th>
                <th style="border: 1px solid #000; padding: 4px;">Due Date</th>
                <th style="border: 1px solid #000; padding: 4px;">Days Late</th>
                <th style="border: 1px solid #000; padding: 4px;" class="right-align">Partial Penalty (₱)</th>
            </tr>
            @foreach($penaltyBreakdown as $item)
                <tr>
                    <td style="border: 1px solid #000; padding: 4px;">{{ \Carbon\Carbon::parse($item['billing_month'])->format('M d, Y') }}</td>
                    <td style="border: 1px solid #000; padding: 4px;">{{ \Carbon\Carbon::parse($item['due_date'])->format('M d, Y') }}</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">{{ $item['days_late'] }}</td>
                    <td style="border: 1px solid #000; padding: 4px;" class="right-align">₱{{ number_format($item['partial_penalty'], 2) }}</td>
                </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="3" style="border: 1px solid #000; padding: 4px; text-align: right;">Total Penalty:</td>
                <td style="border: 1px solid #000; padding: 4px;" class="right-align">₱{{ number_format($penalty, 2) }}</td>
            </tr>
        </table>
    @endif

    <!-- Notes -->
    <div class="note-section">
        <p><strong>1.</strong> Two (2) Consecutive UNPAID Billings follow the <span class="highlight">DISCONNECTION</span>.</p>
        <p><strong>2.</strong> This account must be settled on or before: <strong>{{ \Carbon\Carbon::parse($billing->due_date)->format('M d, Y') }}</strong> to avoid penalty charges.</p>
        <p><strong>3.</strong> Water service will be disconnected without prior notice.</p>
    </div>

    <!-- Signature Block -->
    <table class="footer-table">
        <tr>
            <td>
                <strong>Prepared by:</strong><br>
                GERLIE AGUELO<br>(SIGNATURE)
            </td>
            <td class="right-align">
                <strong>Received by:</strong><br>
                {{ strtoupper($billing->user->first_name . ' ' . $billing->user->last_name) }}<br>(SIGNATURE)
            </td>
        </tr>
    </table>

    <div class="consumer-copy">{{ $copyLabel }}</div>
</div>
</body>
</html>