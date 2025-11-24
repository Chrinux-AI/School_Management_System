# 🧪 Chat System Testing Guide

## Quick Test Scenarios

### Test 1: Search Users

1. Login as any role (admin/teacher/student/parent)
2. Navigate to chat page
3. Type in search bar (e.g., "john", "smith")
4. Verify users appear instantly after 2 characters
5. Check role colors: Students=Blue, Teachers=Green, Parents=Orange, Admin=Red

### Test 2: Browse by Role

1. Click "Students" button → Should show all students
2. Click "Teachers" button → Should show all teachers
3. Click "Parents" button → Should show all parents
4. Verify online status indicators (green dot)
5. Verify role badges display correctly

### Test 3: Start Chat from Search

1. Search for a user
2. Click "Start Chat" button
3. Verify conversation opens
4. Send a test message
5. Check that user is auto-added to Contacts

### Test 4: Contact Management

1. Click "Contacts" tab
2. Verify all saved contacts appear
3. Click star icon to favorite → Should turn gold
4. Click star again → Should unfavorite
5. Click trash icon → Should remove contact (with confirmation)

### Test 5: Multi-Role Access

1. Login as **Admin** → Navigate to `/admin/chat.php` → Should work ✅
2. Login as **Teacher** → Navigate to `/teacher/chat.php` → Should work ✅
3. Login as **Student** → Navigate to `/student/chat.php` → Should work ✅
4. Login as **Parent** → Navigate to `/parent/chat.php` → Should work ✅

### Test 6: Real-time Features

1. Open conversation
2. Type message but don't send → Other user should see "typing..."
3. Send message → Should appear instantly
4. Other user reads → Should see checkmark turn blue
5. React with emoji → Should appear on message
6. Check online status updates every 3 seconds

## Expected Behavior

### Search Bar

- Typing 1 character: No results (minimum 2)
- Typing 2+ characters: Instant results
- Empty search: Shows "Type to search users..."
- No matches: Shows "No users found"

### Tab Navigation

- **Chats**: Shows active conversations
- **Contacts**: Shows saved contacts (empty if none)
- **Users**: Shows search/role filter results

### User Cards

Each card should display:

- Avatar with initials (first letter of first + last name)
- Full name
- Role badge (colored pill)
- Email address
- Online indicator (green dot if online)
- Star button (favorites)
- Chat button (starts conversation)

### Contact Operations

- **Add Contact**: Happens automatically when starting chat
- **Favorite**: Click star icon, turns gold
- **Remove**: Click trash, shows confirmation, removes from list
- **Chat**: Click contact card, opens conversation

## API Response Verification

### Search All Users

```json
GET api/chat.php?action=search_all_users&q=john

Response:
{
  "success": true,
  "users": [
    {
      "id": 5,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@school.com",
      "role": "student",
      "is_online": 1,
      "is_contact": 0,
      "is_favorite": 0
    }
  ]
}
```

### Get Users by Role

```json
GET api/chat.php?action=get_user_by_role&role=student

Response:
{
  "success": true,
  "users": [
    {...}, {...}, {...}
  ]
}
```

### Get Contacts

```json
GET api/chat.php?action=get_contacts

Response:
{
  "success": true,
  "contacts": [
    {
      "contact_user_id": 5,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@school.com",
      "role": "student",
      "nickname": null,
      "is_favorite": 1,
      "is_online": 1,
      "added_at": "2025-01-15 10:30:00"
    }
  ]
}
```

## Error Handling Tests

### Test Invalid Role

```
GET api/chat.php?action=get_user_by_role&role=invalid
Expected: {"success": false, "error": "Invalid role"}
```

### Test Unauthorized Access

```
GET api/chat.php?action=get_contacts (without login)
Expected: {"success": false, "error": "Not logged in"}
```

### Test Add Duplicate Contact

```
POST api/chat.php action=add_contact contact_user_id=5
(Run twice)
Expected: Second call should not create duplicate
```

## Performance Checks

- Search response time: < 500ms
- Role filter load time: < 1 second
- Contact list load: < 300ms
- Real-time updates: Every 3 seconds
- Typing indicators: < 1 second delay

## Browser Console Checks

Open Developer Tools → Console, should see:

```
✅ No JavaScript errors
✅ API calls successful (200 status)
✅ No 404s on resources
✅ Real-time polling active
```

## Common Issues & Solutions

### Issue: Search not working

- Check: Minimum 2 characters typed
- Check: JavaScript console for errors
- Verify: api/chat.php accessible

### Issue: Role buttons not loading

- Check: Users exist for that role in database
- Check: API endpoint returns data
- Verify: Network tab shows 200 response

### Issue: Contacts not saving

- Check: User logged in
- Check: Database has chat_contacts table
- Verify: POST request sent successfully

### Issue: Chat not opening

- Check: Conversation created in database
- Check: currentConversationId set
- Verify: Messages div populated

## Database Verification

```sql
-- Check contacts table exists
SHOW TABLES LIKE 'chat_contacts';

-- Check saved contacts
SELECT * FROM chat_contacts WHERE user_id = 1;

-- Check favorite contacts
SELECT * FROM chat_contacts WHERE user_id = 1 AND is_favorite = 1;

-- Check recent interactions
SELECT * FROM chat_recent_contacts WHERE user_id = 1 ORDER BY last_interaction DESC;
```

## Success Criteria

✅ All 6 test scenarios pass
✅ Search works across all roles
✅ Contact save/remove/favorite functional
✅ All 4 roles can access chat
✅ Real-time features operational
✅ No JavaScript errors
✅ No PHP errors
✅ API returns valid JSON
✅ UI responsive and styled correctly
✅ Performance within acceptable limits

---

**Test Status**: Ready for Testing
**Last Updated**: 2025
**System**: WhatsApp/Telegram-style Chat with Contact Management
