# iSkout — Product Requirements Document
**Version 1.0 | Full-Stack Web Application (HTML · CSS · JS · PHP · MySQL)**

---

## 1. Product Overview

iSkout is a mobile-first campus merchant discovery platform for students of the Polytechnic University of the Philippines (PUP) — Sta. Mesa. It allows students (Iskolars) to find nearby food stalls, printing shops, study spots, supply stores, and other campus-adjacent merchants, while a separate Admin Portal lets authorized personnel manage listings, review reports, and monitor engagement. The application is designed for simple deployment: it runs on XAMPP locally and can be uploaded directly to any PHP-capable free hosting provider such as InfinityFree without any build step or framework dependency.

---

## 2. Goals & Non-Goals

**Goals**
- Let students discover, search, and filter merchant listings around PUP Sta. Mesa.
- Provide email OTP-based account verification on registration.
- Protect the admin portal completely from the student-facing app (no visible link).
- Keep the codebase flat, readable, and portable — one PHP file per page, no frameworks.
- Support XAMPP (localhost) and free PHP hosting (InfinityFree) without code changes.

**Non-Goals**
- No real-time chat, in-app payments, or push notifications.
- No third-party OAuth (e.g., Google Sign-In).
- No React, Vue, Laravel, or Node.js.

---

## 3. User Roles

| Role | Access | Entry Point |
|---|---|---|
| **Guest** | Splash screen only | `index.php` |
| **Student (Iskolar)** | All user-facing screens post-login | `index.php` → login/register flow |
| **Admin** | Admin Portal only | `admin/index.php` (separate, unlisted) |

---

## 4. Architecture

### 4.1 Design Philosophy

Each page is a **single self-contained PHP file** that handles both the HTML output and the server-side logic for that page's actions (form submission, DB queries, session checks). PHP logic lives at the top of each file inside `<?php ?>` blocks; HTML/CSS/JS follows beneath. There is no MVC separation, no routing framework, and no API layer — `$_POST`, `$_SESSION`, and `mysqli` are used directly. This makes the project easy to read, debug, and deploy without a composer setup or build pipeline.

### 4.2 Session Strategy

PHP native sessions (`session_start()`) handle authentication state across pages. Upon successful login or OTP verification, the user's `id`, `webmail`, and `role` are written to `$_SESSION`. Every protected page checks for a valid session at the top and redirects to the login page if absent. Admin pages check for `$_SESSION['role'] === 'admin'` specifically.

### 4.3 Database

A single MySQL database (`iskout_db`) with the following tables:

- **`users`** — `id`, `webmail` (PUP email, UNIQUE), `password_hash`, `is_verified` (TINYINT), `created_at`
- **`otp_tokens`** — `id`, `webmail`, `token` (4-digit), `expires_at`, `used` (TINYINT)
- **`listings`** — `id`, `name`, `location`, `zone` (inside/outside), `open_time`, `close_time`, `image_path`, `created_at`
- **`listing_categories`** — `listing_id`, `category` (food/study/print/rentals/supplies/repair)
- **`listing_tags`** — `listing_id`, `tag` (e.g., "Free Wifi", "Tingi Available")
- **`reports`** — `id`, `listing_id`, `reported_by_user_id`, `reason`, `details`, `status` (pending/reviewed), `created_at`

---

## 5. File Structure

```
iskout/
│
├── index.php                  # Splash + user login/register hub (all user screens)
├── verify.php                 # OTP email verification page
├── dashboard.php              # Main listing discovery screen (protected)
├── listing.php                # Single listing detail view (protected)
├── profile.php                # User profile & logout (protected)
├── forgot_password.php        # Forgot password → OTP reset flow
│
├── admin/
│   ├── index.php              # Admin login page (standalone, no link from user app)
│   ├── dashboard.php          # Admin listings management panel (protected)
│   ├── add_listing.php        # Add new listing form + image upload handler
│   ├── edit_listing.php       # Edit existing listing (prefilled form)
│   └── reports.php            # Reported content queue
│
├── actions/
│   ├── register.php           # POST handler: validate + insert user + send OTP
│   ├── login.php              # POST handler: verify credentials + set session
│   ├── verify_otp.php         # POST handler: check OTP + mark user verified
│   ├── resend_otp.php         # POST handler: regenerate + resend OTP
│   ├── logout.php             # Destroys session + redirects
│   ├── submit_report.php      # POST handler: insert report record
│   └── admin/
│       ├── admin_login.php    # POST handler: validate admin credentials + set session
│       ├── save_listing.php   # POST handler: insert or update listing + upload image
│       └── delete_listing.php # POST handler: soft/hard delete listing
│
├── includes/
│   ├── db.php                 # mysqli connection (host, user, pass, db from config)
│   ├── config.php             # Constants: DB credentials, SMTP settings, app URL
│   ├── auth_guard.php         # Reusable session check — include at top of protected pages
│   ├── admin_guard.php        # Admin-only session check
│   ├── mailer.php             # PHPMailer wrapper — sendOTP($to, $code)
│   └── helpers.php            # Utility functions: sanitize input, format time, etc.
│
├── assets/
│   ├── css/
│   │   ├── styles.css         # User-facing styles (your co-workers' file, unmodified)
│   │   └── admin.css          # Admin portal styles (your co-workers' file, unmodified)
│   ├── js/
│   │   ├── script.js          # User-facing JS (your co-workers' file, unmodified)
│   │   └── admin.js           # Admin portal JS (your co-workers' file, unmodified)
│   └── images/
│       ├── iSkout_Logo.png    # App logo (also used as favicon)
│       └── PUP_Logo.png       # PUP seal (used on admin landing)
│
├── uploads/
│   └── listings/              # Merchant photos uploaded by admin (writable directory)
│
└── favicon.ico                # Converted from iSkout_Logo.png (16×16 or 32×32 ICO)
```

