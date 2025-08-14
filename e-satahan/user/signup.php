<?php 
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if(isset($_POST['submit']))
  {
    $fname=$_POST['fname'];
    $mobno=$_POST['mobno'];
    $email=$_POST['email'];
    
    $password=md5($_POST['password']);
    $ret="select Email,MobileNumber from tbluser where Email=:email || MobileNumber=:mobno";
    $query= $dbh -> prepare($ret);
    $query-> bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':mobno',$mobno,PDO::PARAM_INT);
    
    $query-> execute();
    $results = $query -> fetchAll(PDO::FETCH_OBJ);
if($query -> rowCount() == 0)
{
$sql="insert into tbluser(FullName,MobileNumber,Email,Password)Values(:fname,:mobno,:email,:password)";
$query = $dbh->prepare($sql);
$query->bindParam(':fname',$fname,PDO::PARAM_STR);
$query->bindParam(':email',$email,PDO::PARAM_STR);
$query->bindParam(':mobno',$mobno,PDO::PARAM_INT);

$query->bindParam(':password',$password,PDO::PARAM_STR);
$query->execute();
$lastInsertId = $dbh->lastInsertId();
if($lastInsertId)
{

echo "<script>alert('You have successfully registered with us');</script>";
echo "<script>window.location.href ='signin.php'</script>";
}
else
{

echo "<script>alert('Something went wrong.Please try again');</script>";
}
}
 else
{

echo "<script>alert('Email-id or Mobile Number is already exist. Please try again');</script>";
}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>e-satahan || Signup</title>
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
        .signup-container {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .signup-sidebar {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }
        .signup-form {
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
        .signup-links a {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.2s;
        }
        .signup-links a:hover {
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
                <div class="signup-container row g-0">
                    <!-- Left sidebar with info/branding -->
                    <div class="col-md-5 signup-sidebar">
                        <div>
                            <h2 class="app-title"><i class="fa fa-book-reader me-2"></i>e-satahan</h2>
                            <h4 class="mb-4">Notes Sharing System</h4>
                            <p class="mb-5">Join our community to share and access quality academic notes with fellow students.</p>
                            <div class="mt-4">
                                <p><i class="fas fa-check-circle me-2"></i> Instant access to study materials</p>
                                <p><i class="fas fa-check-circle me-2"></i> Connect with like-minded students</p>
                                <p><i class="fas fa-check-circle me-2"></i> Improve your academic performance</p>
                                <p><i class="fas fa-check-circle me-2"></i> Share your knowledge and notes</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right side with signup form -->
                    <div class="col-md-7 signup-form">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">Create Account</h2>
                            <p class="text-muted">Get started with e-satahan notes sharing system</p>
                        </div>
                        
                        <form method="post">
                            <div class="form-floating mb-3">
                                <input type="text" value="" name="fname" required="true" class="form-control" id="fullName" placeholder="Full Name">
                                <label for="fullName"><i class="far fa-user me-2"></i>Full Name</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="text" name="mobno" class="form-control" id="mobileNumber" required="true" maxlength="10" pattern="[0-9]+" placeholder="Mobile Number">
                                <label for="mobileNumber"><i class="fas fa-mobile-alt me-2"></i>Mobile Number</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="emailAddress" value="" name="email" required="true" placeholder="Email Address">
                                <label for="emailAddress"><i class="far fa-envelope me-2"></i>Email Address</label>
                            </div>
                            
                            <div class="form-floating mb-4">
                                <input type="password" value="" class="form-control" id="password" name="password" required="true" placeholder="Password">
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary py-3 w-100 mb-4" name="submit">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                            
                            <div class="signup-links d-flex justify-content-between mt-4">
                                <a href="../index.php"><i class="fas fa-home me-1"></i> Return to Home</a>
                                <a href="signin.php"><i class="fas fa-sign-in-alt me-1"></i> Already have an account? Sign in</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
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