# 📋 Student Panel - File Linking & Navigation Summary

**Date:** November 24, 2025
**Version:** 2.1.0
**Status:** ✅ Navigation System Complete

---

## 🎯 Overview

This document provides a complete map of all file links, navigation paths, and API integrations for the Student Panel in the Student Attendance Management System (SAMS). All links follow the structure defined in the comprehensive project overview.

---

## 📁 Navigation Structure

### **Primary Navigation File**

**File:** `/includes/student-nav.php`
**Purpose:** Comprehensive student-specific sidebar navigation
**Included in:** All student panel pages via `<?php include '../includes/student-nav.php'; ?>`

### **Navigation Sections**

#### 1. **Core** (Essential Daily Tools)

```
dashboard-enhanced.php → Enhanced dashboard with widgets
checkin.php → QR/Geolocation check-in
attendance.php → View attendance history
schedule.php → Class timetable with filters
```

**Links:**

- **Dashboard** (`dashboard-enhanced.php`)

  - "Check In" button → `checkin.php`
  - "View Messages" button → `messages.php`
  - "Today's Schedule" button → `schedule.php`
  - "Submit Work" button → `assignments.php`
  - Widget "View" links → Various pages (e.g., assignments, grades)
  - AJAX calls → `/api/notifications.php?action=get_unread_count`

- **Check-in** (`checkin.php`)

  - "Check In" form post → Self (processes then redirects to dashboard)
  - QR Code scan → `/api/attendance.php?action=checkin_qr`
  - Geolocation verify → `/api/attendance.php?action=verify_location`

- **Attendance** (`attendance.php`)

  - Filter form → Self with query params `?date=YYYY-MM-DD`
  - "Request Correction" button → `/api/attendance.php?action=request_correction`
  - Export links → `?action=export&format=pdf`

- **Schedule** (`schedule.php`)
  - Filter dropdowns → Self via AJAX reload
  - "Add to Calendar" links → `.ics` file download
  - Class links → Details modal (no navigation)

#### 2. **Academic** (Learning Support)

```
assignments.php → View and submit assignments (Badge: pending count)
grades.php → Grade analytics with charts
class-registration.php → Enroll in classes
events.php → School events calendar
lms-portal.php → Learning Management System integration (Badge: "LMS")
```

**Links:**

- **Assignments** (`assignments.php`)

  - "View" → `?assign_id=ID` (same page, detail view)
  - "Submit" button → `/api/assignments.php?action=submit` (multipart form)
  - File upload → `/api/assignments.php?action=upload_file`
  - Download material → `/uploads/assignments/filename`

- **Grades** (`grades.php`)

  - "View Details" → `?grade_id=ID`
  - Charts load via → `/api/grades.php?action=get_chart_data`
  - "Compare" button → Loads comparison chart (same page)
  - Export → `/api/grades.php?action=export&format=csv`

- **Class Registration** (`class-registration.php`)

  - "Register" button → `/api/classes.php?action=register`
  - "Drop" button → `/api/classes.php?action=drop`
  - Search → Self with `?search=query`

- **Events** (`events.php`)

  - "RSVP" button → `/api/events.php?action=rsvp`
  - "View Details" → Modal (no navigation)
  - Calendar sync → `.ics` download

- **LMS Portal** (`lms-portal.php`)
  - "Launch Course" → External LMS via LTI deep link
  - "Sync Data" → `/api/lti.php?action=sync_courses`
  - Single Sign-On → `/api/lti.php?action=launch`

#### 3. **Communication** (Messaging & Collaboration)

```
messages.php → WhatsApp-style messaging (Badge: unread count)
notifications.php → Notification center (Badge: unread count)
communication.php → Peer-to-peer chat (Badge: "NEW")
../messages.php → Universal inbox
../notices.php → Notice board
../forum/index.php → The Quad Forum
study-groups.php → Collaborative learning
```

**Links:**

