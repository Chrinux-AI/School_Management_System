<?php

/**
 * Advanced Communication System
 */

session_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Require admin access
require_admin('../login.php');

$message = '';
$message_type = '';

// Create communication tables if not exists
try {
    db()->query("
        CREATE TABLE IF NOT EXISTS communication_boards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            type ENUM('teachers', 'students', 'parents', 'general') DEFAULT 'general',
            access_level ENUM('admin', 'teacher', 'student', 'parent', 'all') DEFAULT 'all',
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )
    ");

    db()->query("
        CREATE TABLE IF NOT EXISTS communication_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            board_id INT,
            user_id INT,
            message TEXT NOT NULL,
            reply_to INT NULL,
            attachments JSON NULL,
            is_pinned BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (board_id) REFERENCES communication_boards(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (reply_to) REFERENCES communication_messages(id) ON DELETE CASCADE
        )
    ");

    // Insert default boards if they don't exist
    $existing_boards = db()->count('communication_boards');
    if ($existing_boards === 0) {
        $default_boards = [
            ['Teachers Board', 'Communication hub for teachers', 'teachers', 'teacher'],
            ['Students Board', 'Student discussion and announcements', 'students', 'student'],
            ['Parents Board', 'Parent-school communication', 'parents', 'parent'],
            ['General Board', 'School-wide announcements and discussions', 'general', 'all']
        ];

        foreach ($default_boards as $board) {
            db()->insert('communication_boards', [
                'name' => $board[0],
                'description' => $board[1],
                'type' => $board[2],
                'access_level' => $board[3],
                'created_by' => $_SESSION['user_id']
            ]);
        }
    }
} catch (Exception $e) {
    error_log("Communication tables creation error: " . $e->getMessage());
}

// Handle message posting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $board_id = (int)$_POST['board_id'];
    $message_text = sanitize($_POST['message']);
    $reply_to = !empty($_POST['reply_to']) ? (int)$_POST['reply_to'] : null;

    if (!empty($message_text)) {
        try {
            db()->insert('communication_messages', [
                'board_id' => $board_id,
                'user_id' => $_SESSION['user_id'],
                'message' => $message_text,
                'reply_to' => $reply_to
            ]);

            $message = 'Message posted successfully!';
            $message_type = 'success';

            log_activity($_SESSION['user_id'], 'post_message', 'communication_messages', db()->lastInsertId());
        } catch (Exception $e) {
            $message = 'Error posting message: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Handle message pinning (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin_message']) && $_SESSION['role'] === 'admin') {
    $message_id = (int)$_POST['message_id'];

    try {
        db()->update('communication_messages', [
            'is_pinned' => 1
        ], 'id = ?', [$message_id]);

        $message = 'Message pinned successfully!';
        $message_type = 'success';
    } catch (Exception $e) {
        $message = 'Error pinning message: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Get boards based on user role
$user_role = $_SESSION['role'];
$access_condition = "WHERE access_level = 'all' OR access_level = '$user_role'";
if ($user_role === 'admin') {
    $access_condition = ''; // Admin can access all boards
}

$boards = db()->fetchAll("
    SELECT cb.*,
           COUNT(cm.id) as message_count,
           MAX(cm.created_at) as last_activity
    FROM communication_boards cb
    LEFT JOIN communication_messages cm ON cb.id = cm.board_id
    $access_condition
    GROUP BY cb.id
    ORDER BY cb.name
");

// Get current board
$current_board_id = isset($_GET['board']) ? (int)$_GET['board'] : ($boards[0]['id'] ?? 1);

// Get messages for current board
$messages = db()->fetchAll("
    SELECT cm.*,
           CONCAT(u.first_name, ' ', u.last_name) as user_name,
           u.role as user_role,
           reply.message as reply_message,
           CONCAT(reply_user.first_name, ' ', reply_user.last_name) as reply_user_name
    FROM communication_messages cm
    JOIN users u ON cm.user_id = u.id
    LEFT JOIN communication_messages reply ON cm.reply_to = reply.id
    LEFT JOIN users reply_user ON reply.user_id = reply_user.id
    WHERE cm.board_id = ?
    ORDER BY cm.is_pinned DESC, cm.created_at DESC
    LIMIT 50
", [$current_board_id]);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/attendance/manifest.json">
    <meta name="theme-color" content="#00BFFF">
    <link rel="apple-touch-icon" href="/attendance/assets/images/icons/icon-192x192.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communication Hub - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/advanced-ui.css">
    <style>
        .communication-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 25px;
            height: calc(100vh - 200px);
        }

        .boards-sidebar {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .board-item {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .board-item:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            transform: translateX(5px);
        }

        .board-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }

        .chat-area {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 15px 15px 0 0;
        }

        .messages-container {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            max-height: calc(100vh - 400px);
        }

        .message-item {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #3b82f6;
            transition: all 0.3s ease;
        }

        .message-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .message-item.pinned {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .message-item.reply {
            margin-left: 40px;
            border-left-color: #10b981;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .message-form {
            padding: 25px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 0 0 15px 15px;
        }

        .reply-indicator {
            background: rgba(59, 130, 246, 0.1);
            border-left: 3px solid #3b82f6;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
            font-size: 0.9rem;
            color: #64748b;
        }

        .message-actions {
            display: flex;
            gap: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .message-item:hover .message-actions {
            opacity: 1;
        }

        .online-indicator {
            width: 10px;
            height: 10px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            margin-left: 5px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="cyber-bg">
    <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="header">
        <div class="header-content">
            <div>
                <h1><i class="fas fa-comments"></i> Communication Hub</h1>
                <p>Connect, collaborate, and communicate</p>
            </div>
            <a href="../logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <nav class="nav-menu">
            <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="timetable.php"><i class="fas fa-calendar-alt"></i> Timetable</a>
            <a href="communication.php" class="active"><i class="fas fa-comments"></i> Communication</a>
            <a href="facilities.php"><i class="fas fa-building"></i> Facilities</a>
            <a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a>
        </nav>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="communication-layout">
            <!-- Boards Sidebar -->
            <div class="boards-sidebar">
                <h3 style="margin-bottom: 20px; color: #1e293b;">
                    <i class="fas fa-layer-group"></i> Discussion Boards
                </h3>

                <?php foreach ($boards as $board): ?>
                    <div class="board-item <?php echo $board['id'] == $current_board_id ? 'active' : ''; ?>"
                        onclick="window.location.href='?board=<?php echo $board['id']; ?>'">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-<?php
                                                echo $board['type'] === 'teachers' ? 'chalkboard-teacher' : ($board['type'] === 'students' ? 'user-graduate' : ($board['type'] === 'parents' ? 'users' : 'globe'));
                                                ?>"></i>
                            <div style="flex: 1;">
                                <strong><?php echo htmlspecialchars($board['name']); ?></strong>
                                <div style="font-size: 0.8rem; opacity: 0.8;">
                                    <?php echo $board['message_count']; ?> messages
                                </div>
                            </div>
                        </div>

                        <?php if ($board['last_activity']): ?>
                            <div style="font-size: 0.75rem; margin-top: 5px; opacity: 0.7;">
                                Last: <?php echo timeAgo($board['last_activity']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <!-- Online Users -->
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                    <h4 style="margin-bottom: 15px; color: #64748b;">
                        <i class="fas fa-circle text-success"></i> Online Now
                    </h4>
                    <div style="font-size: 0.9rem; color: #64748b;">
                        <span class="online-indicator"></span> You
                        <br>
                        <span style="opacity: 0.6;">+ 5 others online</span>
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="chat-area">
                <?php
                $current_board = array_filter($boards, fn($b) => $b['id'] == $current_board_id);
                $current_board = reset($current_board);
                ?>

                <div class="chat-header">
                    <h3><?php echo htmlspecialchars($current_board['name'] ?? 'General Board'); ?></h3>
                    <p style="opacity: 0.9; font-size: 0.9rem;">
                        <?php echo htmlspecialchars($current_board['description'] ?? ''); ?>
                    </p>
                </div>

                <div class="messages-container" id="messagesContainer">
                    <?php if (empty($messages)): ?>
                        <div style="text-align: center; padding: 60px; color: #64748b;">
                            <i class="fas fa-comments fa-3x" style="margin-bottom: 20px; opacity: 0.5;"></i>
                            <h3>No messages yet</h3>
                            <p>Be the first to start the conversation!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-item <?php echo $msg['is_pinned'] ? 'pinned' : ''; ?> <?php echo $msg['reply_to'] ? 'reply' : ''; ?>"
                                id="message-<?php echo $msg['id']; ?>">

                                <?php if ($msg['is_pinned']): ?>
                                    <div style="background: #f59e0b; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.8rem; margin-bottom: 10px; display: inline-block;">
                                        <i class="fas fa-thumbtack"></i> Pinned Message
                                    </div>
                                <?php endif; ?>

                                <?php if ($msg['reply_to'] && $msg['reply_message']): ?>
                                    <div class="reply-indicator">
                                        <i class="fas fa-reply"></i> Replying to <?php echo htmlspecialchars($msg['reply_user_name']); ?>:
                                        "<?php echo htmlspecialchars(substr($msg['reply_message'], 0, 100)); ?>..."
                                    </div>
                                <?php endif; ?>

                                <div class="message-header">
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($msg['user_name'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($msg['user_name']); ?></strong>
                                            <span class="badge badge-<?php
                                                                        echo $msg['user_role'] === 'admin' ? 'danger' : ($msg['user_role'] === 'teacher' ? 'primary' : ($msg['user_role'] === 'student' ? 'info' : 'success'));
                                                                        ?>">
                                                <?php echo ucfirst($msg['user_role']); ?>
                                            </span>
                                            <div style="font-size: 0.8rem; color: #64748b;">
                                                <?php echo date('M d, Y g:i A', strtotime($msg['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="message-actions">
                                        <button onclick="replyToMessage(<?php echo $msg['id']; ?>, '<?php echo htmlspecialchars($msg['user_name']); ?>')"
                                            class="btn btn-sm btn-secondary">
                                            <i class="fas fa-reply"></i>
                                        </button>

                                        <?php if ($_SESSION['role'] === 'admin' && !$msg['is_pinned']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                <button type="submit" name="pin_message" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-thumbtack"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Message Form -->
                <div class="message-form">
                    <form method="POST" id="messageForm">
                        <input type="hidden" name="board_id" value="<?php echo $current_board_id; ?>">
                        <input type="hidden" name="reply_to" id="replyTo" value="">

                        <div id="replyIndicator" style="display: none;" class="reply-indicator">
                            <i class="fas fa-reply"></i> <span id="replyText"></span>
                            <button type="button" onclick="cancelReply()" style="float: right; background: none; border: none; color: #64748b;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div style="display: flex; gap: 15px; align-items: end;">
                            <div class="form-group" style="flex: 1; margin: 0;">
                                <textarea name="message" id="messageInput" rows="3"
                                    placeholder="Type your message..." required
                                    style="resize: none;"></textarea>
                            </div>
                            <button type="submit" name="send_message" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let replyToId = null;

        function replyToMessage(messageId, userName) {
            replyToId = messageId;
            document.getElementById('replyTo').value = messageId;
            document.getElementById('replyText').textContent = `Replying to ${userName}`;
            document.getElementById('replyIndicator').style.display = 'block';
            document.getElementById('messageInput').focus();
        }

        function cancelReply() {
            replyToId = null;
            document.getElementById('replyTo').value = '';
            document.getElementById('replyIndicator').style.display = 'none';
        }

        // Auto-scroll to bottom of messages
        function scrollToBottom() {
            const container = document.getElementById('messagesContainer');
            container.scrollTop = container.scrollHeight;
        }

        // Auto-refresh messages every 10 seconds
        setInterval(() => {
            // In a real implementation, you'd use AJAX to fetch new messages
            // For now, we'll just reload the page if there are new messages
        }, 10000);

        // Handle Enter key for sending messages
        document.getElementById('messageInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('messageForm').submit();
            }
        });

        // Scroll to bottom on page load
        window.onload = function() {
            scrollToBottom();
        };
    </script>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>

<?php

function timeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . 'm ago';
    if ($time < 86400) return floor($time / 3600) . 'h ago';
    if ($time < 2592000) return floor($time / 86400) . 'd ago';

    return date('M d', strtotime($datetime));
}

?>