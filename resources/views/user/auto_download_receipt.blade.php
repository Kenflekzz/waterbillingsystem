<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Downloading Receipt...</title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Trigger PDF download
            const link = document.createElement('a');
            link.href = "{{ $pdfUrl }}";
            link.download = "";
            document.body.appendChild(link);
            link.click();

            // Redirect back to billing after download
            setTimeout(() => {
                window.location.href = "{{ route('user.billing') }}";
            }, 1000);
        });
    </script>
</head>
<body>
    <p>Preparing your receipt download...</p>
</body>
</html>