- **Messages** (`messages.php`)

  - "Compose" modal → Posts to `/student/api/messaging-enhanced.php?action=send_message`
  - "Inbox" tab → `/student/api/messaging-enhanced.php?action=get_conversations`
  - "Groups" tab → `/student/api/messaging-enhanced.php?action=get_groups`
  - "Settings" → Opens settings panel (same page)
  - Contact click → Loads thread via `/student/api/messaging-enhanced.php?action=get_messages&contact_id=ID`
  - "Send" button → `/student/api/messaging-enhanced.php?action=send_message`
  - File attach → `/student/api/messaging-enhanced.php?action=send_message` (multipart)
  - Voice note → `/student/api/messaging-enhanced.php?action=upload_voice_note`
  - Search → `/student/api/messaging-enhanced.php?action=search_messages&q=query`
  - Archive → `/student/api/messaging-enhanced.php?action=archive_conversation`
  - Block user → `/student/api/messaging-enhanced.php?action=block_user`

- **Notifications** (`notifications.php`)

  - "Mark All Read" → `/api/notifications.php?action=mark_all_read`
  - "Clear All" → `/api/notifications.php?action=clear_all`
  - Individual "Read" → `/api/notifications.php?action=mark_read&id=ID`
  - "Delete" → `/api/notifications.php?action=delete&id=ID`
  - "Settings" button → Opens settings panel (same page)
  - Filter buttons → Self with `?filter=unread&category=attendance`
  - Notification click → Navigates to `link` field (e.g., `/student/assignments.php`)

- **Communication** (`communication.php`)

  - Peer chat interface (existing WhatsApp-style features)
  - Links to `/api/student-messaging.php`

- **Universal Inbox** (`../messages.php`)

  - Shared messaging system
  - Links to `/api/messaging.php`

- **Notice Board** (`../notices.php`)

  - View announcements
  - No outbound links

- **Forum** (`../forum/index.php`)

  - Community discussions
  - Internal forum links

- **Study Groups** (`study-groups.php`)
  - "Create Group" → `/api/groups.php?action=create`
  - "Join Group" → `/api/groups.php?action=join`
  - Group links → Group chat interface

#### 4. **Analytics** (Performance Tracking)

```
analytics.php → Performance analytics
reports.php → Generate custom reports
```

**Links:**

- **Analytics** (`analytics.php`)

  - Charts load via → `/api/analytics.php?action=get_performance_data`
  - "Export" → `/api/analytics.php?action=export&format=pdf`
  - Date filters → Self with query params

- **Reports** (`reports.php`)
  - "Generate Report" → `/api/reports.php?action=generate`
  - Filter form → Self
  - "Download" → `/api/reports.php?action=download&id=ID`

#### 5. **Tools** (Account Management)

```
profile.php → Manage profile
id-card.php → Digital ID card
settings.php → Account settings
emergency-alerts.php → Emergency notifications
```

**Links:**

- **Profile** (`profile.php`)

  - "Save" button → Self (POST)
  - Photo upload → `/api/profile.php?action=upload_photo`
  - "Change Password" → Modal posting to `/api/profile.php?action=change_password`

- **ID Card** (`id-card.php`)

  - "Download PDF" → Generates PDF
  - "Print" → Browser print dialog
  - QR code → Generated on page load

- **Settings** (`settings.php`)

  - "Save Settings" → Self (POST)
  - Notification toggles → AJAX to `/api/notifications.php?action=save_settings`
  - Theme toggle → LocalStorage + page reload

- **Emergency Alerts** (`emergency-alerts.php`)
  - "Acknowledge" → `/api/emergency.php?action=acknowledge`
  - Real-time updates via polling

---

## 🔌 API Integration Map

### **Messaging API** (`/student/api/messaging-enhanced.php`)

**Endpoints (19 total):**

1. `send_message` - Send direct message
2. `get_conversations` - List conversations
3. `get_messages` - Fetch thread
4. `delete_message` - Remove message
5. `react_to_message` - Add emoji reaction
6. `create_group` - Create group chat
7. `send_group_message` - Post to group
8. `get_groups` - List user groups
9. `get_group_messages` - Fetch group thread
10. `set_typing` - Notify typing status
11. `get_typing_status` - Check typing
12. `search_messages` - Full-text search
13. `archive_conversation` - Archive thread
14. `export_conversation` - Download as text
15. `report_message` - Flag content
16. `block_user` - Block contact
17. `unblock_user` - Unblock contact
18. `upload_voice_note` - Send audio
19. `get_messaging_stats` - Usage analytics

