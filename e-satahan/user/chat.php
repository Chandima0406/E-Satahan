<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

if (strlen($_SESSION['ocasuid'])==0) {
    // Redirect to login page
    header('location:../quick_login.php');
    exit();
}

$user_id = $_SESSION['ocasuid'];
$selected_user = isset($_GET['user']) ? intval($_GET['user']) : 0;

// Get user information
$stmt = $dbh->prepare("SELECT FullName FROM tbluser WHERE ID = :uid");
$stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
$stmt->execute();
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get all users except current user
$stmt = $dbh->prepare("
    SELECT u.ID, u.FullName, u.MobileNumber, 
           (SELECT COUNT(*) FROM chat_messages 
            WHERE sender_id = u.ID AND receiver_id = :uid AND is_read = 0) as unread_count,
           (SELECT MAX(created_at) FROM chat_messages 
            WHERE (sender_id = :uid AND receiver_id = u.ID) 
               OR (sender_id = u.ID AND receiver_id = :uid)) as last_message_time
    FROM tbluser u
    WHERE u.ID != :uid
    ORDER BY last_message_time IS NULL, last_message_time DESC, FullName ASC
");
$stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If a user is selected, get their information
if($selected_user > 0) {
    $stmt = $dbh->prepare("SELECT FullName FROM tbluser WHERE ID = :selected_user");
    $stmt->bindParam(':selected_user', $selected_user, PDO::PARAM_INT);
    $stmt->execute();
    $selected_user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Mark messages from this user as read
    $mark_read = $dbh->prepare("
        UPDATE chat_messages 
        SET is_read = 1 
        WHERE sender_id = :selected_user AND receiver_id = :uid AND is_read = 0
    ");
    $mark_read->bindParam(':selected_user', $selected_user, PDO::PARAM_INT);
    $mark_read->bindParam(':uid', $user_id, PDO::PARAM_INT);
    $mark_read->execute();
}

// Count total unread messages
$stmt = $dbh->prepare("
    SELECT COUNT(*) FROM chat_messages 
    WHERE receiver_id = :uid AND is_read = 0
");
$stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
$stmt->execute();
$total_unread = $stmt->fetchColumn();

// Debug information
echo "<!-- DEBUG INFO -->";
echo "<!-- User ID: $user_id -->";
echo "<!-- Total Users Found: " . count($users) . " -->";
echo "<!-- Total Unread: $total_unread -->";
echo "<!-- Selected User: $selected_user -->";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>e-satahan || Chat System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4e73df;
            --primary-light: rgba(78, 115, 223, 0.1);
            --primary-dark: #224abe;
            --secondary: #858796;
            --success: #1cc88a;
            --danger: #e74a3b;
            --warning: #f6c23e;
            --info: #36b9cc;
            --light: #f8f9fc;
            --dark: #5a5c69;
            --text-main: #434a54;
            --text-light: #ffffff;
            --text-muted: #858796;
            --border-color: #e3e6f0;
            --body-bg: #f5f8fe;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-main);
        }
        
        .app-container {
            height: calc(100vh - 90px);
            min-height: 600px;
            margin: 20px 0;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border-radius: 0.5rem;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }
        
        .card:hover {
            box-shadow: 0 0.25rem 2.75rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .card-header {
            padding: 1rem 1.25rem;
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .users-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .users-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .users-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .search-container {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }
        
        .search-icon {
            position: absolute;
            top: 50%;
            left: 1.75rem;
            transform: translateY(-50%);
            color: var(--secondary);
        }
        
        .search-input {
            padding-left: 2.5rem;
            border-radius: 50px;
        }
        
        .users-list {
            flex: 1;
            overflow-y: auto;
        }
        
        .user-item {
            display: flex;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.2s ease;
            position: relative;
        }
        
        .user-item:hover {
            background-color: var(--primary-light);
        }
        
        .user-item.active {
            background-color: var(--primary-light);
            border-left: 4px solid var(--primary);
        }
        
        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--primary);
            color: var(--text-light);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .user-info {
            flex: 1;
            min-width: 0;
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-status {
            font-size: 0.8rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .unread-badge {
            background-color: var(--danger);
            color: white;
            border-radius: 10px;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .chat-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            display: flex;
            align-items: center;
        }
        
        .back-button {
            display: none;
            margin-right: 1rem;
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .chat-info {
            display: flex;
            align-items: center;
            flex: 1;
        }
        
        .chat-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--primary);
            color: var(--text-light);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .chat-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .chat-status {
            font-size: 0.8rem;
            color: var(--success);
        }
        
        .chat-actions {
            display: flex;
            gap: 15px;
        }
        
        .chat-action-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .chat-action-btn:hover {
            background-color: var(--primary);
            color: var(--text-light);
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #f9fafc;
        }
        
        .no-messages {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-muted);
            text-align: center;
        }
        
        .no-messages-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-light);
        }
        
        .message {
            display: flex;
            flex-direction: column;
            max-width: 75%;
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .message.sent {
            align-self: flex-end;
            align-items: flex-end;
        }
        
        .message.received {
            align-self: flex-start;
            align-items: flex-start;
        }
        
        .message-content {
            padding: 0.75rem 1rem;
            border-radius: 18px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            word-wrap: break-word;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .message.sent .message-content {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--text-light);
            border-bottom-right-radius: 5px;
        }
        
        .message.received .message-content {
            background-color: white;
            border-bottom-left-radius: 5px;
        }
        
        .message-time {
            font-size: 0.7rem;
            margin-top: 5px;
        }
        
        .message.sent .message-time {
            color: var(--text-muted);
        }
        
        .message.received .message-time {
            color: var(--text-muted);
        }
        
        .chat-input-container {
            padding: 1rem;
            background-color: white;
            border-top: 1px solid var(--border-color);
        }
        
        .chat-input-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chat-input {
            flex: 1;
            border-radius: 20px;
            padding: 0.75rem 1.25rem;
            border: 1px solid var(--border-color);
            transition: border-color 0.2s ease;
        }
        
        .chat-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .send-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .send-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .send-btn:disabled {
            background-color: var(--secondary);
            cursor: not-allowed;
            transform: none;
        }
        
        .welcome-screen {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: #f9fafc;
            color: var(--text-muted);
            text-align: center;
        }
        
        .welcome-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-light);
        }
        
        .welcome-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-main);
        }
        
        .welcome-text {
            max-width: 450px;
            margin-bottom: 2rem;
        }
        
        .date-divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }
        
        .date-divider:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: var(--border-color);
            z-index: 1;
        }
        
        .date-text {
            background-color: #f9fafc;
            padding: 0 15px;
            position: relative;
            display: inline-block;
            z-index: 2;
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        
        .nav-item .nav-link {
            color: var(--text-main);
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        
        .nav-item .nav-link:hover {
            color: var(--primary);
        }
        
        .nav-item .nav-link.active {
            color: var(--primary);
            font-weight: 600;
        }
        
        /* Enhanced Professional UI Elements */
        .chat-typing {
            padding: 8px 12px;
            font-size: 0.85rem;
            color: var(--text-muted);
            font-style: italic;
            position: absolute;
            bottom: 60px;
            left: 20px;
        }

        .typing-indicator {
            display: inline-flex;
            align-items: center;
        }

        .typing-dot {
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background-color: var(--text-muted);
            margin: 0 1px;
            animation: typingAnimation 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(1) {
            animation-delay: 0s;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typingAnimation {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-4px);
            }
        }

        /* Enhanced Message Styles */
        .message-content {
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .message.sent .message-content {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        /* Message Status Indicators */
        .message-status {
            font-size: 0.65rem;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 3px;
            color: var(--text-muted);
        }

        /* User Status Indicator */
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }

        .status-online {
            background-color: var(--success);
        }

        .status-offline {
            background-color: var(--secondary);
        }

        .status-away {
            background-color: var(--warning);
        }

        /* Enhanced Mobile Experience */
        @media (max-width: 992px) {
            .app-container {
                height: calc(100vh - 70px);
                margin: 10px 0;
            }
            
            .mobile-action-bar {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 10px;
                background-color: white;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
                z-index: 1001;
                display: flex;
                justify-content: space-between;
            }
        }

        /* Attachment & Emoji Support */
        .chat-input-form .input-wrapper {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            background-color: white;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 0 0 0 10px;
        }

        .chat-input-form .input-actions {
            display: flex;
            align-items: center;
        }

        .input-action-btn {
            background: none;
            border: none;
            color: var(--secondary);
            font-size: 1.2rem;
            padding: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .input-action-btn:hover {
            color: var(--primary);
        }

        .chat-input {
            border: none;
            box-shadow: none;
        }

        .chat-input:focus {
            box-shadow: none;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-book-reader"></i>
                e-satahan
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-notes.php">My Notes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="chat.php">
                            Messages
                            <?php if($total_unread > 0): ?>
                                <span class="badge bg-danger rounded-pill ms-1"><?php echo $total_unread; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-danger btn-sm" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container app-container">
        <div class="row h-100">
            <!-- Users Column -->
            <div class="col-lg-4 col-md-5 h-100 users-column <?php echo ($selected_user ? 'hidden' : ''); ?>" id="users-column">
                <div class="card users-card">
                    <div class="card-header users-header">
                        <h5 class="users-title">Conversations</h5>
                        <span class="badge bg-primary rounded-pill"><?php echo count($users); ?></span>
                    </div>
                    
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control search-input" id="search-users" placeholder="Search...">
                    </div>
                    
                    <div class="users-list">
                        <?php if(count($users) > 0): ?>
                            <?php foreach($users as $user): ?>
                                <?php 
                                $initials = "";
                                $name_parts = explode(" ", $user['FullName']);
                                $initials .= substr($name_parts[0], 0, 1);
                                if(count($name_parts) > 1) {
                                    $initials .= substr($name_parts[count($name_parts)-1], 0, 1);
                                }
                                $initials = strtoupper($initials);
                                ?>
                                <a href="?user=<?php echo $user['ID']; ?>" class="user-item <?php echo ($selected_user == $user['ID'] ? 'active' : ''); ?>" data-name="<?php echo strtolower($user['FullName']); ?>">
                                    <div class="user-avatar"><?php echo $initials; ?></div>
                                    <div class="user-info">
                                        <div class="user-name">
                                            <?php echo htmlspecialchars($user['FullName']); ?>
                                            <?php if($user['unread_count'] > 0): ?>
                                                <span class="unread-badge"><?php echo $user['unread_count']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="user-status">
                                            <?php if($user['last_message_time']): ?>
                                                <i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i>
                                                Last active: <?php echo date('M j, g:i A', strtotime($user['last_message_time'])); ?>
                                            <?php else: ?>
                                                <i class="fas fa-circle text-secondary me-1" style="font-size: 8px;"></i>
                                                No messages yet
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center my-5 text-muted">No conversations found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Chat Column -->
            <div class="col-lg-8 col-md-7 h-100 chat-column">
                <div class="card chat-card">
                    <?php if($selected_user && isset($selected_user_info)): ?>
                        <?php
                        $initials = "";
                        $name_parts = explode(" ", $selected_user_info['FullName']);
                        $initials .= substr($name_parts[0], 0, 1);
                        if(count($name_parts) > 1) {
                            $initials .= substr($name_parts[count($name_parts)-1], 0, 1);
                        }
                        $initials = strtoupper($initials);
                        ?>
                        <div class="card-header">
                            <div class="chat-header">
                                <button class="back-button" id="back-button">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                                <div class="chat-info">
                                    <div class="chat-avatar"><?php echo $initials; ?></div>
                                    <div>
                                        <h5 class="chat-name"><?php echo htmlspecialchars($selected_user_info['FullName']); ?></h5>
                                        <div class="chat-status">
                                            <i class="fas fa-circle me-1"></i>
                                            Online
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-actions">
                                    <button class="chat-action-btn" id="refresh-btn" title="Refresh messages">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="chat-messages" id="chat-messages">
                            <div class="text-center my-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="chat-input-container">
                            <div id="typing-indicator" class="chat-typing" style="display: none;">
                                <span class="typing-user">User</span> is typing
                                <span class="typing-indicator">
                                    <span class="typing-dot"></span>
                                    <span class="typing-dot"></span>
                                    <span class="typing-dot"></span>
                                </span>
                            </div>
                            <form id="chat-form" class="chat-input-form">
                                <input type="hidden" name="receiver_id" value="<?php echo $selected_user; ?>">
                                <div class="input-wrapper">
                                    <div class="input-actions">
                                        <button type="button" class="input-action-btn" title="Add emoji">
                                            <i class="far fa-smile"></i>
                                        </button>
                                    </div>
                                    <input type="text" name="message" class="form-control chat-input" id="message-input" placeholder="Type a message..." autocomplete="off">
                                    <div class="input-actions">
                                        <button type="button" class="input-action-btn" title="Attach file">
                                            <i class="fas fa-paperclip"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" class="send-btn" disabled>
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="welcome-screen">
                            <i class="fas fa-comments welcome-icon"></i>
                            <h4 class="welcome-title">Welcome to e-satahan Chat</h4>
                            <p class="welcome-text">
                                Connect with fellow students and teachers to discuss notes, ask questions, and collaborate on academic topics.
                            </p>
                            <p class="welcome-text">
                                Select a conversation from the left to start chatting.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Toggle between chat and users list on mobile
            $('#back-button').on('click', function() {
                $('#users-column').removeClass('hidden');
            });
            
            // Enable/disable send button based on input
            $('#message-input').on('input', function() {
                $('.send-btn').prop('disabled', $(this).val().trim() === '');
            });
            
            // Search users functionality
            $('#search-users').on('input', function() {
                const searchValue = $(this).val().toLowerCase();
                $('.user-item').each(function() {
                    const userName = $(this).data('name');
                    if (userName.includes(searchValue)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
            
            <?php if($selected_user): ?>
                let lastMessageDate = '';
                
                // Load messages function
                function loadMessages() {
                    $.ajax({
                        url: 'get-messages.php',
                        type: 'POST',
                        data: {
                            receiver_id: <?php echo $selected_user; ?>
                        },
                        success: function(response) {
                            $('#chat-messages').html(response);
                            scrollToBottom();
                        }
                    });
                }
                
                // Initial load messages
                loadMessages();
                
                // Refresh messages periodically
                const messageInterval = setInterval(loadMessages, 5000);
                
                // Manual refresh button
                $('#refresh-btn').on('click', function() {
                    loadMessages();
                    $(this).addClass('rotate');
                    setTimeout(() => {
                        $(this).removeClass('rotate');
                    }, 1000);
                });
                
                // Scroll to bottom of chat
                function scrollToBottom() {
                    const chatMessages = document.getElementById('chat-messages');
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
                
                // Handle form submission
                $('#chat-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    const messageInput = $('#message-input');
                    const message = messageInput.val().trim();
                    
                    if(!message) {
                        alert('Please enter a message');
                        return;
                    }
                    
                    // Debug: log form data
                    const formData = $(this).serialize();
                    console.log('Form data:', formData);
                    console.log('Message:', message);
                    console.log('Receiver ID:', $('input[name="receiver_id"]').val());
                    
                    // Disable the input and button while sending
                    messageInput.prop('disabled', true);
                    $('.send-btn').prop('disabled', true);
                    
                    $.ajax({
                        url: 'send-message.php',
                        type: 'POST',
                        data: {
                            receiver_id: $('input[name="receiver_id"]').val(),
                            message: message
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Send response:', response);
                            if(response.success) {
                                messageInput.val('');
                                loadMessages();
                            } else {
                                alert('Failed to send message: ' + (response.error || 'Unknown error'));
                                console.error('Send error:', response);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', xhr.responseText);
                            console.error('Status:', status);
                            console.error('Error:', error);
                            alert('Network error occurred. Check console for details.');
                        },
                        complete: function() {
                            messageInput.prop('disabled', false);
                            messageInput.focus();
                            $('.send-btn').prop('disabled', true);
                        }
                    });
                });
                
                // Enable/disable send button based on input
                $('#message-input').on('input', function() {
                    const message = $(this).val().trim();
                    $('.send-btn').prop('disabled', message.length === 0);
                });
                
                // Enable send button on page load if there's text
                $('#message-input').trigger('input');
                
                // Clean up on page unload
                $(window).on('beforeunload', function() {
                    clearInterval(messageInterval);
                });
            <?php endif; ?>
        });
    </script>
    
    <style>
        .rotate {
            animation: rotate 1s linear;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>