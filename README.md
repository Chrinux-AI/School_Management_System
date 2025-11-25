# 🎓 Verdant School Management System

> **Complete 42-Module Education Platform** with AI Analytics, Biometric Attendance, LMS Integration & Advanced Cyberpunk UI

[![Version](https://img.shields.io/badge/version-3.0.0-blue.svg)](https://github.com/Chrinux-AI/School_Management_System)
[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-Proprietary-red.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-Production%20Ready-green.svg)]()

---

## 🚀 Quick Start

```bash
# Access Homepage
http://localhost/attendance/

# Login
http://localhost/attendance/login.php

# Default Admin Credentials
Username: admin
Password: Admin@123
```

---

## ✨ Key Features

- ✅ **42 Integrated Modules** - Complete school management solution
- ✅ **18 User Roles** - Admin, Teacher, Student, Parent + 14 specialized roles
- ✅ **Dual UI Themes** - Cyberpunk & Nature interfaces
- ✅ **PWA Support** - Install as mobile/desktop app
- ✅ **LMS Integration** - LTI 1.3 compatible
- ✅ **AI Analytics** - Advanced insights & predictions
- ✅ **Biometric Attendance** - QR code & fingerprint support
- ✅ **Real-time Messaging** - Broadcast & direct communication
- ✅ **Multi-language Ready** - i18n framework

---

## 📦 Installation

### Prerequisites

- PHP 8.0+
- MySQL 8.0+
- Apache 2.4 with mod_rewrite
- 512MB RAM minimum

### LAMPP Installation

```bash
# 1. Start LAMPP
sudo /opt/lampp/lampp start

# 2. Clone repository
cd /opt/lampp/htdocs/
git clone https://github.com/Chrinux-AI/School_Management_System.git attendance

# 3. Setup database
mysql -u root -h localhost --socket=/opt/lampp/var/mysql/mysql.sock
```

```sql
CREATE DATABASE attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendance_system;
SOURCE /opt/lampp/htdocs/attendance/database/verdant-sms-schema.sql;
EXIT;
```

```bash
# 4. Configure environment
cp .env.example .env
nano .env  # Edit with your credentials

# 5. Access system
http://localhost/attendance/
```

---

## ⚙️ Configuration

Create `.env` file in project root:

```env
# Database
DB_HOST=localhost
DB_NAME=attendance_system
DB_USER=root
DB_PASS=

# Email (Gmail)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_FROM_EMAIL=your-email@gmail.com
SMTP_FROM_NAME=School Management System

# Twilio (WhatsApp)
TWILIO_ACCOUNT_SID=your_sid
TWILIO_AUTH_TOKEN=your_token
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886

# App Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost/attendance
TIMEZONE=America/New_York
```

---

## 👥 User Roles

### Admin

- Full system control
- User management & approvals
- Student/Teacher ID assignment (STU20250001, TCH20250001)
- System settings & analytics
- **Dashboard**: `/admin/dashboard.php`

### Teacher

- Class management
- Attendance marking
- Grade management
- Parent communication
- **Dashboard**: `/teacher/dashboard.php`

### Student

- View schedule & grades
- Self check-in
- Submit assignments
- LMS access
- **Dashboard**: `/student/dashboard.php`

### Parent

- View children's progress
- Attendance monitoring
- Fee payments
- Teacher communication
- **Dashboard**: `/parent/dashboard.php`

---

## 📚 Module Overview

### Core Modules (9)

1. **Student Management** - Admissions, records, profiles
2. **Teacher Portal** - Class management, grading
3. **Attendance System** - QR, biometric, manual tracking
4. **Parent Portal** - Child monitoring
5. **Messaging** - Broadcast & direct messaging
6. **Academics** - Subjects, syllabus, exams
7. **Timetable** - Scheduling & periods
8. **Reports** - Analytics & insights
9. **Settings** - System configuration

### Extended Modules (33)

10. **Finance** - Fee management, invoicing, payments
11. **Library** - Book catalog, circulation
12. **Transport** - Routes, vehicles, drivers
13. **Hostel** - Room allocation, warden management
14. **HR** - Staff management, payroll
15. **Inventory** - Asset tracking
16. **Canteen** - Menu, orders, billing
17. **Medical** - Health records, appointments
18. **Events** - Calendar, bookings
19. **Examinations** - Test creation, grading
20. **Assignments** - Homework management
21. **Grades** - Marksheet generation
22. **Certificates** - Document generation
23. **ID Cards** - Digital & physical IDs
24. **Notifications** - Real-time alerts
25. **Analytics** - AI-powered insights
26. **LMS Integration** - Learning management
27. **Alumni** - Graduate portal
28. **Admissions** - Online application
29. **Scholarships** - Award management
30. **Discipline** - Incident tracking
31. **Counseling** - Student support
32. **Sports** - Activities management
33. **Clubs** - Extra-curricular tracking
34. **Voting** - Elections & polls
35. **Forum** - Discussion boards
36. **Chat** - Real-time messaging
37. **Video Conferencing** - Online classes
38. **Document Management** - File storage
39. **Backup & Restore** - Data protection
40. **Security Logs** - Audit trails
41. **PWA** - Progressive web app
42. **API** - REST endpoints

---

## 🔌 API Documentation

### Authentication

All API requests require valid session.

### Messaging API

**Send Message**:

```http
POST /api/messaging.php
Content-Type: application/x-www-form-urlencoded

action=send
&receiver_id=5
&subject=Meeting
&message=Tomorrow at 3PM
```

**Get Inbox**:

```http
GET /api/messaging.php?action=inbox
```

**Response**:

```json
{
  "success": true,
  "messages": [
    {
      "id": 42,
      "sender_name": "John Doe",
      "subject": "Meeting",
      "message": "Tomorrow at 3PM",
      "is_read": 0,
      "created_at": "2025-11-25 14:30:00"
    }
  ]
}
```

---

## 🔒 Security

- **Password Hashing**: bcrypt (cost 12)
- **SQL Injection**: Prepared statements
- **XSS Protection**: HTML escaping
- **CSRF Tokens**: Form validation
- **Session Security**: HTTPOnly cookies, 30-min timeout
- **File Upload**: Type validation, size limits
- **Email Verification**: 10-minute token expiry
- **Role-Based Access**: Permission checks on all pages

---

## 🐛 Troubleshooting

### Can't Login After Registration

- Check email for verification link
- Contact admin to approve account
- Admin assigns Student/Teacher ID

### Student ID Not Found

- Admin goes to `/admin/manage-ids.php`
- Assigns ID (STU20250001)

### Emails Not Sending

- Use Gmail App Password (not regular password)
- Enable "Less secure apps" OR use OAuth2
- Check firewall allows port 587

### Database Errors

- Check `/opt/lampp/logs/mysql_error.log`
- Verify database credentials in `.env`
- Run: `mysql -u root -p attendance_system`

---

## 📖 Documentation

- **Homepage**: `home.php` - Project overview with links
- **System Overview**: `system-overview.php` - Platform details
- **This README**: Complete setup guide

---

## 🗺️ Project Structure

```
/opt/lampp/htdocs/attendance/
├── index.php              # Entry point
├── home.php               # Homepage with all links
├── login.php              # Authentication
├── register.php           # Registration with email verification
├── admin/                 # Admin panel
├── teacher/               # Teacher portal
├── student/               # Student portal
├── parent/                # Parent portal
├── includes/              # Shared components
│   ├── config.php         # Configuration
│   ├── database.php       # PDO connection
│   ├── functions.php      # Helper functions
│   ├── cyber-nav.php      # Cyberpunk navigation
│   └── admin-nav.php      # Admin navigation
├── api/                   # REST API endpoints
├── assets/                # Static resources
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   └── images/           # Images
├── database/              # SQL schema files
└── scripts/               # Utility scripts
```

---

## 🤝 Contributing

We welcome contributions!

```bash
git clone https://github.com/Chrinux-AI/School_Management_System.git
cd School_Management_System
git checkout -b feature/your-feature
# Make changes
git commit -m "feat: add your feature"
git push origin feature/your-feature
```

---

## 📝 Changelog

### v3.0.0 (Nov 25, 2025)

- ✅ Added homepage with comprehensive links
- ✅ Consolidated all documentation
- ✅ Fixed sidebar collapse across all roles
- ✅ Removed 60+ redundant .md files
- ✅ Implemented .env loader for secrets
- ✅ Cleaned git history (removed hardcoded credentials)

---

## 📄 License

Proprietary software for educational institutions.

---

## 🙏 Acknowledgments

- Font Awesome - Icons
- Google Fonts - Typography (Orbitron, Inter)
- PHPMailer - Email functionality
- Chart.js - Analytics visualizations

---

## 📞 Support

- **GitHub Issues**: [Report Bug](https://github.com/Chrinux-AI/School_Management_System/issues)
- **Email**: support@verdantsms.com
- **Homepage**: http://localhost/attendance/

---

**Version**: 3.0.0
**Last Updated**: November 25, 2025
**Status**: ✅ Production Ready

Made with ❤️ by Verdant SMS Team
