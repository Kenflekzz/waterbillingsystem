# Screenshot Capture Guide

This file tells you exactly what screenshots to capture and what filenames to use. Put all images into `docs/images/`.

Capture checklist

1. Home page with footer
- URL: `/`
- Filename: `docs/images/homepage.png`
- Capture: the footer area showing the `View Meter Number` and `Read Water Bill` links.

2. View meter number page
- URL: `/view-meternumber`
- Filename: `docs/images/view_meter_number.png`
- Capture: the search form with `Search` button and the result showing the meter number.

3. Read water bill page
- URL: `/read-waterbill`
- Filename: `docs/images/read_waterbill.png`
- Capture: the search form and a bill result showing `Print` and `Download` buttons.

4. Sign up page
- URL: `/user/register`
- Filename: `docs/images/signup.png`
- Capture: the full page showing the `Register` button and the form filled with example data (do not use real personal data).

2. Login page
- URL: `/user/login`
- Filename: `docs/images/login.png`
- Capture: the login form showing `Email` and `Password` fields and the `Login` button.

3. Billing list
- URL: `/user/billing`
- Filename: `docs/images/billing_list.png`
- Capture: the list of bills; make sure one bill's `View`, `Print`, and `Pay` buttons are visible.

4. Bill details view
- URL pattern: `/user/billing/{id}`
- Filename: `docs/images/bill_view.png`
- Capture: the bill detail page showing `Previous Reading`, `Current Reading`, `Consumption`, `Amount Due`, and the `Pay` button.

5. Payment confirmation
- Where: the payment provider or the app payment confirmation screen
- Filename: `docs/images/payment_confirm.png`
- Capture: the final confirmation screen that shows `Payment Successful` or `Payment Failed` (capture the success case if possible).

6. Receipt view
- URL pattern: `/user/user_receipt/{payment}`
- Filename: `docs/images/receipt.png`
- Capture: the receipt page with `Download Receipt` and `Print Receipt` buttons visible.

How to make screenshots look consistent
- Use a desktop browser at 1366×768 or 1280×800 for consistent sizing.
- Hide any sensitive real data; use test accounts and test meter numbers.
- Save images as PNG and place them in `docs/images/`.

Including screenshots in the guide
- After placing images in `docs/images/`, you can manually insert image references into `docs/USER_GUIDE.md` where indicated.

Automating PDF generation
- See `docs/generate_user_guide_pdf.ps1` to convert `docs/USER_GUIDE.md` to PDF when images are present.