> **Hosting note:** On InfinityFree, set the `uploads/listings/` folder permissions to `755` via the File Manager. The rest of the codebase can remain at default permissions.

---

## 6. Authentication & OTP Email Verification

### 6.1 Registration Flow

1. Student fills in PUP Webmail + password + confirm password on `index.php` (register screen).
2. POST to `actions/register.php` — validates that email ends in `@iskolar.pup.edu.ph` (or the correct PUP domain), hashes password with `password_hash()`, inserts user with `is_verified = 0`, generates a random 4-digit OTP, stores it in `otp_tokens` with a 10-minute expiry, and sends the OTP via email.
3. User is redirected to `verify.php` (session stores the pending webmail).
4. On submit, POST to `actions/verify_otp.php` — matches token, checks expiry, marks `is_verified = 1`, destroys OTP record, sets full session, redirects to `dashboard.php`.

### 6.2 OTP Email — What You Need

Use **PHPMailer** (download the 3 core files manually — no Composer needed for InfinityFree). Configure it in `includes/mailer.php` to connect via **SMTP** to a free transactional email service. Recommended: **Brevo (formerly Sendinblue)** — free tier sends 300 emails/day, provides SMTP credentials, and works reliably from shared hosting IPs. Alternatively, use **Gmail SMTP** with an App Password if the admin has a Gmail account. Store SMTP host, port (587), username, and password in `includes/config.php` as constants (never hardcode directly in mailer.php). On localhost/XAMPP, point PHPMailer at Gmail SMTP; it works without a local mail server.

### 6.3 Forgot Password Flow

1. Student enters webmail on `forgot_password.php`.
2. System sends a new OTP (same mechanism as registration).
3. Student enters OTP, then sets a new password on the same page (multi-step via PHP session state).

---

## 7. Admin Authentication

### 7.1 Separate Entry Point

The admin portal lives entirely under `admin/`. There is **no link to it anywhere in the student-facing app** — the `index.php` splash screen's "Admin Login?" link from the prototype frontend must be **removed** in the PHP version. Admin access is via direct URL only: `yourdomain.com/admin/` (or `localhost/iskout/admin/` locally).

### 7.2 Credentials Strategy

Admin credentials are **stored as constants in `includes/config.php`** rather than in the database, which is acceptable for a single-admin academic project. Example:

```php
define('ADMIN_USERNAME', 'admin@iskout.pup');
define('ADMIN_PASSWORD_HASH', password_hash('your-secure-password', PASSWORD_DEFAULT));
```

In `actions/admin/admin_login.php`, validate with `password_verify($_POST['password'], ADMIN_PASSWORD_HASH)`. **Do not store the plaintext password anywhere.** Generate the hash once using a local PHP snippet and paste the result into `config.php`. This approach avoids a separate admin table while remaining secure. If the project later needs multiple admins, migrate to a `admins` DB table at that point.

---

## 8. User-Facing Pages & Behavior

All screens from the prototype (`index.html` / `script.js`) are preserved visually. The PHP versions simply replace the static `navigateTo()` JavaScript calls with real `<form>` POSTs and PHP redirects for actions that require server interaction. Purely cosmetic navigation between screens that don't touch the server (e.g., switching between login and register tabs) can remain JavaScript-driven as in the prototype — no change needed.

| Screen | PHP File | Notes |
|---|---|---|
| Splash | `index.php` | Default landing; "Admin Login?" link removed |
| Login | `index.php` (tab/section) | POST → `actions/login.php` |
| Register | `index.php` (tab/section) | POST → `actions/register.php` |
| OTP Verify | `verify.php` | Requires `$_SESSION['pending_webmail']` |
| Dashboard | `dashboard.php` | Lists fetched from DB; search & filter via PHP/JS |
| Listing Detail | `listing.php?id=X` | Single listing + report modal |
| Profile | `profile.php` | Shows webmail; logout link |
| Forgot Password | `forgot_password.php` | OTP reset flow |

---

## 9. Admin Portal Pages & Behavior

