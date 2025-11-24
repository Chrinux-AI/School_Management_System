# 🔒 Security Policy

## Supported Versions

We provide security updates for the following versions:

| Version | Supported | Status              |
| ------- | --------- | ------------------- |
| 2.1.x   | ✅ Yes    | Active development  |
| 2.0.x   | ✅ Yes    | Security fixes only |
| < 2.0   | ❌ No     | End of life         |

## 🚨 Reporting a Vulnerability

**Please do NOT report security vulnerabilities through public GitHub issues.**

We take security seriously and appreciate responsible disclosure of security vulnerabilities.

### Reporting Process

1. **Email Security Team**

   - Send details to: **security@sams.edu** (or your institution's security contact)
   - Use subject line: `[SECURITY] Vulnerability Report - SAMS`

2. **Include This Information**

   - Type of vulnerability (SQL injection, XSS, authentication bypass, etc.)
   - Affected version(s)
   - Steps to reproduce
   - Proof of concept (if available)
   - Potential impact assessment
   - Suggested fix (if you have one)

3. **Response Timeline**

   - **Initial Response**: Within 48 hours
   - **Status Update**: Within 7 days
   - **Fix Timeline**: Based on severity
     - Critical: 24-48 hours
     - High: 7 days
     - Medium: 30 days
     - Low: 90 days

4. **Disclosure Policy**
   - We request 90 days before public disclosure
   - We will credit you in security advisories (unless you prefer to remain anonymous)
   - We will notify you when a fix is released

### What to Expect

✅ **We will:**

- Acknowledge receipt within 48 hours
- Provide regular status updates
- Work with you to understand and validate the issue
- Credit you in our security advisory (with your permission)
- Notify you when the vulnerability is fixed

❌ **We will not:**

- Take legal action if you follow responsible disclosure
- Publicly disclose your identity without permission

---

## 🛡️ Security Best Practices

### For System Administrators

1. **Keep Software Updated**

   ```bash
   # Regularly update SAMS
   git pull origin main

   # Update dependencies
   composer update
   ```

2. **Use HTTPS Only**

   - Force SSL/TLS for all connections
   - LTI 1.3 requires HTTPS
   - Use valid SSL certificates (Let's Encrypt recommended)

3. **Database Security**

   - Use strong database passwords (16+ characters, mixed case, numbers, symbols)
   - Restrict database access to localhost
   - Enable MySQL slow query log for monitoring
   - Regular database backups (daily recommended)

4. **File Permissions**

   ```bash
   # Secure permissions
   chmod 755 /opt/lampp/htdocs/attendance
   chmod 644 /opt/lampp/htdocs/attendance/*.php
   chmod 600 /opt/lampp/htdocs/attendance/config/*.php
   chmod 700 /opt/lampp/htdocs/attendance/uploads

   # Protect sensitive files
   chmod 400 /opt/lampp/htdocs/attendance/config/lti_keys/private.key
   ```

5. **Environment Configuration**

   ```php
   // config/config.php
   define('ENVIRONMENT', 'production'); // NOT 'development'
   define('DEBUG_MODE', false); // Disable debug output
   define('LOG_ERRORS', true); // Log to file, not screen
   ```

6. **Regular Security Audits**
   - Review user access logs monthly
   - Monitor failed login attempts
   - Check for unauthorized database changes
   - Audit LMS integration logs

### For Developers

1. **Input Validation**

   ```php
   // Always validate and sanitize user input
   $user_input = filter_input(INPUT_POST, 'field_name', FILTER_SANITIZE_STRING);

   // Use prepared statements for ALL database queries
   $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
   $stmt->execute([$user_id]);
   ```

2. **Authentication & Authorization**

   ```php
   // Always check authentication
   require_admin(); // or require_teacher(), require_student(), require_parent()

   // Verify user owns the resource
   if ($resource_owner_id !== $_SESSION['user_id']) {
       http_response_code(403);
       exit('Unauthorized access');
   }
   ```

3. **XSS Prevention**

   ```php
   // Always escape output
   echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

   // Use Content Security Policy
   header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net");
   ```

4. **CSRF Protection**

   ```php
   // Generate token
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

   // Validate token
   if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
       die('CSRF token validation failed');
   }
   ```

5. **Password Security**

   ```php
   // Hash passwords with bcrypt
   $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

   // Verify passwords
   if (password_verify($input_password, $stored_hash)) {
       // Valid password
   }
   ```

6. **File Upload Security**

   ```php
   // Validate file types
   $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
   if (!in_array($_FILES['file']['type'], $allowed_types)) {
       die('Invalid file type');
   }

   // Rename uploaded files
   $safe_name = bin2hex(random_bytes(16)) . '.' . $extension;
   move_uploaded_file($_FILES['file']['tmp_name'], "uploads/$safe_name");
   ```

### For End Users

1. **Strong Passwords**

   - Minimum 12 characters
   - Mix of uppercase, lowercase, numbers, symbols
   - Don't reuse passwords from other sites
   - Use a password manager

2. **Account Security**

   - Enable two-factor authentication if available
   - Never share your login credentials
   - Log out when using shared computers
   - Report suspicious activity immediately

3. **Phishing Awareness**
   - Verify URLs before entering credentials
   - Don't click suspicious links in emails
   - SAMS will never ask for your password via email
   - Report phishing attempts to IT security

---

## 🔐 Security Features

SAMS includes built-in security features:

### Authentication

- ✅ Bcrypt password hashing (cost factor: 12)
- ✅ Session management with secure cookies
- ✅ Account lockout after 5 failed login attempts
- ✅ Password complexity requirements
- ✅ Session timeout (30 minutes inactivity)

### Authorization

- ✅ Role-Based Access Control (RBAC)
- ✅ Per-page authentication checks
- ✅ Resource ownership validation
- ✅ Admin approval for new registrations

### Data Protection

- ✅ PDO prepared statements (SQL injection prevention)
- ✅ XSS filtering on all user inputs
- ✅ CSRF token validation on forms
- ✅ Input sanitization and validation
- ✅ Output encoding with htmlspecialchars()

### LMS Integration Security

- ✅ LTI 1.3 standard compliance
- ✅ OpenID Connect authentication
- ✅ JWT token validation (RS256/RS384/RS512)
- ✅ Nonce validation (replay attack prevention)
- ✅ Public/private key encryption
- ✅ HTTPS enforcement for LTI endpoints

### Audit & Logging

- ✅ Login/logout logging
- ✅ Failed authentication tracking
- ✅ Database change auditing
- ✅ LMS sync logging
- ✅ Error logging (development mode only)

---

## 📋 Security Checklist

### Pre-Deployment

- [ ] All dependencies updated to latest secure versions
- [ ] HTTPS/SSL certificate installed and validated
- [ ] Database credentials use strong passwords
- [ ] File permissions set correctly (755/644/600)
- [ ] Debug mode disabled (`DEBUG_MODE = false`)
- [ ] Error display disabled (`display_errors = Off`)
- [ ] Default admin password changed
- [ ] CSRF protection enabled on all forms
- [ ] Input validation implemented on all user inputs
- [ ] LTI private keys generated with 2048-bit RSA
- [ ] Backup system configured and tested

### Post-Deployment

- [ ] Security audit completed
- [ ] Penetration testing performed
- [ ] User access logs reviewed
- [ ] Regular backup schedule confirmed
- [ ] Monitoring system configured
- [ ] Incident response plan documented
- [ ] Security team contact information published

---

## 🚦 Vulnerability Severity Levels

| Severity | Description                        | Response Time | Examples                                    |
| -------- | ---------------------------------- | ------------- | ------------------------------------------- |
| Critical | Immediate threat to system or data | 24-48 hours   | Remote code execution, SQL injection        |
| High     | Significant security risk          | 7 days        | Authentication bypass, privilege escalation |
| Medium   | Moderate risk requiring attention  | 30 days       | XSS, CSRF, information disclosure           |
| Low      | Minor security concern             | 90 days       | Rate limiting issues, weak defaults         |

---

## 📜 Security Compliance

SAMS is designed to comply with:

- **FERPA** (Family Educational Rights and Privacy Act) - Student data privacy
- **COPPA** (Children's Online Privacy Protection Act) - Child user protection
- **GDPR** (General Data Protection Regulation) - EU data protection (if applicable)
- **OWASP Top 10** - Common web application vulnerabilities
- **LTI 1.3 Security** - IMS Global Learning Consortium security standards
- **PCI DSS** (if processing payments) - Payment card data security

---

## 🔗 Security Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [LTI 1.3 Security Specification](https://www.imsglobal.org/spec/security/v1p0/)
- [MySQL Security Best Practices](https://dev.mysql.com/doc/refman/8.0/en/security.html)
- [Apache Security Tips](https://httpd.apache.org/docs/2.4/misc/security_tips.html)

---

## 📞 Contact

**Security Team**: security@sams.edu
**General Support**: support@sams.edu
**Project Maintainer**: See [CONTRIBUTING.md](CONTRIBUTING.md)

---

**Last Updated**: December 2024
**Version**: 2.1.0

Thank you for helping keep SAMS secure! 🔒
