<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['ocasuid'])==0) {
    header('location:user/logout.php');
    exit();
}

$user_id = $_SESSION['ocasuid'];

// Get user information
$stmt = $dbh->prepare("SELECT FullName FROM tbluser WHERE ID = :uid");
$stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
$stmt->execute();
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if this is the user's first interaction with the chatbot
$stmt = $dbh->prepare("
    SELECT COUNT(*) FROM chat_messages 
    WHERE (sender_id = :uid AND receiver_id = 0) 
       OR (sender_id = 0 AND receiver_id = :uid)
");
$stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
$stmt->execute();
$message_count = $stmt->fetchColumn();

// If no previous messages, send welcome message from bot
if ($message_count == 0) {
    $welcome_message = "Hey there, How can I assist you";
    $welcome_stmt = $dbh->prepare("
        INSERT INTO chat_messages (sender_id, receiver_id, message, created_at, is_read) 
        VALUES (0, :uid, :message, NOW(), 0)
    ");
    $welcome_stmt->bindParam(':uid', $user_id, PDO::PARAM_INT);
    $welcome_stmt->bindParam(':message', $welcome_message, PDO::PARAM_STR);
    $welcome_stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>e-satahan || Chat Bot</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS here -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/css/slicknav.css">
    <link rel="stylesheet" href="assets/css/flaticon.css">
    <link rel="stylesheet" href="assets/css/progressbar_barfiller.css">
    <link rel="stylesheet" href="assets/css/gijgo.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/animated-headline.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/themify-icons.css">
    <link rel="stylesheet" href="assets/css/slick.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
            --bot-color: #28a745;
            --bot-light: rgba(40, 167, 69, 0.1);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-main);
        }
        

        
        .app-container {
            margin-top: 30px;
            margin-bottom: 30px;
            min-height: calc(100vh - 200px);
        }
        
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border-radius: 0.5rem;
            transition: box-shadow 0.3s ease;
        }
        
        .chat-card {
            height: 700px;
            display: flex;
            flex-direction: column;
        }
        
        .card-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--bot-color), #20c997);
            color: white;
            border-bottom: none;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }
        
        .chat-header {
            display: flex;
            align-items: center;
        }
        
        .bot-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .chat-title {
            font-weight: 700;
            font-size: 1.3rem;
            margin: 0;
        }
        
        .chat-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .chat-messages {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            background-color: #f8f9fa;
        }
        
        .message {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
        }
        
        .message.user {
            justify-content: flex-end;
        }
        
        .message.bot {
            justify-content: flex-start;
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0 10px;
            flex-shrink: 0;
        }
        
        .message.user .message-avatar {
            background-color: var(--primary);
            color: white;
            order: 2;
        }
        
        .message.bot .message-avatar {
            background-color: var(--bot-color);
            color: white;
            order: 1;
        }
        
        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
        }
        
        .message.user .message-content {
            background-color: var(--primary);
            color: white;
            order: 1;
            border-bottom-right-radius: 5px;
        }
        
        .message.bot .message-content {
            background-color: white;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            order: 2;
            border-bottom-left-radius: 5px;
        }
        
        .message-text {
            margin: 0;
            line-height: 1.4;
        }
        
        .message-time {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
            margin-top: 5px;
        }
        
        .message.user .message-time {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .chat-input-container {
            padding: 1.5rem;
            background-color: white;
            border-top: 1px solid var(--border-color);
            border-radius: 0 0 0.5rem 0.5rem;
        }
        
        .typing-indicator {
            display: none;
            padding: 10px 20px;
            color: var(--text-muted);
            font-style: italic;
            border-top: 1px solid var(--border-color);
        }
        
        .chat-input-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .input-wrapper {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            background-color: var(--light);
            border: 2px solid var(--border-color);
            border-radius: 25px;
            padding: 5px 15px;
            transition: border-color 0.3s ease;
        }
        
        .input-wrapper:focus-within {
            border-color: var(--bot-color);
        }
        
        .chat-input {
            border: none;
            background: transparent;
            padding: 10px 15px;
            font-size: 1.1rem;
            flex: 1;
        }
        
        .chat-input:focus {
            outline: none;
            box-shadow: none;
        }
        
        .send-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--bot-color), #20c997);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        
        .send-btn:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        
        .send-btn:disabled {
            background: var(--secondary);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .welcome-message {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }
        
        .welcome-icon {
            font-size: 4rem;
            color: var(--bot-color);
            margin-bottom: 1rem;
        }
        
        .loading-dots {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .loading-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--bot-color);
            animation: loading 1.4s infinite both;
        }
        
        .loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .loading-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes loading {
            0%, 80%, 100% {
                opacity: 0.3;
                transform: scale(0.8);
            }
            40% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Scrollbar styling */
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        
        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .chat-messages::-webkit-scrollbar-thumb {
            background: var(--secondary);
            border-radius: 3px;
        }
        
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: var(--dark);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .app-container {
                margin-top: 20px;
                margin-bottom: 20px;
                min-height: calc(100vh - 160px);
            }
            
            .chat-card {
                height: 600px;
            }
            
            .message-content {
                max-width: 85%;
            }
            
            .chat-input-container {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Include Header -->
    <?php include('includes/header.php'); ?>

    <main>
        <div class="container app-container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="card chat-card">
                    <div class="card-header">
                        <div class="chat-header w-100">
                            <div class="bot-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div>
                                <h1 class="chat-title">e-satahan Assistant</h1>
                                <h2 class="chat-subtitle">Your friendly AI helper for notes and study assistance</h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chat-messages">
                        <div class="text-center my-4" id="loading-spinner">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading messages...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading conversation...</p>
                        </div>
                        <div id="error-message" class="text-center my-4 d-none">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Unable to load messages. <a href="#" onclick="loadMessages()">Try again</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="typing-indicator" id="typing-indicator">
                        <i class="fas fa-robot me-2"></i>
                        e-satahan Assistant is typing
                        <span class="loading-dots ms-2">
                            <span class="loading-dot"></span>
                            <span class="loading-dot"></span>
                            <span class="loading-dot"></span>
                        </span>
                    </div>
                    
                    <div class="chat-input-container">
                        <form id="chat-form" class="chat-input-form">
                            <div class="input-wrapper">
                                <input type="text" name="message" class="form-control chat-input" id="message-input" placeholder="Ask me anything about your notes or studies..." autocomplete="off">
                            </div>
                            <button type="submit" class="send-btn" disabled>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- JS here -->
    <script src="assets/js/vendor/modernizr-3.5.0.min.js"></script>
    <script src="assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.slicknav.min.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/slick.min.js"></script>
    <script src="assets/js/gijgo.min.js"></script>
    <script src="assets/js/jquery.barfiller.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/jquery.meanmenu.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <script src="assets/js/jquery.countdown.min.js"></script>
    <script src="assets/js/jquery.scrollUp.min.js"></script>
    <script src="assets/js/jquery.nice-select.min.js"></script>
    <script src="assets/js/jquery.sticky.js"></script>
    <script src="assets/js/jquery.magnific-popup.js"></script>
    <script src="assets/js/contact.js"></script>
    <script src="assets/js/jquery.form.js"></script>
    <script src="assets/js/jquery.validate.min.js"></script>
    <script src="assets/js/mail-script.js"></script>
    <script src="assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Additional jQuery for chat functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let isTyping = false;
            
            // Enable/disable send button based on input
            $('#message-input').on('input', function() {
                $('.send-btn').prop('disabled', $(this).val().trim() === '');
            });
            
            // Handle Enter key for sending messages
            $('#message-input').on('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) { // Enter key without Shift
                    e.preventDefault();
                    $('#chat-form').submit();
                }
            });
            
            // Handle Ctrl+Enter for new line (optional)
            $('#message-input').on('keydown', function(e) {
                if (e.which === 13 && e.ctrlKey) {
                    e.preventDefault();
                    const currentValue = $(this).val();
                    $(this).val(currentValue + '\n');
                }
            });
            
            // Load messages function
            function loadMessages() {
                $('#loading-spinner').show();
                $('#error-message').hide();
                
                $.ajax({
                    url: 'get-bot-messages.php',
                    type: 'GET',
                    timeout: 10000, // 10 second timeout
                    success: function(response) {
                        $('#loading-spinner').hide();
                        $('#chat-messages').html(response);
                        scrollToBottom();
                    },
                    error: function(xhr, status, error) {
                        $('#loading-spinner').hide();
                        $('#error-message').removeClass('d-none');
                        console.error('Failed to load messages:', status, error);
                    }
                });
            }
            
            // Initial load messages
            loadMessages();
            
            // Auto-refresh messages every 5 seconds when not actively typing
            let refreshInterval = setInterval(function() {
                if (!$('#message-input').is(':focus') && !isTyping) {
                    loadMessages();
                }
            }, 5000);
            
            // Clear interval when user starts typing
            $('#message-input').on('focus input', function() {
                clearInterval(refreshInterval);
            });
            
            // Restart interval when user stops typing
            $('#message-input').on('blur', function() {
                refreshInterval = setInterval(function() {
                    if (!$('#message-input').is(':focus') && !isTyping) {
                        loadMessages();
                    }
                }, 5000);
            });
            
            // Scroll to bottom of chat
            function scrollToBottom() {
                const chatMessages = document.getElementById('chat-messages');
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            // Show typing indicator
            function showTyping() {
                if (!isTyping) {
                    isTyping = true;
                    $('#typing-indicator').slideDown(200);
                    scrollToBottom();
                }
            }
            
            // Hide typing indicator
            function hideTyping() {
                if (isTyping) {
                    isTyping = false;
                    $('#typing-indicator').slideUp(200);
                }
            }
            
            // Simulate bot response
            function getBotResponse(userMessage) {
                // This function is now handled by the server
                // We'll make a server call to get the response
                return null; // Server will handle the response
            }
            
            // Handle form submission
            $('#chat-form').on('submit', function(e) {
                e.preventDefault();
                
                const messageInput = $('#message-input');
                const message = messageInput.val().trim();
                
                if(!message) return;
                
                // Disable the input and button while processing
                messageInput.prop('disabled', true);
                $('.send-btn').prop('disabled', true);
                
                // Clear input immediately
                messageInput.val('');
                
                // Send user message
                $.ajax({
                    url: 'send-bot-message.php',
                    type: 'POST',
                    data: {
                        message: message,
                        sender: 'user'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            loadMessages();
                            
                            // Show typing indicator and simulate bot response
                            setTimeout(function() {
                                showTyping();
                                
                                // Generate bot response after a delay
                                setTimeout(function() {
                                    // Send request for auto bot response
                                    $.ajax({
                                        url: 'send-bot-message.php',
                                        type: 'POST',
                                        data: {
                                            message: message,
                                            sender: 'auto-response'
                                        },
                                        dataType: 'json',
                                        success: function(botResp) {
                                            hideTyping();
                                            if(botResp.success) {
                                                loadMessages();
                                            }
                                        },
                                        error: function() {
                                            hideTyping();
                                            console.error('Failed to get bot response');
                                        }
                                    });
                                }, 1500 + Math.random() * 1000); // Random delay for more natural feel
                            }, 500);
                        } else {
                            alert('Failed to send message: ' + (response.message || 'Please try again.'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        alert('Connection error. Please check your internet connection and try again.');
                    },
                    complete: function() {
                        messageInput.prop('disabled', false);
                        messageInput.focus();
                        $('.send-btn').prop('disabled', true);
                    }
                });
            });
            
            // Focus on input when page loads
            $('#message-input').focus();
        });
    </script>
</body>
</html>
