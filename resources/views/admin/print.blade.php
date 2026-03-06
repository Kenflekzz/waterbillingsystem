<!DOCTYPE html>
<html>
<head>
    <title>Billing Statement</title>
    <link rel="icon" href="{{ asset($homepage->favicon ?? 'images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
    @vite('resources/css/print.css')
</head>
<body onload="window.print()">

    <div class="print-wrapper">
        <!-- Admin Copy -->
        <div class="bill-wrapper">
            @include('admin._bill_copy', [
                'billing' => $billing,
                'arrears' => $arrears,
                'penalty' => $penalty,
                'arrearsBreakdown' => $arrearsBreakdown,
                'penaltyBreakdown' => $penaltyBreakdown,
                'totalAmount' => $billing->current_bill + $arrears + $penalty + $billing->maintenance_cost + $billing->installation_fee,
                'copyLabel' => "ADMIN'S COPY"
            ])
        </div>

        <!-- Consumer Copy -->
        <div class="bill-wrapper">
            @include('admin._bill_copy', [
                'billing' => $billing,
                'arrears' => $arrears,
                'penalty' => $penalty,
                'arrearsBreakdown' => $arrearsBreakdown,
                'penaltyBreakdown' => $penaltyBreakdown,
                'totalAmount' => $billing->current_bill + $arrears + $penalty + $billing->maintenance_cost + $billing->installation_fee,
                'copyLabel' => "CONSUMER'S COPY"
            ])
        </div>
    </div>

</body>
</html>