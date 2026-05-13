# Water Billing System — Simple User Guide (Easy, step-by-step)

This guide explains what a regular person (user) and an admin will experience in the system. It's written simply — like explaining to a child — with clear step-by-step actions and what each button or page does.

If you are an admin, your job is to manage people and water bills. If you are a user (a person who pays water bills), your job is to see your bills, pay them, and check your water usage.

---

**How to use this guide:**
- Read the "For Users" section if you are a consumer.
- Read the "For Admins" section if you administer the system.
- Use the "Quick actions" boxes to perform common tasks.

---

**Basic pages you will see**
- Home page: a welcome page with simple information.
- Login/Register pages: where you sign up or sign in.
- Billing page: where bills are shown.
- Payments and receipts: where you pay and download proof of payment.
- Consumption (meters): shows how much water you used.
- Notifications/Messages: system or admin messages for you.

---

**For Users (People who pay water bills)**

1) Signing up and signing in
- What you do: Go to `/user/register` to create an account with your name, email, and meter number. Then go to `/user/login` to sign in.
- Why: So the system knows who you are and can show your bills.

Quick tip: If you forget your password, use the password reset (OTP) pages — the system will send a code to your email or phone.

2) Seeing your bills (What you will see)
- Where: `/user/billing` after you sign in.
- What you see: A list of bills. Each bill shows the month, amount you owe, whether it is paid or not, and a "View" or "Print" button.
- What you can do:
  - Click "View" or the bill to see details (previous reading, current reading, consumption, charges).
  - Click "Print" to open a printable version of your bill.

Simple explanation: A bill is like a restaurant receipt — it lists what you used and how much to pay.

3) Paying a bill (step-by-step)
- Where: From the bill details page (`/user/billing/{id}`) or the quick action `Pay` button in `/user/billing`.
- Buttons and labels you will see:
  - `View` — open bill details
  - `Print` — open printable bill
  - `Pay` — start payment flow
  - On the payment page: `Proceed to payment`, `Confirm`, `Cancel`
- How it works (simple):
  1. Click `View` on a bill and then click the `Pay` button.
  2. Choose a payment method (for example, `GCash` or `PayMongo`).
  3. Click `Proceed to payment` or `Confirm` on the payment provider screen.
  4. When payment finishes, the system shows a `Payment Successful` page or a `Payment Failed` page.
- What you get: A receipt you can view and download at `/user/user_receipt/{payment}` and `/user/user_receipt/download/{payment}`. Look for a `Download Receipt` or `Print Receipt` button on the receipt page.

Child-friendly: Think of clicking the `Pay` button like putting a coin in a vending machine. If the machine takes the coin, you get a ticket (receipt) that proves you paid.

4) Viewing and downloading receipts
- Where: `/user/user_receipt/{payment}` to view, and `/user/user_receipt/download/{payment}` to download.
- Buttons and labels you will see:
  - `Download Receipt` — download PDF or image of the receipt
  - `Print Receipt` — open print dialog
- Why: Keep the receipt as proof you paid.

5) Checking how much water you used (consumption)
- Where: `/user/my-consumption` or `/user/consumption`.
- What you see: A history of readings showing how much water you used each month or day (if meter data is available).

Simple note: The system may get meter readings from an IoT device, or an admin may enter them.

6) Notifications and messages
- Where: `/user/notifications`.
- What you see: Admin messages (announcements) and notifications about payments or problems.
- You can mark a notification as read so it looks cleared.

7) Reporting problems (if something is wrong)
- Where: `/user/billing/report-problem`.
- What you do: Fill a short form describing the problem (e.g., wrong amount, broken meter).
- What happens next: Admins can see your report and reply or fix things.

8) Reading a public bill page
- Where: `/read-waterbill` (anyone can see this).
- Why: If you want to quickly check a bill without logging in.

---

**For Admins (People who manage the system)**

Admins have more pages because they must control the whole system. Think of an admin like a librarian who cares for the books and the readers.

1) Signing in
- Where: `/admin/login` or `/admin/register`.
- Why: So you can access admin pages.

2) Dashboard — what admins see first
- Where: `/admin/dashboard`.
- What it shows: Quick numbers (like how many subscribers, how many unpaid bills), recent activity, and short links to important actions.

3) Managing clients (people who get billed)
- Where: `/admin/clients`.
- Actions:
  - Add a new client: fill name, address, meter number, and contact.
  - Edit client details: fix typos or update addresses.
  - Remove client: only if needed.
  - Print client list: to keep a paper record.

Child explanation: Clients are the people who drink water; admins keep their contact details in the list.

4) Generating and managing bills (the core admin task)
- Where: `/admin/billings` and helper endpoints.
- What admins do:
  - Create a new bill for a client: add the meter readings, consumption, and any fees.
  - The system can calculate charges using the readings and rates.
  - Print bills for mailing or distribution: `/admin/billings/{id}/print`.
  - Get the next bill number: `/admin/billings/next-id`.
  - See the latest bill for a client: `/admin/billings/latest/{clientId}`.
  - Check and apply penalties or calculate arrears (previous unpaid bills).

