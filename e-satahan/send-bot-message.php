<?php
session_start();
include('includes/dbconnection.php');

header('Content-Type: application/json');

if (strlen($_SESSION['ocasuid'])==0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['ocasuid'];
$message = trim($_POST['message'] ?? '');
$sender = $_POST['sender'] ?? '';

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

// Validate message length
if (strlen($message) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Message too long. Please keep it under 1000 characters.']);
    exit();
}

// Validate sender parameter
if (!in_array($sender, ['user', 'bot', 'auto-response'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid sender type']);
    exit();
}

// Function to get bot response
function getBotResponse($dbh, $userMessage) {
    $message = strtolower(trim($userMessage));
    
    // Try to find a response from database first
    try {
        $stmt = $dbh->prepare("SELECT response FROM bot_responses WHERE LOWER(:message) LIKE CONCAT('%', LOWER(keyword), '%') ORDER BY RAND() LIMIT 1");
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->execute();
        $dbResponse = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dbResponse) {
            return $dbResponse['response'];
        }
    } catch (Exception $e) {
        // If database query fails, fall back to hardcoded responses
    }
    
    // Fallback to hardcoded responses if no database response found
    $responses = [
        'greetings' => [
            "Hello! How can I help you with your studies today?",
            "Hi there! I'm here to assist you with your notes and learning.",
            "Hey! What can I help you with regarding your academic work?"
        ],
        'notes' => [
            "I can help you organize your notes better. What subject are you working on?",
            "Notes are important for learning! Do you need help with a specific topic?",
            "I'd love to help you with your notes. What do you need assistance with?"
        ],
        'study' => [
            "Studying effectively is key to success! What study techniques work best for you?",
            "I can suggest some study methods. What subject are you focusing on?",
            "Great question about studying! How can I assist you with your learning goals?"
        ],
        'help' => [
            "I'm here to help! You can ask me about notes, study tips, or any academic questions.",
            "I can assist with organizing notes, study techniques, and academic guidance. What do you need?",
            "Feel free to ask me anything about your studies, notes, or learning strategies!"
        ],
        'exam' => [
            "Preparing for exams? Here are some tips: Create a study schedule, practice with past papers, and take regular breaks!",
            "Exam time can be stressful. Try active recall, spaced repetition, and mock tests for better preparation.",
            "For effective exam prep, focus on understanding concepts rather than memorizing. Want specific study strategies?"
        ],
        'thanks' => [
            "You're welcome! Happy to help with your learning journey.",
            "Glad I could assist! Feel free to ask if you need more help.",
            "My pleasure! I'm always here to support your academic success."
        ],
        'default' => [
            "That's an interesting question! Could you tell me more about what you're looking for?",
            "I'd be happy to help! Can you provide more details about your question?",
            "I'm here to assist you with your studies. Could you clarify what specific help you need?",
            "I understand you need help. Could you be more specific about your academic question?",
            "I'm designed to help with notes and studying. How can I assist you with your learning today?"
        ]
    ];
    
    $category = 'default';
    
    if (preg_match('/\b(hello|hi|hey|good morning|good afternoon|good evening)\b/', $message)) {
        $category = 'greetings';
    } elseif (preg_match('/\b(note|notes|notebook|writing|document)\b/', $message)) {
        $category = 'notes';
    } elseif (preg_match('/\b(study|learn|learning|education|academic|school|college|university)\b/', $message)) {
        $category = 'study';
    } elseif (preg_match('/\b(help|assist|support|guide|guidance)\b/', $message)) {
        $category = 'help';
    } elseif (preg_match('/\b(exam|test|quiz|assessment|examination|preparation)\b/', $message)) {
        $category = 'exam';
    } elseif (preg_match('/\b(thank|thanks|appreciate|grateful)\b/', $message)) {
        $category = 'thanks';
    }
    
    $categoryResponses = $responses[$category];
    return $categoryResponses[array_rand($categoryResponses)];
}

try {
    if ($sender === 'user') {
        // User message: sender_id = user_id, receiver_id = 0 (bot)
        $stmt = $dbh->prepare("
            INSERT INTO chat_messages (sender_id, receiver_id, message, created_at, is_read) 
            VALUES (:sender_id, 0, :message, NOW(), 1)
        ");
        $stmt->bindParam(':sender_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message']);
        }
        
    } else if ($sender === 'bot') {
        // Bot message: sender_id = 0 (bot), receiver_id = user_id
        $stmt = $dbh->prepare("
            INSERT INTO chat_messages (sender_id, receiver_id, message, created_at, is_read) 
            VALUES (0, :receiver_id, :message, NOW(), 0)
        ");
        $stmt->bindParam(':receiver_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Bot response sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send bot response']);
        }
        
    } else if ($sender === 'auto-response') {
        // Auto-generate bot response based on user message
        $userMessage = $message;
        $botResponse = getBotResponse($dbh, $userMessage);
        
        $stmt = $dbh->prepare("
            INSERT INTO chat_messages (sender_id, receiver_id, message, created_at, is_read) 
            VALUES (0, :receiver_id, :message, NOW(), 0)
        ");
        $stmt->bindParam(':receiver_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $botResponse, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Auto-response sent successfully', 'response' => $botResponse]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send auto-response']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid sender type']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
