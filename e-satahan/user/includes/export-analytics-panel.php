<!-- Export Analytics Functionality -->

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Export Analytics Data</h5>
            </div>
            <div class="card-body">
                <form id="exportForm" method="get" action="../export_analytics.php" class="form-inline">
                    <!-- Hidden fields for current filters -->
                    <input type="hidden" name="start_date" value="<?php echo $startDate; ?>">
                    <input type="hidden" name="end_date" value="<?php echo $endDate; ?>">
                    <input type="hidden" name="subject" value="<?php echo htmlspecialchars($subject); ?>">
                    
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label for="report">Report Type</label>
                            <select class="form-control w-100" id="report" name="report">
                                <option value="downloads">Downloads Report</option>
                                <option value="views">Views Report</option>
                                <option value="users">User Activity Report</option>
                                <option value="subjects">Subject Popularity Report</option>
                                <option value="shares">Shares Report</option>
                                <option value="ratings">Ratings Report</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-2">
                            <label for="format">Format</label>
                            <select class="form-control w-100" id="format" name="format">
                                <option value="excel">Excel (.xls)</option>
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-download mr-1"></i> Export Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
