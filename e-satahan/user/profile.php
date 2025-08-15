<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['ocasuid']==0)) {
  header('location:logout.php');
  } else{
     if(isset($_POST['submit']))
  {
    $uid=$_SESSION['ocasuid'];
    $fname=$_POST['name'];
    $mobno=$_POST['mobilenumber'];
    $email=$_POST['email'];
    $sql="update tbluser set FullName=:name,MobileNumber=:mobilenumber,Email=:email where ID=:uid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':name',$fname,PDO::PARAM_STR);
    $query->bindParam(':email',$email,PDO::PARAM_STR);
    $query->bindParam(':mobilenumber',$mobno,PDO::PARAM_STR);
    $query->bindParam(':uid',$uid,PDO::PARAM_STR);
    $query->execute();
    echo '<script>alert("Profile has been updated successfully")</script>';
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-satahan || User Profile</title>
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-light: rgba(99, 102, 241, 0.1);
            --secondary: #f43f5e;
            --secondary-hover: #e11d48;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --dark: #111827;
            --light: #ffffff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: var(--gray-700);
            line-height: 1.6;
            padding: 2rem 0;
        }
        
        .container {
            max-width: 1140px;
        }
        
        /* Top Header */
        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0;
        }
        
        .brand-icon {
            font-size: 1.75rem;
            color: var(--primary);
        }
        
        .header-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .nav-link {
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s;
        }
        
        .nav-link:hover {
            color: var(--primary);
        }
        
        .nav-link.active {
            color: var(--primary);
        }
        
        .btn-logout {
            color: #fff;
            background-color: var(--danger);
            border-color: var(--danger);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .btn-logout:hover {
            background-color: #dc2626;
            border-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
            color: #fff;
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }
        
        .user-menu:hover {
            background-color: var(--gray-100);
        }
        
        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .user-info {
            display: none;
        }
        
        @media (min-width: 768px) {
            .user-info {
                display: block;
            }
        }
        
        .user-name {
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
            font-size: 0.9375rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin: 0;
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title-wrapper {
            flex: 1;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: var(--gray-500);
            margin-bottom: 0;
        }
        
        .page-actions {
            display: flex;
            gap: 1rem;
        }
        
        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-bottom: 1.5rem;
        }
        
        .breadcrumb-item {
            display: inline-flex;
            align-items: center;
        }
        
        .breadcrumb-item a {
            color: var(--gray-500);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb-item a:hover {
            color: var(--primary);
        }
        
        .breadcrumb-divider {
            color: var(--gray-400);
            margin: 0 0.5rem;
        }
        
        /* Profile Content */
        .profile-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        @media (max-width: 992px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }
        
        /* Profile Card */
        .profile-card {
            background-color: var(--light);
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .profile-card-header {
            background: linear-gradient(120deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .profile-card-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2ZmZmZmZiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIGNsYXNzPSJmZWF0aGVyIGZlYXRoZXItY2lyY2xlIj48Y2lyY2xlIGN4PSIxMiIgY3k9IjEyIiByPSIxMCIvPjwvc3ZnPg==');
            background-position: -35px -45px;
            background-repeat: no-repeat;
            opacity: 0.1;
        }
        
        .profile-avatar {
            width: 6.5rem;
            height: 6.5rem;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            border: 4px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        
        .profile-avatar-text {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .profile-role {
            opacity: 0.85;
            margin-bottom: 0;
        }
        
        .profile-info {
            padding: 1.5rem;
        }
        
        .profile-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
        }
        
        .profile-info-item:last-child {
            margin-bottom: 0;
        }
        
        .profile-info-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            background-color: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .profile-info-content {
            flex-grow: 1;
        }
        
        .profile-info-label {
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .profile-info-value {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0;
        }
        
        /* Form Card */
        .form-card {
            background-color: var(--light);
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .form-card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .form-card-title-wrapper {
            flex: 1;
        }
        
        .form-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .form-card-subtitle {
            color: var(--gray-500);
            font-size: 0.9375rem;
            margin-bottom: 0;
        }
        
        .form-card-body {
            padding: 2rem;
        }
        
        .form-card-footer {
            padding: 1.5rem 2rem;
            background-color: var(--gray-50);
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-300);
            transition: all 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        
        .form-text {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: 0.5rem;
        }
        
        .form-control:disabled,
        .form-control[readonly] {
            background-color: var(--gray-100);
        }
        
        .input-group > .btn {
            z-index: 0;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.2);
        }
        
        .btn-outline-secondary {
            border-color: var(--gray-300);
            color: var(--gray-700);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--gray-100);
            color: var(--gray-800);
            border-color: var(--gray-400);
        }
        
        /* Footer */
        .footer {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
            text-align: center;
            color: var(--gray-500);
            font-size: 0.875rem;
        }
        
        /* Modal */
        .modal-content {
            border-radius: 1rem;
            border: none;
        }
        
        .modal-header {
            border-bottom: 1px solid var(--gray-200);
            padding: 1.25rem 1.5rem;
        }
        
        .modal-title {
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid var(--gray-200);
            padding: 1.25rem 1.5rem;
        }
        
        @media (max-width: 768px) {
            .app-header {
                flex-wrap: wrap;
            }
            
            .header-nav {
                margin-top: 1rem;
                width: 100%;
                justify-content: space-between;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .page-actions {
                width: 100%;
            }
            
            .form-card-header {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- App Header -->
        <div class="app-header">
            <a href="dashboard.php" class="brand">
                <i class="fas fa-book-reader brand-icon"></i>
                <h1 class="brand-name">e-satahan</h1>
            </a>
            
            <div class="header-nav">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage-notes.php" class="nav-link">
                    <i class="fas fa-book"></i>
                    <span>My Notes</span>
                </a>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
                <div class="user-dropdown">
                    <?php
                    $uid=$_SESSION['ocasuid'];
                    $sql="SELECT * from tbluser where ID=:uid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':uid',$uid,PDO::PARAM_STR);
                    $query->execute();
                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                    
                    if($query->rowCount() > 0)
                    {
                        foreach($results as $row)
                        {
                            // Get initials for avatar
                            $initials = strtoupper(substr($row->FullName, 0, 1));
                            if (strpos($row->FullName, ' ') !== false) {
                                $nameParts = explode(' ', $row->FullName);
                                $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1));
                            }
                    ?>
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?php echo $initials; ?>
                        </div>
                        <div class="user-info">
                            <h6 class="user-name"><?php echo htmlentities($row->FullName); ?></h6>
                            <p class="user-role">Student</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-wrapper">
                <h1 class="page-title">My Profile</h1>
                <p class="page-subtitle">Manage your personal information</p>
            </div>
            <div class="page-actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fas fa-user-edit"></i> Quick Edit
                </button>
            </div>
        </div>
        
        <div class="breadcrumb">
            <div class="breadcrumb-item">
                <a href="dashboard.php">Dashboard</a>
            </div>
            <div class="breadcrumb-divider">/</div>
            <div class="breadcrumb-item">Profile</div>
        </div>
        
        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-avatar">
                        <span class="profile-avatar-text"><?php echo $initials; ?></span>
                    </div>
                    <h2 class="profile-name"><?php echo htmlentities($row->FullName); ?></h2>
                    <p class="profile-role">Student</p>
                </div>
                <div class="profile-info">
                    <div class="profile-info-item">
                        <div class="profile-info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="profile-info-content">
                            <div class="profile-info-label">Email Address</div>
                            <p class="profile-info-value"><?php echo htmlentities($row->Email); ?></p>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="profile-info-content">
                            <div class="profile-info-label">Phone Number</div>
                            <p class="profile-info-value"><?php echo htmlentities($row->MobileNumber); ?></p>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="profile-info-content">
                            <div class="profile-info-label">Registration Date</div>
                            <p class="profile-info-value">
                                <?php 
                                $date = new DateTime($row->RegDate);
                                echo $date->format('F j, Y'); 
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="profile-info-content">
                            <div class="profile-info-label">Last Updated</div>
                            <p class="profile-info-value">
                                <?php 
                                $updatedDate = new DateTime($row->LastUpdationDate ?? $row->RegDate);
                                echo $updatedDate->format('F j, Y'); 
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Profile Form -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-title-wrapper">
                        <h3 class="form-card-title">
                            <i class="fas fa-user-edit text-primary"></i> Profile Information
                        </h3>
                        <p class="form-card-subtitle">Update your personal details</p>
                    </div>
                </div>
                
                <div class="form-card-body">
                    <form method="post" id="profileForm">
                        <div class="mb-4">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlentities($row->FullName); ?>" required>
                            <div class="form-text">This name will be displayed on your profile and shared notes.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlentities($row->Email); ?>" required>
                            <div class="form-text">We'll never share your email with anyone else.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="mobilenumber" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="mobilenumber" name="mobilenumber" value="<?php echo htmlentities($row->MobileNumber); ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#phoneEditModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                            <div class="form-text">Phone number updates require verification.</div>
                        </div>
                    </div>
                    
                    <div class="form-card-footer">
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php } } ?>
        
        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> e-satahan Notes Sharing System. All rights reserved.</p>
        </div>
    </div>
    
    <!-- Phone Edit Modal -->
    <div class="modal fade" id="phoneEditModal" tabindex="-1" aria-labelledby="phoneEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="phoneEditModalLabel">Change Phone Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>To change your phone number, please contact our support team:</p>
                    <div class="d-flex align-items-center mt-3 mb-2">
                        <div class="me-3 text-primary">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Email Support</h6>
                            <p class="mb-0 text-muted">support@esatahan.edu.ph</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="me-3 text-primary">
                            <i class="fas fa-phone fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Call Center</h6>
                            <p class="mb-0 text-muted">+63 912 345 6789</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Quick Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="quickEditForm">
                        <div class="mb-3">
                            <label for="quickName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="quickName" name="name" value="<?php echo htmlentities($row->FullName); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quickEmail" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="quickEmail" name="email" value="<?php echo htmlentities($row->Email); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quickPhone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="quickPhone" name="mobilenumber" value="<?php echo htmlentities($row->MobileNumber); ?>" readonly>
                            <div class="form-text">Contact support to change your phone number.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveQuickEdit">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const form = document.querySelector('#profileForm');
            form.addEventListener('submit', function(event) {
                validateForm(event, 'name', 'email');
            });
            
            // Quick edit form handling
            const quickEditForm = document.querySelector('#quickEditForm');
            const saveQuickEditBtn = document.querySelector('#saveQuickEdit');
            
            saveQuickEditBtn.addEventListener('click', function() {
                const nameInput = document.querySelector('#quickName');
                const emailInput = document.querySelector('#quickEmail');
                
                // Copy values to the main form
                document.querySelector('#name').value = nameInput.value;
                document.querySelector('#email').value = emailInput.value;
                
                // Submit the main form if validation passes
                if (validateForm(null, 'quickName', 'quickEmail', false)) {
                    document.querySelector('#profileForm').submit();
                }
            });
            
            // Shared validation function
            function validateForm(event, nameId, emailId, preventDefault = true) {
                const nameInput = document.getElementById(nameId);
                const emailInput = document.getElementById(emailId);
                
                if (nameInput.value.trim() === '') {
                    alert('Please enter your full name');
                    nameInput.focus();
                    if (preventDefault && event) event.preventDefault();
                    return false;
                }
                
                if (emailInput.value.trim() === '') {
                    alert('Please enter your email address');
                    emailInput.focus();
                    if (preventDefault && event) event.preventDefault();
                    return false;
                }
                
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value.trim())) {
                    alert('Please enter a valid email address');
                    emailInput.focus();
                    if (preventDefault && event) event.preventDefault();
                    return false;
                }
                
                return true;
            }
        });
    </script>
</body>
</html>
<?php }  ?>