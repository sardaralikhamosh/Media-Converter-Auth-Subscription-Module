# Media Converter — Auth & Subscription Module

A **plug-and-play** login/signup + subscription system for your existing
Core PHP media converter site. It adds:

- User registration & login
- Forgot / reset password
- 3-conversion free tier with automatic limit enforcement
- Upgrade page (Free → Pro → Business)
- Conversion logging per user
- Zero changes needed to existing converter logic (just 1 line per file)

---

## 📁 File Structure

Drop everything into your existing project root as-is:

```
your-project/
│
├── includes/               ← NEW (shared helpers)
│   ├── config.php
│   ├── db.php
│   ├── auth.php
│   └── conversion_guard.php
│
├── auth/                   ← NEW (auth pages)
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── forgot-password.php
│   ├── reset-password.php
│   └── assets/
│       ├── auth.css
│       └── upgrade.css
│
├── subscription/           ← NEW (upgrade/checkout)
│   ├── upgrade.php
│   └── checkout.php
│
├── sql/
│   └── schema.sql          ← Run this ONCE on your DB
│
├── index.php               ← EXISTING — add 1 line (see below)
├── document.php            ← EXISTING — add 1 line
└── ...all your other converter pages
```

---

## 🚀 Installation Steps

### Step 1 — Import the database schema

Open your MySQL client (phpMyAdmin, CLI, etc.) and run:

```sql
SOURCE /path/to/sql/schema.sql;
```

Or paste the contents of `sql/schema.sql` into phpMyAdmin's SQL tab.

This creates:
- `users` table
- `subscriptions` table
- `conversion_logs` table

---

### Step 2 — Configure `includes/config.php`

Edit the five values at the top:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('APP_URL', 'https://mediaconverter.dezinegenius.com');
```

---

### Step 3 — Copy module files to your project

Upload these folders/files to your server root:
- `includes/`
- `auth/`
- `subscription/`

---

### Step 4 — Protect existing converter pages (1 line per file)

At the very top of **each** existing PHP converter file, add:

```php
<?php
require_once __DIR__ . '/includes/conversion_guard.php';
// ... rest of your existing code unchanged
?>
```

**Files to update:**
- `index.php`
- `document.php`
- `jpg-to-png.php`
- `png-to-jpg.php`
- `jpg-to-pdf.php`
- ... every converter file

That's all! The guard automatically:
1. Redirects guests → login page
2. Redirects free users who've used 3 conversions → upgrade page
3. Does nothing else — your existing code runs normally

---

### Step 5 — (Optional) Show conversion counter banner

Inside the `<body>` of your converter pages, add one echo after the
`<body>` tag to show the usage progress bar:

```php
<body>
<?php echo conversion_banner(); ?>
<!-- rest of your existing HTML... -->
```

---

### Step 6 — Record conversions after successful conversion

Find the point in your code where a file is successfully converted and
call `record_conversion()`:

```php
// Example: after your ImageMagick/ffmpeg conversion succeeds
$success = record_conversion('jpg', 'png', $originalFileName, $fileSize);

if (!$success) {
    // Shouldn't happen because conversion_guard already checked,
    // but handle gracefully just in case
    header('Location: ' . APP_URL . '/subscription/upgrade.php?limit=1');
    exit;
}
```

---

### Step 7 — Add nav links (optional but recommended)

In your existing navbar/header, add login/logout links:

```php
<?php require_once __DIR__ . '/includes/auth.php'; ?>

<?php if (is_logged_in()): ?>
    <span><?= htmlspecialchars(current_user()['name']) ?></span>
    <a href="/auth/logout.php">Sign Out</a>
<?php else: ?>
    <a href="/auth/login.php">Sign In</a>
    <a href="/auth/register.php">Sign Up</a>
<?php endif; ?>
```

---

## 💳 Payment Integration

The checkout page (`subscription/checkout.php`) has a "Simulate Payment"
button for development/testing. For real payments, replace it with:

### Stripe (recommended)

1. Install Stripe PHP SDK: `composer require stripe/stripe-php`
2. In `checkout.php`, create a Checkout Session:

```php
\Stripe\Stripe::setApiKey('sk_live_YOUR_KEY');

$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'usd',
            'unit_amount' => (int)($planInfo['price'] * 100),
            'product_data' => ['name' => $planInfo['label'] . ' Plan'],
            'recurring' => ['interval' => 'month'],
        ],
        'quantity' => 1,
    ]],
    'mode' => 'subscription',
    'success_url' => APP_URL . '/subscription/success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'  => APP_URL . '/subscription/upgrade.php',
]);

header('Location: ' . $session->url);
exit;
```

3. Create `subscription/success.php` to verify the session and update
   the user's plan (same logic as the simulate block).

---

## ⚙️ Customisation

| What | Where |
|------|-------|
| Change free limit (default: 3) | `includes/config.php` → `FREE_CONVERSIONS_LIMIT` |
| Change plan prices | `includes/config.php` → `PLANS` array |
| Restyle auth pages | `auth/assets/auth.css` |
| Restyle upgrade page | `auth/assets/upgrade.css` |
| Make limit per-day instead of total | `includes/auth.php` → `has_reached_limit()` |

---

## 🔒 Security Notes

- Passwords are hashed with `password_hash()` (bcrypt)
- Sessions are regenerated on login
- All user input is parameterised (PDO prepared statements)
- CSRF protection: add a CSRF token to forms for production
- Email enumeration is prevented on the forgot-password page

---

## 📋 Quick Checklist

- [ ] Run `sql/schema.sql` on your database
- [ ] Edit `includes/config.php` with DB credentials and APP_URL
- [ ] Upload `includes/`, `auth/`, `subscription/` folders
- [ ] Add `require_once __DIR__ . '/includes/conversion_guard.php';` to every converter PHP file
- [ ] (Optional) Add `echo conversion_banner();` after `<body>` tags
- [ ] (Optional) Add `record_conversion(...)` after successful conversions
- [ ] (Optional) Add nav login/logout links to your header
- [ ] Replace the simulate-payment block with a real payment gateway
