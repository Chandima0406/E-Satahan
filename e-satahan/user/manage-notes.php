<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['ocasuid']==0)) {
  header('location:logout.php');
  exit();
} 

// Handle note deletion
if(isset($_GET['delid'])) {
    $rid = intval($_GET['delid']);
    $sql = "DELETE FROM tblnotes WHERE ID=:rid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':rid', $rid, PDO::PARAM_STR);
    $query->execute();
    echo "<script>alert('Note deleted successfully');</script>"; 
    echo "<script>window.location.href = 'manage-notes.php'</script>";     
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-satahan || Manage Notes</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Main Styles */
        :root {
            --primary: #2563eb;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --border: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: var(--text-primary);
            line-height: 1.5;
            padding-top: 2rem;
        }
        
        .container {
            max-width: 1200px;
        }
        
        /* Header */
        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .app-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }
        
        .app-nav {
            display: flex;
            gap: 0.5rem;
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .page-subtitle {
            color: var(--text-secondary);
            max-width: 600px;
        }
        
        /* Card */
        .card {
            border-radius: 0.5rem;
            border: 1px solid var(--border);
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h5 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        /* Table */
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            color: var(--dark);
            border-bottom-width: 1px;
            background-color: #f8fafc;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        
        .table td {
            padding: 0.75rem 0.75rem;
            vertical-align: middle;
        }
        
        .table tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.25em 0.625em;
            border-radius: 0.25rem;
        }
        
        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        /* Buttons */
        .btn {
            padding: 0.375rem 0.75rem;
            font-weight: 500;
            border-radius: 0.25rem;
            transition: all 0.15s ease-in-out;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: #1e40af;
            border-color: #1e40af;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }
        
        .btn-icon {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            color: var(--secondary);
            opacity: 0.5;
            margin-bottom: 1rem;
        }
        
        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .empty-state-text {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }
        
        /* User Avatar */
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
        }
        
        /* User Menu */
        .user-menu {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-menu {
            border-radius: 0.5rem;
            border: 1px solid var(--border);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: var(--text-primary);
        }
        
        .dropdown-item:hover {
            background-color: #f8fafc;
            color: var(--primary);
        }
        
        .dropdown-item i {
            margin-right: 0.5rem;
            width: 1rem;
            text-align: center;
            color: var(--secondary);
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem 0 1rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        /* Media Queries */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .app-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- App Header -->
        <header class="app-header">
            <h1 class="app-title">
                <i class="fas fa-book-reader me-2"></i> e-satahan
            </h1>
            <div class="app-nav">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
                <div class="user-menu dropdown">
                    <?php
                    $uid = $_SESSION['ocasuid'];
                    $sql = "SELECT FullName FROM tbluser WHERE ID=:uid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                    $query->execute();
                    $userName = $query->fetchColumn() ?: 'User';
                    
                    // Get initials for avatar
                    $initials = strtoupper(substr($userName, 0, 1));
                    if (strpos($userName, ' ') !== false) {
                        $nameParts = explode(' ', $userName);
                        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1));
                    }
                    ?>
                    <div class="user-avatar dropdown-toggle" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $initials; ?>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="add-notes.php"><i class="fas fa-plus"></i> Add Notes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>
        
        <!-- Page Header -->
        <div class="page-header">
            <h2 class="page-title">Manage Notes</h2>
            <p class="page-subtitle">View and manage your uploaded notes.</p>
        </div>
        
        <!-- Notes Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Your Notes</h5>
                <a href="add-notes.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add New Note
                </a>
            </div>
            <div class="card-body p-0">
                <?php
                $uid = $_SESSION['ocasuid'];
                $sql = "SELECT * FROM tblnotes WHERE UserID=:uid ORDER BY CreationDate DESC";
                $query = $dbh->prepare($sql);
                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);
                $cnt = 1;
                
                if($query->rowCount() > 0) {
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($results as $row) { 
                                $status = $row->Status == 1 ? 
                                    '<span class="status-badge status-active">Active</span>' : 
                                    '<span class="status-badge status-pending">Pending</span>';
                            ?>
                            <tr>
                                <td><?php echo $cnt++; ?></td>
                                <td><?php echo htmlentities($row->NotesTitle); ?></td>
                                <td><?php echo htmlentities($row->Subject); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row->CreationDate)); ?></td>
                                <td><?php echo $status; ?></td>
                                <td class="text-end">
                                    <a href="view-notes.php?viewid=<?php echo $row->ID; ?>" class="btn btn-outline-primary btn-sm btn-icon" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit-notes.php?editid=<?php echo $row->ID; ?>" class="btn btn-outline-secondary btn-sm btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row->ID; ?>" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $row->ID; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $row->ID; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $row->ID; ?>">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete the note "<?php echo htmlentities($row->NotesTitle); ?>"?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <a href="manage-notes.php?delid=<?php echo $row->ID; ?>" class="btn btn-danger">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } else { ?>
                <div class="empty-state">
                    <i class="fas fa-book empty-state-icon"></i>
                    <h3 class="empty-state-title">No notes found</h3>
                    <p class="empty-state-text">You haven't uploaded any notes yet. Start by adding your first note.</p>
                    <a href="add-notes.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Note
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> e-satahan Notes Sharing System. All rights reserved.</p>
        </footer>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>