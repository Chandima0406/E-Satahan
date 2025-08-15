<?php
session_start();
//error_reporting(0);
include('includes/dbconnection.php');

if(isset($_POST['login'])) 
  {
    $emailormobnum=$_POST['emailormobnum'];
    $password=md5($_POST['password']);
    $sql ="SELECT Email,MobileNumber,Password,ID FROM tbluser WHERE (Email=:emailormobnum || MobileNumber=:emailormobnum) and Password=:password";
    $query=$dbh->prepare($sql);
    $query->bindParam(':emailormobnum',$emailormobnum,PDO::PARAM_STR);
    $query-> bindParam(':password', $password, PDO::PARAM_STR);
    $query-> execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);
    if($query->rowCount() > 0)
{
foreach ($results as $result) {
$_SESSION['ocasuid']=$result->ID;

}
$_SESSION['login']=$_POST['emailormobnum'];

echo "<script type='text/javascript'> document.location ='dashboard.php'; </script>";
} else{
echo "<script>alert('Invalid Details');</script>";
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>e-satahan || Signin</title>
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Heebo', sans-serif;
        }
        .login-container {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .login-sidebar {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }
        .login-form {
            background: white;
            padding: 2.5rem;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.25);
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #0a58ca;
            border-color: #0a58ca;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(13, 110, 253, 0.3);
        }
        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: #0d6efd;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .spinner-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .app-title {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 2rem;
        }
        .login-links a {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.2s;
        }
        .login-links a:hover {
            color: #0d6efd;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="spinner-wrapper">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="login-container row g-0">
                    <!-- Left sidebar with info/branding -->
                    <div class="col-md-5 login-sidebar">
                        <div>
                            <h2 class="app-title"><i class="fa fa-book-reader me-2"></i>e-satahan</h2>
                            <h4 class="mb-4">Notes Sharing System</h4>
                            <p class="mb-5">Access your account to share and discover academic notes with fellow students.</p>
                            <div class="mt-4">
                                <p><i class="fas fa-share-alt me-2"></i> Share your knowledge</p>
                                <p><i class="fas fa-graduation-cap me-2"></i> Learn from peers</p>
                                <p><i class="fas fa-comments me-2"></i> Collaborate effectively</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right side with login form -->
                    <div class="col-md-7 login-form">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">Welcome Back</h2>
                            <p class="text-muted">Sign in to continue to e-satahan</p>
                        </div>
                        
                        <form method="post">
                            <div class="form-floating mb-4">
                                <input type="text" class="form-control" id="emailormobnum" placeholder="Email or Mobile Number" 
                                       required="true" name="emailormobnum">
                                <label for="emailormobnum"><i class="far fa-user me-2"></i>Email or Mobile Number</label>
                            </div>
                            
                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="password" placeholder="Password" 
                                       name="password" required="true">
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>
                            
                            <div class="d-flex justify-content-end mb-4">
                                <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
                            </div>
                            
                            <button type="submit" class="btn btn-primary py-3 w-100 mb-4" name="login">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </form>
                        
                        <div class="login-links d-flex justify-content-between mt-4">
                            <a href="../index.php"><i class="fas fa-home me-1"></i> Return to Home</a>
                            <a href="signup.php"><i class="fas fa-user-plus me-1"></i> Create an account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    
    <script>
        // Hide spinner after page load
        $(window).on('load', function() {
            $('#spinner').fadeOut('slow');
        });
    </script>
</body>
</html>