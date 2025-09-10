<?php
// Basic error reporting to see issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('includes/dbconnection.php');

// Check session properly
if (strlen($_SESSION['ocasuid']) == 0) {
  header('location:logout.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>e-satahan || Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Professional Dashboard Styling */
        body {
            background-color: #f5f8fe;
            font-family: 'Heebo', sans-serif;
            color: #444;
        }
        
        .dashboard-welcome {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(78, 115, 223, 0.16);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-welcome::after {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 60%;
            height: 200%;
            opacity: 0.1;
            transform: rotate(30deg);
            background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 100%);
        }
        
        .dashboard-welcome h2 {
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 10px;
        }
        
        .stat-card {
            border-radius: 10px;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            background: white;
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            background: rgba(78, 115, 223, 0.1);
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #4e73df;
        }
        
        .stat-content {
            padding: 25px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #4e73df;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        
        .stat-label {
            color: #858796;
            font-size: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }
        
        .btn-light {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .btn-light:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-outline-primary {
            color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-outline-primary:hover {
            background-color: #4e73df;
            color: white;
        }
        
        .btn-home {
            background-color: #34c38f;
            color: white;
            border: none;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(52, 195, 143, 0.2);
        }
        
        .btn-home:hover {
            background-color: #2ca97a;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 195, 143, 0.4);
        }
        
        .nav-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .card-title {
            font-weight: 700;
            color: #434a54;
            margin-bottom: 20px;
        }
        
        .action-card {
            transition: all 0.3s;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .action-icon {
            font-size: 24px;
            margin-bottom: 15px;
            color: #4e73df;
        }
        
        .action-title {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 10px;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        
        /* Top Navigation Bar */
        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .logo-icon {
            font-size: 26px;
            color: #4e73df;
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 22px;
            color: #333;
            margin: 0;
        }
        
        /* Utility classes */
        .text-primary {
            color: #4e73df !important;
        }
        
        .bg-primary-light {
            background-color: rgba(78, 115, 223, 0.1);
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <!-- Top Navigation -->
        <div class="top-nav">
            <a href="../index.php" class="logo">
                <i class="fas fa-book-reader logo-icon"></i>
                <h1 class="logo-text">e-satahan</h1>
            </a>
            <div class="nav-actions">
                <a href="../index.php" class="btn btn-home">
                    <i class="fas fa-home me-2"></i>Return Home
                </a>
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
        
        <!-- Welcome Banner -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-welcome">
                    <?php
                    // Simple query to test database connection
                    $uid = $_SESSION['ocasuid'];
                    try {
                        $sql = "SELECT FullName FROM tbluser WHERE ID=:uid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':uid', $uid, PDO::PARAM_INT);
                        $query->execute();
                        $userName = $query->fetchColumn();
                    } catch (PDOException $e) {
                        $userName = "User";
                    }
                    ?>
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h2><i class="fas fa-smile me-3"></i>Welcome back, <?php echo htmlspecialchars($userName); ?></h2>
                            <p class="mb-0 opacity-80">Your personal dashboard for e-satahan Notes Sharing System</p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="manage-notes.php" class="btn btn-light"><i class="fas fa-plus me-2"></i> Upload New Notes</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row">
            <!-- Notes Stats -->
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="row g-0">
                        <div class="col-4">
                            <div class="stat-icon">
                                <i class="fas fa-book fa-3x"></i>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="stat-content">
                                <?php 
                                try {
                                    $sql1 = "SELECT COUNT(*) FROM tblnotes WHERE UserID=:uid";
                                    $query1 = $dbh->prepare($sql1);
                                    $query1->bindParam(':uid', $uid, PDO::PARAM_INT);
                                    $query1->execute();
                                    $totnotes = $query1->fetchColumn();
                                } catch (PDOException $e) {
                                    $totnotes = 0;
                                }
                                ?>
                                <div class="stat-value"><?php echo $totnotes; ?></div>
                                <div class="stat-label">Total Notes</div>
                                <a href="manage-notes.php" class="btn btn-sm btn-outline-primary mt-2">View All</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Views Stats -->
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="row g-0">
                        <div class="col-4">
                            <div class="stat-icon">
                                <i class="fas fa-eye fa-3x"></i>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="stat-content">
                                <?php 
                                try {
                                    $sql2 = "SELECT SUM(ViewCount) FROM tblnotes WHERE UserID=:uid";
                                    $query2 = $dbh->prepare($sql2);
                                    $query2->bindParam(':uid', $uid, PDO::PARAM_INT);
                                    $query2->execute();
                                    $views = $query2->fetchColumn() ?: 0;
                                } catch (PDOException $e) {
                                    $views = 0;
                                }
                                ?>
                                <div class="stat-value"><?php echo $views; ?></div>
                                <div class="stat-label">Total Views</div>
                                <a href="#" class="btn btn-sm btn-outline-primary mt-2">Analytics</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Downloads Stats -->
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="row g-0">
                        <div class="col-4">
                            <div class="stat-icon">
                                <i class="fas fa-download fa-3x"></i>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="stat-content">
                                <?php 
                                try {
                                    $sql3 = "SELECT SUM(DownloadCount) FROM tblnotes WHERE UserID=:uid";
                                    $query3 = $dbh->prepare($sql3);
                                    $query3->bindParam(':uid', $uid, PDO::PARAM_INT);
                                    $query3->execute();
                                    $downloads = $query3->fetchColumn() ?: 0;
                                } catch (PDOException $e) {
                                    $downloads = 0;
                                }
                                ?>
                                <div class="stat-value"><?php echo $downloads; ?></div>
                                <div class="stat-label">Downloads</div>
                                <a href="#" class="btn btn-sm btn-outline-primary mt-2">Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Grid -->
        <div class="row mt-3">
            <div class="col-12 mb-3">
                <h5 class="fw-bold">Quick Actions</h5>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card action-card text-center">
                    <div class="action-icon">
                        <i class="fas fa-file-upload"></i>
                    </div>
                    <div class="action-title">Upload Notes</div>
                    <a href="add-notes.php" class="stretched-link"></a>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card action-card text-center">
                    <div class="action-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="action-title">Manage Notes</div>
                    <a href="manage-notes.php" class="stretched-link"></a>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card action-card text-center">
                    <div class="action-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="action-title">Profile Settings</div>
                    <a href="profile.php" class="stretched-link"></a>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card action-card text-center">
                    <div class="action-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="action-title">Return Home</div>
                    <a href="../index.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Recent Activity</h5>
                            <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Views</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $sql4 = "SELECT Subject, CreationDate, ViewCount, Status FROM tblnotes 
                                                WHERE UserID=:uid ORDER BY CreationDate DESC LIMIT 5";
                                        $query4 = $dbh->prepare($sql4);
                                        $query4->bindParam(':uid', $uid, PDO::PARAM_INT);
                                        $query4->execute();
                                        $recentNotes = $query4->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        if (count($recentNotes) > 0) {
                                            foreach ($recentNotes as $note) {
                                                $status = $note['Status'] == 1 ? 
                                                    '<span class="badge bg-success">Active</span>' : 
                                                    '<span class="badge bg-warning">Pending</span>';
                                                echo "<tr>
                                                    <td>" . htmlspecialchars($note['Subject']) . "</td>
                                                    <td>" . date('M j, Y', strtotime($note['CreationDate'])) . "</td>
                                                    <td>" . $note['ViewCount'] . "</td>
                                                    <td>" . $status . "</td>
                                                </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4' class='text-center'>No recent activity found</td></tr>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='4' class='text-center'>Unable to load recent activity</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-4 mb-3 pt-3 text-center border-top">
            <p class="text-muted small mb-0">&copy; <?php echo date('Y'); ?> e-satahan Notes Sharing System. All rights reserved.</p>
        </div>
    </div>

    <!-- Bootstrap core JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Page load animation
        $(document).ready(function() {
            $('.stat-card').each(function(index) {
                $(this).delay(100 * index).animate({opacity: 1, top: 0}, 500);
            });
            
            // Click effect on action cards
            $('.action-card').click(function() {
                window.location = $(this).find('a').attr('href');
                return false;
            });
        });
    </script>
</body>
</html>