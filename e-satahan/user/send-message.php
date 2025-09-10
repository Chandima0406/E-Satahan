<?php
session_start();
error_reporting(0); // Disable error display for clean JSON
ini_set('display_errors', 0);
include('includes/dbconnection.php');

// Return JSON response
header('Content-Type: application/json');

try {
    if (strlen($_SESSION['ocasuid'])==0) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sender_id = $_SESSION['ocasuid'];
        $receiver_id = isset($_POST['receiver_id']) ? $_POST['receiver_id'] : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        // Validate input
        if(empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
            exit();
        }
        
        if(empty($receiver_id) || !is_numeric($receiver_id)) {
            echo json_encode(['success' => false, 'error' => 'Invalid receiver ID']);
            exit();
        }
        
        $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message, created_at, is_read) 
                VALUES (:sender_id, :receiver_id, :message, NOW(), 0)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        $query->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
        $query->bindParam(':message', $message, PDO::PARAM_STR);
        
        if ($query->execute()) {
            $message_id = $dbh->lastInsertId();
            echo json_encode([
                'success' => true, 
                'message_id' => $message_id
            ]);
        } else {
            $errorInfo = $query->errorInfo();
            echo json_encode([
                'success' => false, 
                'error' => 'Database error: ' . $errorInfo[2]
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
}
?> 