**Called from:**

- `messages.php` - All message operations
- `dashboard-enhanced.php` - Widgets (unread count)
- `student-nav.php` - Real-time polling (30s interval)

### **Notification API** (`/api/notifications.php`)

**Endpoints (11 total):**

1. `get_all` - Fetch notifications
2. `get_unread_count` - Real-time counter
3. `mark_read` - Update status
4. `mark_all_read` - Bulk mark
5. `delete` - Remove notification
6. `clear_all` - Delete all
7. `create` - Admin: create
8. `broadcast` - Admin: send to role
9. `save_settings` - Update preferences
10. `get_settings` - Fetch preferences
11. `get_stats` - Analytics dashboard

**Called from:**

- `notifications.php` - All notification operations
- `dashboard-enhanced.php` - Widgets
- `student-nav.php` - Real-time polling (30s interval)

### **Other APIs**

- `/api/attendance.php` - Attendance operations
- `/api/assignments.php` - Assignment management
- `/api/grades.php` - Grade operations
- `/api/lti.php` - LMS integration
- `/api/analytics.php` - Performance data

---

## 🎨 Widget System (Dashboard)

### **Dashboard Widgets** (`dashboard-enhanced.php`)

All widgets are draggable and save layout to localStorage.

**Widget List:**

1. **Attendance Overview**

   - Displays: Present, Late, Absent, Rate
   - No outbound links

2. **Pending Assignments**

   - "View" links → `assignments.php?assign_id=ID`
   - "All Assignments" link → `assignments.php`

3. **Achievements**

   - Badge display (no links)
   - "View All" → Future feature

4. **Attendance Streak**

   - "View History" → `attendance.php`

5. **Recent Notifications**

   - Notification clicks → Navigate to `link` field
   - "View All" → `notifications.php`

6. **Upcoming Events**

   - Event clicks → `events.php?event_id=ID`
   - "View Calendar" → `events.php`

7. **Grade Average**

   - "View Details" → `grades.php`

8. **Quick Actions**
   - "Check In Now" → `checkin.php`
   - "View Messages" → `messages.php`
   - "Today's Schedule" → `schedule.php`
   - "Submit Work" → `assignments.php`

---

## 📱 Real-Time Features

### **Polling Intervals**

Configured in `student-nav.php`:

```javascript
// Notification polling - Every 30 seconds
setInterval(function () {
  fetch("../api/notifications.php?action=get_unread_count")
    .then((res) => res.json())
    .then((data) => updateNotificationBadge(data.count));
}, 30000);

// Message polling - Every 30 seconds
setInterval(function () {
  fetch("../api/messaging-enhanced.php?action=get_messaging_stats")
    .then((res) => res.json())
    .then((data) => updateMessageBadge(data.stats.unread));
}, 30000);
```

### **Typing Indicators**

- Set typing: `set_typing` endpoint (3s TTL)
- Check status: `get_typing_status` endpoint (polled every 1s during active chat)

---

## 🔐 Authentication Flow

### **Session Validation**

All student pages include:

```php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}
```

### **Login to Dashboard Flow**

1. User visits `/login.php`
2. Form posts to self
3. `/includes/auth.php` validates credentials
4. Success: `header('Location: /student/dashboard-enhanced.php')`
5. Failure: Error message on `/login.php`

### **Logout Flow**

1. Sidebar "Logout" button → `/logout.php`
2. `/logout.php` destroys session
3. Redirects to `/index.php` or `/login.php`

---

## 📊 Database Queries

### **Dashboard Widgets**

```php
// Attendance stats
SELECT * FROM attendance_records WHERE student_id = ?

// Pending assignments
SELECT a.* FROM assignments a
JOIN class_enrollments ce ON a.class_id = ce.class_id
WHERE ce.student_id = ? AND a.due_date >= CURDATE()

// Unread notifications
SELECT * FROM notifications WHERE user_id = ? AND is_read = 0

// Badges
SELECT * FROM student_badges WHERE student_id = ?

// Streak
SELECT current_streak FROM attendance_streaks WHERE student_id = ?
```

### **Navigation Badges**

