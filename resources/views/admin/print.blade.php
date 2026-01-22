<link rel="icon" href="{{ asset($homepage->favicon ?? 'images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
<!DOCTYPE html>
<html>
<head>
    <title>Billing Statement</title>
    <link rel="icon" href="{{ asset($homepage->favicon ?? 'images/MAGALLANES_LOGO.png') }}" type="image/x-icon">
    @vite('resources/css/print.css')
</head>
<body onload="window.print()">

    @include('admin._bill_copy', [
        'billing' => $billing,
        'arrears' => $arrears,
        'penalty' => $penalty,
        'arrearsBreakdown' => $arrearsBreakdown,
        'penaltyBreakdown' => $penaltyBreakdown,
        'reconnectionFee' => $reconnectionFee,
        'totalAmount' => $billing->current_bill + $arrears + $penalty + $billing->maintenance_cost + $billing->installation_fee + $reconnectionFee,
        'copyLabel' => "ADMIN'S COPY"
    ])


    @include('admin._bill_copy', [
        'billing' => $billing,
        'arrears' => $arrears,
        'penalty' => $penalty,
        'arrearsBreakdown' => $arrearsBreakdown,
        'penaltyBreakdown' => $penaltyBreakdown,
        'reconnectionFee' => $reconnectionFee,
        'totalAmount' => $billing->current_bill + $arrears + $penalty + $billing->maintenance_cost + $billing->installation_fee + $reconnectionFee,
        'copyLabel' => "CONSUMER'S COPY"
    ])

</body>
</html>