| Screen | PHP File | Notes |
|---|---|---|
| Admin Login | `admin/index.php` | No session = show form; valid session = redirect to dashboard |
| Admin Dashboard | `admin/dashboard.php` | Listings grid; search/filter; Modify/Delete per card |
| Add Listing | `admin/add_listing.php` | Form + image upload → `actions/admin/save_listing.php` |
| Edit Listing | `admin/edit_listing.php?id=X` | Prefilled form → same save action |
| Reports Queue | `admin/reports.php` | Table of pending reports with listing reference and dismiss action |

Metrics on the admin dashboard (Total Businesses, Pop-up Merchants, Established Merchants) are computed with simple `COUNT()` SQL queries on page load — no caching needed at this scale.

---

## 10. Image Uploads

Admin-uploaded listing photos are saved to `uploads/listings/` with a sanitized, timestamped filename (e.g., `listing_1718123456_kapekuripot.jpg`) to prevent collisions and path traversal. PHP validates file type (MIME check, not just extension) and enforces a 2MB size limit before moving the upload. The stored path relative to the project root is saved in the `listings.image_path` DB column. On InfinityFree, confirm the `uploads/` directory is writable — test with a simple `is_writable()` check on the add listing page during development.

---

## 11. Favicon

In each HTML `<head>`, add:
```html
<link rel="icon" type="image/png" href="/assets/images/iSkout_Logo.png">
```
This works without converting to `.ico` in modern browsers. For maximum compatibility, also place a converted `favicon.ico` (32×32) in the project root — free online tools like `favicon.io` can generate it from the PNG. The `<link>` tag takes priority in modern browsers; the root `favicon.ico` is a silent fallback.

---

## 12. Local Development (XAMPP)

1. Place the `iskout/` folder inside `C:/xampp/htdocs/`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`, create database `iskout_db`, and run the SQL schema file (`database/schema.sql` — create this file with all `CREATE TABLE` statements).
4. Set DB credentials in `includes/config.php` (`localhost`, `root`, `""` by default on XAMPP).
5. Set SMTP credentials in `includes/config.php` to point at Gmail or Brevo.
6. Access the app at `http://localhost/iskout/` and the admin at `http://localhost/iskout/admin/`.

---

## 13. Deployment to InfinityFree

1. Register at infinityfree.com — get a free subdomain (e.g., `iskout.infinityfreeapp.com`) or connect a custom domain.
2. Use the built-in File Manager or an FTP client (FileZilla) to upload the project into the `htdocs/` directory.
3. In the InfinityFree Control Panel → MySQL Databases, create a new database and user. Update `includes/config.php` with the provided host, DB name, username, and password.
4. Import `database/schema.sql` via phpMyAdmin (available in the InfinityFree panel).
5. Set `uploads/listings/` to permission `755` via the File Manager.
6. No other configuration is required — the app is live.

> **Note:** InfinityFree blocks outbound SMTP on port 25 but allows port 587 (STARTTLS). PHPMailer configured with `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS` and `Port = 587` to Brevo or Gmail will work correctly.

---

## 14. Security Baseline

- All `$_POST` input sanitized with `htmlspecialchars()` and `mysqli_real_escape_string()` before DB use; prepared statements (`mysqli_prepare`) preferred for all queries.
- Passwords stored with `password_hash(PASSWORD_DEFAULT)` and verified with `password_verify()`.
- OTP tokens expire after 10 minutes and are deleted (or flagged `used = 1`) immediately after successful verification.
- Session is regenerated (`session_regenerate_id(true)`) on login to prevent fixation.
- File uploads validated by MIME type (`finfo_file()`) and extension whitelist (jpg, jpeg, png, webp only).
- Admin routes return HTTP 403 and redirect to `admin/index.php` for any request lacking `$_SESSION['role'] === 'admin'`.
- `includes/config.php` contains no secrets in version control — add it to `.gitignore` if using Git, and maintain a `config.sample.php` with placeholder values for teammates.

---

## 15. Gaps & Decisions Summary

| Topic | Decision |
|---|---|
| Admin credentials | Hardcoded as PHP constants in `config.php` (hashed, not plaintext) |
| Admin entry point | `admin/index.php` — no link from student app |
| OTP email service | PHPMailer + Brevo SMTP (free, 300/day) |
| OTP length | 4 digits, 10-minute expiry, single-use |
| Image storage | Local filesystem (`uploads/listings/`), path in DB |
| Frontend changes | Minimal — CSS/JS files untouched; PHP wraps existing HTML |
| Favicon | `iSkout_Logo.png` via `<link>` tag + root `favicon.ico` fallback |
| Forgot password | OTP-based reset, same mailer infrastructure |
| Search & filter | Server-side PHP query with `LIKE` + category JOIN; JS handles UI tab state |
| "Remember me" | 30-day cookie storing encrypted session token (simple implementation: `setcookie('remember_token', ...)` + `remember_tokens` DB table) |