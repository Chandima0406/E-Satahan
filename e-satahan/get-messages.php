<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['ocasuid'])==0) {
    header('location:logout.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user = $_SESSION['ocasuid'];
    $other_user = $_POST['receiver_id'];
    
    // Get messages between current user and selected user
    $sql = "SELECT c.*, 
            s.FullName as sender_name, 
            r.FullName as receiver_name 
            FROM chat_messages c 
            JOIN tbluser s ON c.sender_id = s.ID 
            JOIN tbluser r ON c.receiver_id = r.ID 
            WHERE (c.sender_id = :current_user AND c.receiver_id = :other_user) 
            OR (c.sender_id = :other_user AND c.receiver_id = :current_user) 
            ORDER BY c.created_at ASC";
            
    $query = $dbh->prepare($sql);
    $query->bindParam(':current_user', $current_user, PDO::PARAM_STR);
    $query->bindParam(':other_user', $other_user, PDO::PARAM_STR);
    $query->execute();
    $messages = $query->fetchAll(PDO::FETCH_OBJ);
    
    // Mark messages as read
    $update_sql = "UPDATE chat_messages SET is_read = 1 
                   WHERE receiver_id = :current_user 
                   AND sender_id = :other_user 
                   AND is_read = 0";
    $update_query = $dbh->prepare($update_sql);
    $update_query->bindParam(':current_user', $current_user, PDO::PARAM_STR);
    $update_query->bindParam(':other_user', $other_user, PDO::PARAM_STR);
    $update_query->execute();
    
    // Output messages
    foreach($messages as $message) {
        $is_sent = $message->sender_id == $current_user;
        $time = date('h:i A', strtotime($message->created_at));
        $date = date('M d, Y', strtotime($message->created_at));
        
        echo '<div class="message ' . ($is_sent ? 'sent' : 'received') . '">
                <div class="message-content">' . htmlspecialchars($message->message) . '</div>
                <div class="message-time">' . $time . ' • ' . $date . '</div>
              </div>';
    }
}
?> 