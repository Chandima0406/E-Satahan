<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check if user is logged in and is admin
if (strlen($_SESSION["ocasuid"]) == 0) {
    header('location:logout.php');
} else if ($_SESSION["ocasurole"] != "admin") {
    header('location:dashboard.php');
}

// Date range filter
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$subject = isset($_GET['subject']) ? $_GET['subject'] : '';

// Analytics query with filters
try {
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // For better query handling
    
    // Total counts
    $stats_sql = "SELECT 
                   COUNT(DISTINCT n.ID) as total_notes,
                   COUNT(DISTINCT u.ID) as total_users,
                   SUM(COALESCE(n.File1Downloads, 0) + COALESCE(n.File2Downloads, 0) + 
                       COALESCE(n.File3Downloads, 0) + COALESCE(n.File4Downloads, 0)) as total_downloads,
                   SUM(COALESCE(n.ViewCount, 0)) as total_views
                 FROM tblnotes n
                 JOIN tbluser u ON n.UserID = u.ID";
                 
    $stats_query = $dbh->prepare($stats_sql);
    $stats_query->execute();
    $stats = $stats_query->fetch(PDO::FETCH_ASSOC);
    
    // Top downloaded notes
    $downloads_sql = "SELECT 
                      n.ID, n.NotesTitle, n.Subject, u.FullName,
                      n.AverageRating,
                      (COALESCE(n.File1Downloads, 0) + COALESCE(n.File2Downloads, 0) + 
                       COALESCE(n.File3Downloads, 0) + COALESCE(n.File4Downloads, 0)) as total_downloads,
                      n.CreationDate
                    FROM tblnotes n
                    JOIN tbluser u ON n.UserID = u.ID
                    WHERE (:subject = '' OR n.Subject = :subject)
                    ORDER BY total_downloads DESC
                    LIMIT 10";
    
    $downloads_query = $dbh->prepare($downloads_sql);
    $downloads_query->bindParam(':subject', $subject, PDO::PARAM_STR);
    $downloads_query->execute();
    $top_downloads = $downloads_query->fetchAll(PDO::FETCH_ASSOC);
    
    // Top viewed notes
    $views_sql = "SELECT 
                  n.ID, n.NotesTitle, n.Subject, u.FullName,
                  n.ViewCount, n.LastViewDate
                FROM tblnotes n
                JOIN tbluser u ON n.UserID = u.ID
                WHERE (:subject = '' OR n.Subject = :subject)
                ORDER BY n.ViewCount DESC
                LIMIT 10";
    
    $views_query = $dbh->prepare($views_sql);
    $views_query->bindParam(':subject', $subject, PDO::PARAM_STR);
    $views_query->execute();
    $top_views = $views_query->fetchAll(PDO::FETCH_ASSOC);
    
    // Top rated notes
    $ratings_sql = "SELECT 
                    n.ID, n.NotesTitle, n.Subject, u.FullName,
                    n.AverageRating, COUNT(r.ID) as rating_count
                  FROM tblnotes n
                  JOIN tbluser u ON n.UserID = u.ID
                  LEFT JOIN tblratings r ON n.ID = r.NoteID
                  WHERE (:subject = '' OR n.Subject = :subject)
                  GROUP BY n.ID
                  HAVING n.AverageRating > 0
                  ORDER BY n.AverageRating DESC, rating_count DESC
                  LIMIT 10";
    
    $ratings_query = $dbh->prepare($ratings_sql);
    $ratings_query->bindParam(':subject', $subject, PDO::PARAM_STR);
    $ratings_query->execute();
    $top_ratings = $ratings_query->fetchAll(PDO::FETCH_ASSOC);
    
    // Activity over time
    $activity_sql = "SELECT 
                     DATE_FORMAT(d.DownloadDate, '%Y-%m-%d') as date,
                     COUNT(*) as download_count
                   FROM tbldownloads d
                   JOIN tblnotes n ON d.NoteID = n.ID
                   WHERE d.DownloadDate BETWEEN :start_date AND :end_date
                   AND (:subject = '' OR n.Subject = :subject)
                   GROUP BY DATE_FORMAT(d.DownloadDate, '%Y-%m-%d')
                   ORDER BY date";
    
    $activity_query = $dbh->prepare($activity_sql);
    $activity_query->bindParam(':start_date', $startDate, PDO::PARAM_STR);
    $activity_query->bindParam(':end_date', $endDate, PDO::PARAM_STR);
    $activity_query->bindParam(':subject', $subject, PDO::PARAM_STR);
    $activity_query->execute();
    $activity_data = $activity_query->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all subjects for filter
    $subject_query = $dbh->query("SELECT DISTINCT Subject FROM tblnotes ORDER BY Subject");
    $subjects = $subject_query->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Analytics Dashboard - E-Satahan Notes</title>
    <link href="css/style.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    
    <!-- Chart.js for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <style>
        .card {
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .card-header {
            background-color: #f7fafc;
            border-bottom: 1px solid #edf2f7;
            padding: 15px 20px;
        }
        
        .stats-card {
            text-align: center;
            padding: 20px;
        }
        
        .stats-card .number {
            font-size: 24px;
            font-weight: 700;
            color: #3182ce;
            margin-bottom: 5px;
        }
        
        .stats-card .label {
            color: #718096;
            font-size: 14px;
        }
        
        .stats-card i {
            font-size: 28px;
            margin-bottom: 15px;
            color: #4299e1;
        }
        
        .filter-form {
            background-color: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .rating-stars {
            color: #f6ad55;
            font-size: 14px;
        }
        
        .analytics-table th {
            background-color: #f8fafc;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        
        <?php include_once('includes/header.php');?>
        <?php include_once('includes/sidebar.php');?>
        
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h1 class="mb-0 fw-bold">Analytics Dashboard</h1> 
                    </div>
                    <div class="col-6">
                        <div class="text-end">
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <!-- Filter Form -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body filter-form">
                                <form method="GET" class="form-inline">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label for="start_date">Start Date</label>
                                            <input type="date" id="start_date" name="start_date" class="form-control w-100" 
                                                   value="<?php echo $startDate; ?>">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label for="end_date">End Date</label>
                                            <input type="date" id="end_date" name="end_date" class="form-control w-100" 
                                                   value="<?php echo $endDate; ?>">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label for="subject">Subject</label>
                                            <select id="subject" name="subject" class="form-control w-100">
                                                <option value="">All Subjects</option>
                                                <?php foreach($subjects as $subj): ?>
                                                <option value="<?php echo htmlspecialchars($subj); ?>" 
                                                        <?php echo ($subject === $subj) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($subj); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                                            <a href="analytics.php" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                  <!-- Export functionality -->
                <?php include_once('includes/export-analytics-panel.php'); ?>
                
                <!-- Stats Overview -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body stats-card">
                                <i class="fas fa-file-alt"></i>
                                <div class="number"><?php echo number_format($stats['total_notes']); ?></div>
                                <div class="label">Total Notes</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body stats-card">
                                <i class="fas fa-users"></i>
                                <div class="number"><?php echo number_format($stats['total_users']); ?></div>
                                <div class="label">Total Users</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body stats-card">
                                <i class="fas fa-download"></i>
                                <div class="number"><?php echo number_format($stats['total_downloads']); ?></div>
                                <div class="label">Total Downloads</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body stats-card">
                                <i class="fas fa-eye"></i>
                                <div class="number"><?php echo number_format($stats['total_views']); ?></div>
                                <div class="label">Total Views</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Chart -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Activity Over Time</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="activityChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Notes Tables -->
                <div class="row">
                    <!-- Top Downloads -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Top Downloaded Notes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered analytics-table" id="downloadsTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Note Title</th>
                                                <th>Subject</th>
                                                <th>Author</th>
                                                <th>Downloads</th>
                                                <th>Rating</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $count = 1;
                                            foreach($top_downloads as $note): 
                                            ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td>
                                                    <a href="../view-note.php?id=<?php echo $note['ID']; ?>" target="_blank">
                                                        <?php echo htmlentities($note['NotesTitle']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlentities($note['Subject']); ?></td>
                                                <td><?php echo htmlentities($note['FullName']); ?></td>
                                                <td><strong><?php echo number_format($note['total_downloads']); ?></strong></td>
                                                <td>
                                                    <div class="rating-stars">
                                                        <?php 
                                                        $rating = round($note['AverageRating']);
                                                        for($i = 1; $i <= 5; $i++) {
                                                            if($i <= $rating) {
                                                                echo '<i class="fas fa-star"></i>';
                                                            } else {
                                                                echo '<i class="far fa-star"></i>';
                                                            }
                                                        }
                                                        ?>
                                                        <span class="ms-1">(<?php echo number_format($note['AverageRating'], 1); ?>)</span>
                                                    </div>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($note['CreationDate'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Top Viewed -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Most Viewed Notes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered analytics-table" id="viewsTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Note Title</th>
                                                <th>Subject</th>
                                                <th>Views</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $count = 1;
                                            foreach($top_views as $note): 
                                            ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td>
                                                    <a href="../view-note.php?id=<?php echo $note['ID']; ?>" target="_blank">
                                                        <?php echo htmlentities($note['NotesTitle']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlentities($note['Subject']); ?></td>
                                                <td><strong><?php echo number_format($note['ViewCount']); ?></strong></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Rated -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Highest Rated Notes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered analytics-table" id="ratingsTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Note Title</th>
                                                <th>Subject</th>
                                                <th>Rating</th>
                                                <th>Reviews</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $count = 1;
                                            foreach($top_ratings as $note): 
                                            ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td>
                                                    <a href="../view-note.php?id=<?php echo $note['ID']; ?>" target="_blank">
                                                        <?php echo htmlentities($note['NotesTitle']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlentities($note['Subject']); ?></td>
                                                <td>
                                                    <div class="rating-stars">
                                                        <?php 
                                                        $rating = round($note['AverageRating']);
                                                        for($i = 1; $i <= 5; $i++) {
                                                            if($i <= $rating) {
                                                                echo '<i class="fas fa-star"></i>';
                                                            } else {
                                                                echo '<i class="far fa-star"></i>';
                                                            }
                                                        }
                                                        ?>
                                                        <span class="ms-1">(<?php echo number_format($note['AverageRating'], 1); ?>)</span>
                                                    </div>
                                                </td>
                                                <td><?php echo $note['rating_count']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include_once('includes/footer.php');?>
        </div>
    </div>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/app-style-switcher.js"></script>
    <script src="js/waves.js"></script>
    <script src="js/sidebarmenu.js"></script>
    <script src="js/custom.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        // Activity Chart
        const activityChart = document.getElementById('activityChart').getContext('2d');
        
        // Generate labels and data arrays from PHP data
        const activityData = <?php echo json_encode($activity_data); ?>;
        const labels = activityData.map(item => item.date);
        const data = activityData.map(item => item.download_count);
        
        new Chart(activityChart, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Downloads',
                    data: data,
                    backgroundColor: 'rgba(66, 153, 225, 0.2)',
                    borderColor: '#4299e1',
                    borderWidth: 2,
                    pointBackgroundColor: '#3182ce',
                    pointRadius: 4,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#4a5568',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 15,
                        displayColors: false,
                        callbacks: {
                            title: function(tooltipItems) {
                                return 'Date: ' + tooltipItems[0].label;
                            },
                            label: function(context) {
                                return 'Downloads: ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });
        
        // Initialize DataTables
        $(document).ready(function() {
            $('#downloadsTable').DataTable({
                paging: false,
                searching: false,
                info: false
            });
            
            $('#viewsTable').DataTable({
                paging: false,
                searching: false,
                info: false
            });
            
            $('#ratingsTable').DataTable({
                paging: false,
                searching: false,
                info: false
            });
        });
    </script>
</body>
</html>
