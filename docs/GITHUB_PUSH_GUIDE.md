# 🚀 GitHub Push Guide

## Quick Push to GitHub

```bash
cd /opt/lampp/htdocs/attendance

# 1. Ensure remote is set
git remote -v

# 2. If remote not set, add it
git remote add origin https://github.com/Chrinux-AI/School_Management_System.git

# 3. Push to GitHub
git push -u origin master
```

## ⚠️ Before You Push

### 1. Verify No Secrets in Code

```bash
# Check for hardcoded credentials
grep -r "AC063981065db843" . --exclude-dir=.git --exclude-dir=vendor
grep -r "christolabiyi35@gmail.com" . --exclude-dir=.git --exclude-dir=vendor
grep -r "pgzoahiaxzsuersg" . --exclude-dir=.git --exclude-dir=vendor

# Should return NO results if properly cleaned
```

### 2. Verify .env.example Exists

```bash
ls -la .env.example
# Should show the example file
```

### 3. Verify .env is Ignored

```bash
cat .gitignore | grep ".env"
# Should show:
# .env
# .env.local
# .env.*.local
```

### 4. Check Git Status

```bash
git status
# Make sure .env is NOT listed (should be ignored)
```

## 🔒 If Push is Blocked by GitHub Secret Scanning

If you see this error:

```
remote: error: GH013: Repository rule violations found for refs/heads/master.
remote: - Push cannot contain secrets
```

### Solution A: Use Secret Bypass URL (Temporary)

GitHub will provide a URL like:

```
https://github.com/Chrinux-AI/School_Management_System/security/secret-scanning/unblock-secret/xxxxx
```

1. Open this URL in your browser
2. Click "Allow secret" (only if you've removed it from current commit)
3. Push again

### Solution B: Remove Secret from Git History (Recommended)

If the secret is in your git history:

```bash
# 1. Create a backup first!
cp -r /opt/lampp/htdocs/attendance /opt/lampp/htdocs/attendance_backup

# 2. Remove the secret from history using BFG Repo Cleaner
# Download BFG: https://rtyley.github.io/bfg-repo-cleaner/

# Or use git filter-branch (slower but built-in):
git filter-branch --force --index-filter \
  'git rm --cached --ignore-unmatch includes/config.php' \
  --prune-empty --tag-name-filter cat -- --all

# 3. Clean up
git reflog expire --expire=now --all
git gc --prune=now --aggressive

# 4. Force push (⚠️ WARNING: Rewrites history!)
git push origin --force --all
git push origin --force --tags
```

### Solution C: Fresh Start (Nuclear Option)

If all else fails:

```bash
# 1. Backup current work
cp -r /opt/lampp/htdocs/attendance /tmp/attendance_backup

# 2. Delete .git folder
cd /opt/lampp/htdocs/attendance
rm -rf .git

# 3. Reinitialize
git init
git add .
git commit -m "Initial commit - School Management System"

# 4. Push to GitHub
git remote add origin https://github.com/Chrinux-AI/School_Management_System.git
git branch -M master
git push -u origin master --force
```

## ✅ After Successful Push

### 1. Revoke Compromised Credentials

Even if push succeeds, if secrets were exposed:

#### Gmail App Password

1. Go to: https://myaccount.google.com/apppasswords
2. Delete the compromised app password
3. Create a new one
4. Update your `.env` file

#### Twilio Credentials

1. Go to: https://console.twilio.com
2. Navigate to Account > API keys & tokens
3. Revoke/rotate the Auth Token
4. Update your `.env` file

### 2. Set Up Environment Variables

Follow the guide in `docs/ENVIRONMENT_SETUP.md`

### 3. Test Locally

```bash
# Start Apache
sudo /opt/lampp/lampp start

# Test the application
open http://localhost/attendance
```

### 4. Update GitHub Repository Settings

1. Go to: https://github.com/Chrinux-AI/School_Management_System/settings
2. Add repository secrets for CI/CD (if using)
3. Enable branch protection on `master`
4. Set up GitHub Actions (optional)

## 📋 Pre-Deployment Checklist

- [x] Secrets removed from code
- [x] `.env.example` created
- [x] `.env` in `.gitignore`
- [x] Config files use `getenv()`
- [x] All commits clean
- [x] README.md updated
- [x] Documentation complete
- [x] Tests passing (if any)
- [ ] Production credentials secured
- [ ] Backup created
- [ ] Team notified

## 🎯 Next Steps After Push

1. **Clone on another machine** to verify it works:

   ```bash
   git clone https://github.com/Chrinux-AI/School_Management_System.git
   cd School_Management_System
   cp .env.example .env
   # Edit .env with your credentials
   ```

2. **Set up CI/CD** (optional):

   - GitHub Actions for automated testing
   - Deploy hooks for production

3. **Protect sensitive branches**:
   - Require pull request reviews
   - Require status checks
   - Enforce signed commits

## 🆘 Need Help?

- GitHub Docs: https://docs.github.com
- Secret Scanning: https://docs.github.com/en/code-security/secret-scanning
- Git Credential Helper: https://git-scm.com/docs/gitcredentials

---

**Happy Coding! 🎉**
