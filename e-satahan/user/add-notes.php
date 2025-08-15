<?php
session_start();
//error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['ocasuid']==0)) {
  header('location:logout.php');
  } else{
    if(isset($_POST['submit']))
  {
    $ocasuid=$_SESSION['ocasuid'];
    $subject=$_POST['subject'];
    $notestitle=$_POST['notestitle'];
    $notesdesc=$_POST['notesdesc'];
    $file1=$_FILES["file1"]["name"];
    
    $extension1 = substr($file1,strlen($file1)-4,strlen($file1));
    $file2=$_FILES["file2"]["name"];
    $extension2 = substr($file2,strlen($file2)-4,strlen($file2));
    $file3=$_FILES["file3"]["name"];
    $extension3 = substr($file3,strlen($file3)-4,strlen($file3));
    $file4=$_FILES["file4"]["name"];
    $extension4 = substr($file4,strlen($file4)-4,strlen($file4));
    $allowed_extensions = array("docs",".doc",".pdf");

    if(!in_array($extension1,$allowed_extensions))
    {
      echo "<script>alert('File has Invalid format. Only docs / doc/ pdf format allowed');</script>";
    }
    else {
      $file1=md5($file).time().$extension1;
      if($file2!=''):
      $file2=md5($file).time().$extension2; endif;
      if($file3!=''):
      $file3=md5($file).time().$extension3; endif;
      if($file4!=''):
      $file4=md5($file).time().$extension4; endif;
      move_uploaded_file($_FILES["file1"]["tmp_name"],"folder1/".$file1);
      move_uploaded_file($_FILES["file2"]["tmp_name"],"folder2/".$file2);
      move_uploaded_file($_FILES["file3"]["tmp_name"],"folder3/".$file3);
      move_uploaded_file($_FILES["file4"]["tmp_name"],"folder4/".$file4);

      $sql="insert into tblnotes(UserID,Subject,NotesTitle,NotesDecription,File1,File2,File3,File4)values(:ocasuid,:subject,:notestitle,:notesdesc,:file1,:file2,:file3,:file4)";
      $query=$dbh->prepare($sql);
      $query->bindParam(':ocasuid',$ocasuid,PDO::PARAM_STR);
      $query->bindParam(':subject',$subject,PDO::PARAM_STR);
      $query->bindParam(':notestitle',$notestitle,PDO::PARAM_STR);
      $query->bindParam(':notesdesc',$notesdesc,PDO::PARAM_STR);
      $query->bindParam(':file1',$file1,PDO::PARAM_STR);
      $query->bindParam(':file2',$file2,PDO::PARAM_STR);
      $query->bindParam(':file3',$file3,PDO::PARAM_STR);
      $query->bindParam(':file4',$file4,PDO::PARAM_STR);

      $query->execute();

      $LastInsertId=$dbh->lastInsertId();
      if ($LastInsertId>0) {
        echo '<script>alert("Notes has been added.")</script>';
        echo "<script>window.location.href ='add-notes.php'</script>";
      }
      else
      {
        echo '<script>alert("Something Went Wrong. Please try again")</script>';
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-satahan || Add Notes</title>
  
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fe;
            font-family: 'Heebo', sans-serif;
            color: #495057;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .app-logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2b4cca;
            text-decoration: none;
        }
        
        .app-logo i {
            margin-right: 0.5rem;
            font-size: 1.75rem;
        }
        
        .app-nav {
            display: flex;
            gap: 1rem;
        }
        
        .page-header {
            background: linear-gradient(120deg, #2b4cca 0%, #5978e9 100%);
            color: white;
            border-radius: 0.75rem;
            padding: 1.8rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 20px rgba(43, 76, 202, 0.15);
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: "";
            position: absolute;
            top: -40%;
            right: -40%;
            width: 80%;
            height: 200%;
            opacity: 0.08;
            background: radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(25deg);
        }
        
        .page-header .header-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.35rem;
        }
        
        .page-header .header-subtitle {
            opacity: 0.85;
            font-weight: 400;
            margin-bottom: 0;
            max-width: 80%;
        }
        
        .notes-form-card {
            border-radius: 0.75rem;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.05);
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .notes-form-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.2rem 1.5rem;
            border-radius: 0.75rem 0.75rem 0 0;
        }
        
        .notes-form-body {
            padding: 1.5rem;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #5978e9;
        }
        
        .form-control:focus {
            border-color: #5978e9;
            box-shadow: 0 0 0 0.25rem rgba(89, 120, 233, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(120deg, #2b4cca 0%, #5978e9 100%);
            border: none;
            box-shadow: 0 3px 8px rgba(43, 76, 202, 0.25);
            padding: 0.65rem 1.75rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(43, 76, 202, 0.35);
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
        
        .btn-outline-secondary {
            border-color: #ced4da;
            color: #6c757d;
            transition: all 0.3s;
        }
        
        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .note-editor {
            border-radius: 0.375rem;
        }
        
        .note-toolbar {
            background-color: #f8f9fa;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        
        .form-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #324054;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .alert-upload-requirements {
            background-color: rgba(89, 120, 233, 0.1);
            color: #5978e9;
            border: none;
            border-left: 4px solid #5978e9;
            border-radius: 0.25rem;
        }
        
        .app-footer {
            text-align: center;
            padding-top: 2rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <!-- Main Container -->
    <div class="container">
        <!-- App Header -->
        <div class="app-header">
            <a href="dashboard.php" class="app-logo">
                <i class="fas fa-book-reader"></i> e-satahan
            </a>
            <div class="app-nav">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="manage-notes.php" class="btn btn-outline-primary">
                    <i class="fas fa-list me-2"></i> Manage Notes
                </a>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h4 class="header-title">Add New Notes</h4>
                    <p class="header-subtitle mb-0">Share your knowledge with the community by uploading study materials</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="manage-notes.php" class="btn btn-light">
                        <i class="fas fa-list me-2"></i> View My Notes
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card notes-form-card">
            <div class="notes-form-header">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Note Information</h5>
            </div>
            <div class="notes-form-body">
                <form method="post" enctype="multipart/form-data" id="notesForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="notesTitle">
                                <i class="fas fa-heading me-1 text-primary"></i> Notes Title
                            </label>
                            <input type="text" class="form-control" id="notesTitle" name="notestitle" required placeholder="Enter a descriptive title">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="subject">
                                <i class="fas fa-book me-1 text-primary"></i> Subject
                            </label>
                            <input type="text" class="form-control" id="subject" name="subject" required placeholder="Enter the subject">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label" for="notesDesc">
                            <i class="fas fa-align-left me-1 text-primary"></i> Notes Description
                        </label>
                        <textarea class="form-control" id="notesDesc" name="notesdesc" rows="6" required placeholder="Provide a detailed description of your notes"></textarea>
                    </div>
                    
                    <div class="alert alert-upload-requirements mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Allowed file formats:</strong> .doc, .docs, .pdf | <strong>Max file size:</strong> 10MB
                    </div>
                    
                    <div class="form-section-title">Upload Files</div>
                    
                    <div class="mb-4">
                        <label class="form-label required">
                            <i class="fas fa-file-upload me-1 text-primary"></i> Main Document (Required)
                        </label>
                        <div class="input-group">
                            <input type="file" class="form-control" name="file1" required>
                            <span class="input-group-text"><i class="fas fa-file-pdf"></i></span>
                        </div>
                    </div>
                    
                    <div class="form-section-title">Additional Resources (Optional)</div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Additional Document 1</label>
                            <div class="input-group">
                                <input type="file" class="form-control" name="file2">
                                <span class="input-group-text"><i class="fas fa-file-pdf"></i></span>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Additional Document 2</label>
                            <div class="input-group">
                                <input type="file" class="form-control" name="file3">
                                <span class="input-group-text"><i class="fas fa-file-pdf"></i></span>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Additional Document 3</label>
                            <div class="input-group">
                                <input type="file" class="form-control" name="file4">
                                <span class="input-group-text"><i class="fas fa-file-pdf"></i></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="manage-notes.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Upload Notes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- App Footer -->
        <div class="app-footer">
            <p>&copy; <?php echo date('Y'); ?> e-satahan Notes Sharing System. All rights reserved.</p>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize rich text editor for notes description
            $('#notesDesc').summernote({
                placeholder: 'Write detailed notes description here...',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen']]
                ]
            });
            
            // Form validation
            $('#notesForm').submit(function() {
                // You can add additional validation here
                return true;
            });
        });
    </script>
</body>
</html>
<?php }  ?>