```php
// Unread messages
SELECT COUNT(*) FROM student_messages
WHERE to_student_id = ? AND is_read = 0

// Unread notifications
SELECT COUNT(*) FROM notifications
WHERE user_id = ? AND is_read = 0

// Pending assignments
SELECT COUNT(*) FROM assignments a
JOIN class_enrollments ce ON a.class_id = ce.class_id
WHERE ce.student_id = ? AND a.due_date >= CURDATE()
AND a.id NOT IN (SELECT assignment_id FROM assignment_submissions WHERE student_id = ?)
```

---

## 🎯 Key Features Summary

### ✅ **Implemented**

- Enhanced dashboard with 8 draggable widgets
- Comprehensive messaging (19 API endpoints)
- Notification center with categorization
- Real-time badge updates (30s polling)
- WhatsApp-style communication
- File attachments (5MB limit)
- Voice notes (2MB limit)
- Typing indicators & read receipts
- Message search & archiving
- User blocking & reporting
- Group chat functionality
- Customizable notification preferences
- Gamification (badges, streaks)
- Dedicated student navigation

### ⏳ **In Progress**

- Attendance & schedule features
- QR/Geolocation check-in
- Timetable with filters

### 📅 **Planned**

- Assignment submission system
- Grade analytics with charts
- Collaboration tools (forums, note sharing)
- AI study coach
- Portfolio builder
- Offline mode
- Multi-device sync

---

## 📖 Usage Examples

### **Sending a Message**

```javascript
// From messages.php "Send" button
const formData = new FormData();
formData.append("action", "send_message");
formData.append("to_user_id", 456);
formData.append("message", "Hello!");
formData.append("attachment", fileInput.files[0]);

fetch("/student/api/messaging-enhanced.php", {
  method: "POST",
  body: formData,
})
  .then((res) => res.json())
  .then((data) => {
    if (data.success) {
      // Refresh message list
      loadMessages(456);
    }
  });
```

### **Marking Notification as Read**

```javascript
// From notifications.php notification card click
function handleNotificationClick(id, link) {
  fetch("../api/notifications.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ action: "mark_read", id: id }),
  }).then(() => {
    if (link && link !== "#") {
      window.location.href = link;
    }
  });
}
```

### **Widget Drag & Drop**

```javascript
// From dashboard-enhanced.php
function saveWidgetLayout() {
  const layout = Array.from(widgets).map((w) => w.dataset.widget);
  localStorage.setItem("studentDashboardLayout", JSON.stringify(layout));
}

// Load on page load
function loadWidgetLayout() {
  const saved = localStorage.getItem("studentDashboardLayout");
  if (saved) {
    const layout = JSON.parse(saved);
    layout.forEach((widgetId) => {
      const widget = document.querySelector(`[data-widget="${widgetId}"]`);
      if (widget) container.appendChild(widget);
    });
  }
}
```

---

## 🔄 Integration with Project Overview

### **Alignment Checklist**

- ✅ All navigation links follow project structure
- ✅ API endpoints match specification
- ✅ File paths use relative URLs from root
- ✅ Session authentication on all pages
- ✅ Role-based redirects implemented
- ✅ Cyberpunk theme consistent
- ✅ Mobile-responsive design
- ✅ Real-time features (polling)
- ✅ LMS integration placeholders
- ✅ Security measures (CSRF, XSS prevention)

### **File Structure Compliance**

```
/student/
├── dashboard-enhanced.php ✅
├── messages.php ✅
├── notifications.php ✅
├── communication.php ✅
├── checkin.php ✅
├── attendance.php ✅
├── schedule.php ✅
├── assignments.php ✅
├── grades.php ✅
├── profile.php ✅
├── settings.php ✅
├── api/
│   └── messaging-enhanced.php ✅

/includes/
└── student-nav.php ✅

/api/
└── notifications.php ✅
```

---

**Status:** ✅ **COMPLETE - All file links and navigation paths documented**

**Next Phase:** Implement remaining features (Attendance/Schedule, Assignments/Grades, Collaboration Tools)

---

**Last Updated:** November 24, 2025
**Version:** 2.1.0
**Developed by:** SAMS Team
**Theme:** Cyberpunk Neon 🌃
