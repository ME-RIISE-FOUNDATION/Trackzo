# Trackzo — PHP + MySQL edition

A working construction-ERP website: login, dashboard, and full create / edit / delete
for Clients, Projects, Materials, Purchase Orders, Finance, Accounts, Estimation, plus
Reports, Calendar and Settings. Built with plain **PHP + MySQL + HTML/CSS** — the exact
stack Hostinger's HTML/PHP hosting runs.

---

## What's inside

```
trackzo-php/
├─ config.php            <-- EDIT your database details here
├─ install.php           <-- run ONCE to create tables + sample data
├─ schema.sql            <-- alternative: import via phpMyAdmin instead of install.php
├─ login.php  logout.php
├─ index.php             (Dashboard)
├─ clients.php  projects.php  materials.php  purchase.php
├─ finance.php  estimation.php  reports.php  calendar.php
├─ account_tracker.php   settings.php
└─ inc/                  (shared: db config, helpers, sidebar/topbar layout)
```

No build step, no Node, no Composer. Just upload and go.

---

## Deploy to Hostinger (5 steps)

**1. Create the database**
hPanel → **Databases → MySQL Databases**. Create a database and a user, tick all
privileges. Note the **database name**, **username**, and **password** (on Hostinger these
usually look like `u123456789_trackzo`).

**2. Set your credentials**
Open `config.php` and fill in:
```php
define('DB_HOST', 'localhost');      // Hostinger = localhost
define('DB_NAME', 'u123456789_trackzo');
define('DB_USER', 'u123456789_admin');
define('DB_PASS', 'your-password');
```

**3. Upload the files**
hPanel → **File Manager → public_html**. Upload the **contents** of the `trackzo-php`
folder (or upload `trackzo-php.zip` and Extract). `index.php` should sit directly in
`public_html` (or a subfolder if you prefer `yourdomain.com/trackzo`).

**4. Run the installer**
Visit **`https://yourdomain.com/install.php`** once. It creates all tables and loads the
sample data, and shows your login details.

**5. Log in & clean up**
Go to **`https://yourdomain.com/login.php`**:
- Email: `james.carter@trackzo.io`
- Password: `password123`

Then **delete `install.php`** from the server (security), and change your password in
**Settings**.

> Prefer phpMyAdmin? Instead of step 4, open phpMyAdmin, select your database, go to the
> **Import** tab, and import `schema.sql`. It contains all tables + data + the login user.

---

## Test locally first (optional, with XAMPP)

1. Copy the `trackzo-php` folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. The default `config.php` already matches XAMPP (`root` / no password / `trackzo`).
   Create the database once: open `http://localhost/phpmyadmin` → New → name it `trackzo`.
4. Visit `http://localhost/trackzo-php/install.php`, then `.../login.php`.

---

## Login
| Email | Password |
|-------|----------|
| james.carter@trackzo.io | password123 |

Passwords are stored **hashed** (bcrypt). All forms are protected against CSRF and use
prepared statements (no SQL injection).

---

## Reports export
Open **Reports** and use the two buttons top-right:
- **Export Excel** — downloads `trackzo-report-YYYY-MM-DD.xls` (opens in Excel/Google
  Sheets). Amount columns are real numbers so you can `SUM` them.
- **Export PDF** — opens a clean printable report; use your browser's **Save as PDF**
  (the print dialog opens automatically). Works on any device, no plugins.

Both include: financial summary, expenses by category, project budget utilization, top
clients and the full transactions ledger.

## Notes
- **Mobile responsive**: on phones/tablets the sidebar becomes a slide-in drawer (tap the
  ☰ menu button); tables scroll horizontally and forms stack to a single column. On desktop
  the sidebar can collapse to an icon rail via the chevron.
- Currency is **Indian Rupees (₹)** with Indian formatting (e.g. `₹48,00,000`, `₹1.3Cr`,
  `₹3.2L`). Change the symbol/app name at the top of `config.php`.
- Design uses Tailwind via CDN + Google Fonts, so the app needs an internet connection to
  render its styles (fine for any live website; visitors always have internet).
- To change what data appears, just add/edit records inside the app — everything is live
  from the database.