Step-by-step (create a bill):
 1. Go to `Create bill` (button labeled `Create` or `New Billing`).
 2. Choose the client from the list or search by name/meter number.
 3. Enter the `Previous Reading` and `Current Reading` fields.
 4. Confirm the calculated `Consumption` and `Amount Due` shown by the form.
 5. Click `Save` or `Create` to store the bill. The bill will appear in the client's bill list.
 6. To print the bill, open the saved bill and click `Print`.

Child-friendly: Generating a bill is like writing how many candies someone ate, and then asking them to pay for those candies.

5) Recording payments
- Where: `/admin/payments`.
- What you do: Mark payments received, view online payments, and print receipts if needed.

6) Totals and reports
- Where: `/admin/totals/*`, `/admin/reports`.
- What you can do: See and print lists of unpaid customers, paid customers, disconnected accounts, and usage reports.

7) IoT flowmeter devices and readings
- Where: `/admin/flowmeter` and `/admin/flow-readings`.
- What admins do:
  - Add devices: register a new meter device that reports water flow.
  - Assign a device to a client: tell the device which client it measures.
  - Delete devices or reassign them.
  - View readings: the system stores readings the device sends.

How readings get in:
  - Devices send readings to `POST /admin/flow-readings`.
  - Admins can fetch the latest reading for a device with `GET /admin/flow-readings/latest/{deviceId}`.

Child-friendly: Think of the device as a little robot that counts how much water flows and tells the admin.

8) Sending messages and announcements
- Where: `/admin/messages`.
- What to do: Send a message to everyone (general) or a specific user (personal).

9) Activity log
- Where: `/admin/activity_log`.
- What it shows: A history of changes done in the system by admins.

10) Disconnect queue (when someone hasn't paid)
- Where: `/admin/disconnect-pending`.
- What to do: Mark accounts that need to be disconnected for non-payment, and mark them when action is taken.

11) Edit homepage content
- Where: `/admin/homepage/edit` and `PUT /admin/homepage/update`.
- Why: Change the welcome message, notices, or public info on the home page.

---

**Common user-experienced flows (detailed steps)**

Flow: New user signs up and pays a bill
 1. Go to `/user/register` and click the `Register` button after filling the form.
 2. Sign in at `/user/login` using the `Login` button.
 3. Go to `/user/billing` and find the newest bill.
 4. Click `View` on the bill and then click `Pay`.
 5. On the payment page click `Proceed to payment`, follow the provider steps and click `Confirm` when asked.
 6. After success, go to `/user/user_receipt/{payment}` and click `Download Receipt` to save proof.

Flow: Admin creates bills for the month
 1. Admin signs into `/admin/login`.
 2. Go to `/admin/billings` and click "Create".
 3. Select client, enter previous and current readings.
 4. Confirm and save. The bill appears in the client's list.
 5. Optionally, print bills to mail or hand out.

Flow: IoT device posts a reading
 1. Device or service sends a reading to `POST /admin/flow-readings`.
 2. The system saves the reading to the database and may update consumption reports.
 3. Admin can view charts at `/admin/flow-readings/chart-data`.

---

**Safety and troubleshooting (simple)**
- If a payment doesn't work: check the payment provider message, or try again later.
- If a bill looks wrong: report it to admin with `/user/billing/report-problem`.
- If you can't log in: use password reset or contact admin.

---

**Where to find things in the app code (for curious people)**
- Routes: [routes/web.php](routes/web.php)
- User billing logic: `app/Http/Controllers/UserBillingController.php`
- Admin billing logic: `app/Http/Controllers/BillingController.php`
- IoT devices: `app/Http/Controllers/IotDeviceController.php` and `app/Http/Controllers/FlowReadingController.php`

---

If you want, I can now:
- Add screenshots for each step (I will create image placeholders and a capture guide),
- Export this guide to PDF using a small script I add to the `docs/` folder,
- Or expand any flow with exact button names and example screenshots.

---

**Screenshots and PDF export**

I added a separate short guide with exact steps to capture screenshots and suggested filenames: see [docs/SCREENSHOT_GUIDE.md](SCREENSHOT_GUIDE.md). Place screenshots into `docs/images/` and use the filenames suggested. When ready, run the included PowerShell script `docs/generate_user_guide_pdf.ps1` to try generating `docs/USER_GUIDE.pdf` from the Markdown file (requires `pandoc` or `wkhtmltopdf`).

Example screenshot filenames and where they go:
- `docs/images/signup.png` — signup page (`/user/register`).
- `docs/images/login.png` — login page (`/user/login`).
- `docs/images/billing_list.png` — billing list (`/user/billing`).
- `docs/images/bill_view.png` — bill details (with `Pay` button visible).
- `docs/images/payment_confirm.png` — payment provider confirmation screen.
- `docs/images/receipt.png` — receipt view/download screen.

Place the images in `docs/images/` and they will be referenced by the screenshot guide for manual inclusion or when generating a PDF.
