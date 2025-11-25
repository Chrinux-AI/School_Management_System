# Environment Setup Guide

## Setting Up Your Environment

Before pushing to GitHub or deploying to production, you must configure your environment variables.

### Step 1: Create .env File

```bash
cp .env.example .env
```

### Step 2: Edit .env File

Open `.env` and fill in your actual credentials:

```bash
nano .env
# or
vim .env
# or use your favorite editor
```

### Step 3: Configure Your Credentials

#### Database Configuration

```env
DB_HOST=localhost
DB_NAME=attendance_system
DB_USER=root
DB_PASS=your_database_password
```

#### SMTP Configuration (Gmail)

1. Enable 2FA on your Gmail account
2. Generate an App Password: https://myaccount.google.com/apppasswords
3. Add credentials to .env:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-16-char-app-password
SMTP_FROM_EMAIL=your-email@gmail.com
SMTP_FROM_NAME=School Management System
```

#### Twilio Configuration (WhatsApp)

1. Sign up at https://www.twilio.com
2. Get your Account SID and Auth Token from Console
3. Add to .env:

```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
ADMIN_WHATSAPP_NUMBER=whatsapp:+1234567890
```

### Step 4: Secure Your .env File

The `.env` file is already in `.gitignore` - **NEVER commit it to GitHub!**

```bash
# Verify .env is ignored
git status
# .env should NOT appear in the list
```

### Step 5: Load Environment Variables (Optional)

For production servers, you can load .env using a package:

```bash
composer require vlucas/phpdotenv
```

Then in your PHP code:

```php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
```

## For Development

If you're just testing locally, you can use the default values in `config.php` for now, but remember to configure proper credentials before going to production.

## Troubleshooting

### GitHub Push Protection Error

If you see "Push cannot contain secrets":

1. ✅ Make sure `.env` exists and contains your real credentials
2. ✅ Verify `config.php` uses `getenv()` or has placeholder values
3. ✅ Check no real credentials are hardcoded in any committed files
4. ✅ Run: `git log -p | grep -i "twilio\|smtp"` to find leaked secrets
5. ✅ If secrets found in history, you need to rewrite history:

```bash
# Remove sensitive file from history
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch includes/config.php" \
  --prune-empty --tag-name-filter cat -- --all

# Force push (dangerous - only if necessary)
git push origin --force --all
```

### Better Approach: Create New Credentials

If secrets were leaked:

1. **Revoke the compromised credentials** (Twilio Console, Gmail App Passwords)
2. Generate new credentials
3. Update your `.env` file
4. Ensure config.php doesn't have hardcoded values
5. Commit and push

## Production Deployment

For production environments:

1. Use environment variables set by your hosting provider
2. Or use a secrets management service (AWS Secrets Manager, Azure Key Vault)
3. Never store credentials in code or config files
4. Use HTTPS for all communication
5. Regularly rotate credentials

---

**Remember:** Security is not optional! Always protect your credentials.
