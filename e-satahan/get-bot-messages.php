<?php
session_start();
include('includes/dbconnection.php');

if (strlen($_SESSION['ocasuid'])==0) {
    exit('Unauthorized');
}

$user_id = $_SESSION['ocasuid'];

try {
    // Get user information for avatar
    $stmt = $dbh->prepare("SELECT FullName FROM tbluser WHERE ID = :uid");
    $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $user_initials = "";
    if ($current_user) {
        $name_parts = explode(" ", $current_user['FullName']);
        $user_initials .= substr($name_parts[0], 0, 1);
        if(count($name_parts) > 1) {
            $user_initials .= substr($name_parts[count($name_parts)-1], 0, 1);
        }
        $user_initials = strtoupper($user_initials);
    }

    // Get all messages between user and bot (sender_id = 0 is bot)
    $stmt = $dbh->prepare("
        SELECT sender_id, message, created_at 
        FROM chat_messages 
        WHERE (sender_id = :uid AND receiver_id = 0) 
           OR (sender_id = 0 AND receiver_id = :uid)
        ORDER BY created_at ASC
    ");
    $stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($messages) > 0) {
        foreach ($messages as $message) {
            $is_user = ($message['sender_id'] == $user_id);
            $message_class = $is_user ? 'user' : 'bot';
            $time = date('g:i A', strtotime($message['created_at']));
            
            echo '<div class="message ' . $message_class . '">';
            
            if ($is_user) {
                echo '<div class="message-content">';
                echo '<p class="message-text">' . htmlspecialchars($message['message']) . '</p>';
                echo '<div class="message-time">' . $time . '</div>';
                echo '</div>';
                echo '<div class="message-avatar">' . $user_initials . '</div>';
            } else {
                echo '<div class="message-avatar"><i class="fas fa-robot"></i></div>';
                echo '<div class="message-content">';
                echo '<p class="message-text">' . htmlspecialchars($message['message']) . '</p>';
                echo '<div class="message-time">' . $time . '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        }
    } else {
        echo '<div class="welcome-message">';
        echo '<i class="fas fa-robot welcome-icon"></i>';
        echo '<h5>Welcome to e-satahan Assistant!</h5>';
        echo '<p>I\'m here to help you with your studies and notes. Feel free to ask me anything!</p>';
        echo '</div>';
    }
    
} catch (Exception $e) {
    echo '<div class="text-center text-danger">Error loading messages</div>';
}
?>
