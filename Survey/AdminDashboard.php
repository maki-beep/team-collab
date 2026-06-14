<?php
include 'db_conn.php'; 

function fetchTotal($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $data = mysqli_fetch_assoc($result);
        return $data['total'] ?? 0;
    }
    return 0; 
}

// Count Statistics
$count_full  = fetchTotal($conn, "SELECT COUNT(*) as total FROM full_surveys");
$count_quick = fetchTotal($conn, "SELECT COUNT(*) as total FROM quick_reports");
$count_issue = fetchTotal($conn, "SELECT COUNT(*) as total FROM issue_reports");
$grand_total = $count_full + $count_quick + $count_issue;

// Filters
$filter_search_by = $_GET['search_by'] ?? '';
$filter_value     = mysqli_real_escape_string($conn, trim($_GET['filter_value'] ?? ''));
$filter_symptom   = mysqli_real_escape_string($conn, trim($_GET['symptom'] ?? ''));

$filter_issue_by    = $_GET['issue_search_by'] ?? '';
$filter_issue_value = mysqli_real_escape_string($conn, trim($_GET['issue_filter_value'] ?? ''));
$filter_issue_type  = mysqli_real_escape_string($conn, trim($_GET['issue_type'] ?? ''));

$clean_health_id = preg_replace('/[^0-9]/', '', $filter_value);
$clean_issue_id  = preg_replace('/[^0-9]/', '', $filter_issue_value);

// Health Logs Filter
$health_where_full  = "1=1";
$health_where_quick = "1=1";

if ($filter_search_by === 'id' && $clean_health_id !== '') {
    $health_where_full .= " AND id = '{$clean_health_id}'";
    $health_where_quick .= " AND id = '{$clean_health_id}'";
} elseif ($filter_search_by === 'stall' && $filter_value !== '') {
    $health_where_full .= " AND stall_id LIKE '%{$filter_value}%'";
    $health_where_quick .= " AND stall_id LIKE '%{$filter_value}%'";
}

if (!empty($filter_symptom)) {
    $health_where_full .= " AND symptoms LIKE '%{$filter_symptom}%'";
    $health_where_quick .= " AND symptoms LIKE '%{$filter_symptom}%'";
}

// FIXED QUERY - Now includes names
$health_query_sql = "
    (SELECT id, first_name, middle_initial, last_name, product_type, stall_id, 
            created_at AS order_time, symptoms AS symp_list, 'FULL' AS type 
     FROM full_surveys WHERE {$health_where_full})
    UNION
    (SELECT id, first_name, middle_initial, last_name, product_type, stall_id, 
            created_at AS order_time, symptoms AS symp_list, 'QUICK' AS type 
     FROM quick_reports WHERE {$health_where_quick})
    ORDER BY order_time DESC LIMIT 50";

// Issue Reports Filter
$issue_where = "1=1";
if ($filter_issue_by === 'id' && $clean_issue_id !== '') {
    $issue_where .= " AND id = '{$clean_issue_id}'";
}
if (!empty($filter_issue_type)) {
    $issue_where .= " AND issues LIKE '%{$filter_issue_type}%'";
}

$issue_query_sql = "SELECT * FROM issue_reports WHERE {$issue_where} ORDER BY created_at DESC LIMIT 50";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="AdminDashboard.css"/>
    <title>Altura-579 Admin Dashboard</title>
    <style>
        .card-header h2 {
            color: #64748b;
            font-size: 1.25rem;
            margin: 0;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card-header {
            display: flex;
            flex-direction: column; 
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 20px;
            padding: 0 50px; 
        }
        .filter-row {
            display: flex;
            align-items: center;
            justify-content: flex-start; 
            gap: 10px;
            width: 100%;
            flex-wrap: wrap;
        }
        .filter-row select, .filter-row input {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #cbd5e1;
            font-size: 0.85rem;
            color: #334155;
            background-color: #fff;
        }
        table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
        thead th {
            background-color: #f8fafc;
            color: #94a3b8;
            text-transform: uppercase;
            font-size: 0.75rem; 
            font-weight: 700; 
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 12px;
            text-align: left;
        }
        td { 
            padding: 18px 12px; 
            text-align: left; 
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
            color: #475569;
        }
        td strong { color: #0f172a; font-weight: 800; }

        th:first-child, td:first-child { padding-left: 50px; }
        th:not(:first-child), td:not(:first-child) { padding-left: 20px; }

        .btn-filter { background: #334155; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .btn-clear-x { color: #94a3b8; text-decoration: none; font-size: 1.3rem; font-weight: bold; margin-left: 8px; }

        .export-btn {
            background: #10b981;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        }
        .export-btn:hover {
            background: #059669;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header style="padding: 20px 50px;">
            <div class="header-title">
                <h1>Altura-579 Public Market Health Monitoring Tracker</h1>
                <p>Brgy. 579 Public Market Safety Admin</p>
            </div>
            <div class="header-info" style="margin-top: 10px;">
                <strong>Administrator:</strong> Karylle Maboloc
            </div>
        </header>

        <section class="stats-bar" style="padding: 0 50px;">
            <div class="stat-card"><h3>Total Submissions</h3><p><?php echo $grand_total; ?></p></div>
            <div class="stat-card"><h3>Health Surveys</h3><p><?php echo $count_full; ?></p></div>
            <div class="stat-card"><h3>Quick Reports</h3><p><?php echo $count_quick; ?></p></div>
            <div class="stat-card"><h3>Reported Issues</h3><p><?php echo $count_issue; ?></p></div>
        </section>

        <!-- HEALTH MONITORING LOG -->
        <div class="card">
            <div class="card-header">
                <div style="margin-top: 12px; display: flex; justify-content: flex-end; width: 100%;">
                    <a href="export.php?type=health" class="export-btn">
                        Export Health Logs (CSV)
                    </a>
                </div>
                <h2>Health Monitoring Log</h2>
                
                <form method="GET" action="" class="filter-row">
                    <input type="hidden" name="issue_search_by" value="<?php echo htmlspecialchars($filter_issue_by); ?>">
                    <input type="hidden" name="issue_filter_value" value="<?php echo htmlspecialchars($filter_issue_value); ?>">
                    <input type="hidden" name="issue_type" value="<?php echo htmlspecialchars($filter_issue_type); ?>">

                    <select name="search_by" onchange="this.form.submit()">
                        <option value="">Search By...</option>
                        <option value="id" <?php echo ($filter_search_by === 'id') ? 'selected' : ''; ?>>ID</option>
                        <option value="stall" <?php echo ($filter_search_by === 'stall') ? 'selected' : ''; ?>>Stall</option>
                    </select>
                    
                    <?php if($filter_search_by): ?>
                        <input type="text" name="filter_value" placeholder="Enter <?php echo strtoupper($filter_search_by); ?>..." value="<?php echo htmlspecialchars($filter_value); ?>">
                    <?php endif; ?>

                    <select name="symptom" onchange="this.form.submit()">
                        <option value="">All Symptoms</option>
                        <?php
                        $symptoms_list = ['Loss of Appetite','Fever','Cough','Fatigue','Nausea','Diarrhea','Abdominal Pain'];
                        foreach ($symptoms_list as $s) {
                            $sel = ($filter_symptom === $s) ? 'selected' : '';
                            echo "<option value=\"{$s}\" {$sel}>{$s}</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn-filter">Search</button>
                    <?php if ($filter_search_by || $filter_symptom): ?>
                        <a href="AdminDashboard.php" class="btn-clear-x">×</a>
                    <?php endif; ?>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>M.I.</th>
                        <th>Last Name</th>
                        <th>Product Type</th>
                        <th>Stall/Vendor</th>
                        <th>Time</th>
                        <th>Reported Symptoms</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $health_result = mysqli_query($conn, $health_query_sql);
                    while ($row = mysqli_fetch_assoc($health_result)) {
                        $displayID = $row['type'] . str_pad($row['id'], 3, "0", STR_PAD_LEFT);
                        
                        echo "<tr>
                                <td><strong>{$displayID}</strong></td>
                                <td>" . htmlspecialchars($row['first_name'] ?? 'N/A') . "</td>
                                <td>" . htmlspecialchars($row['middle_initial'] ?? '') . "</td>
                                <td>" . htmlspecialchars($row['last_name'] ?? 'N/A') . "</td>
                                <td>" . htmlspecialchars($row['product_type'] ?? 'N/A') . "</td>
                                <td>" . htmlspecialchars($row['stall_id'] ?? 'N/A') . "</td>
                                <td>" . date('M d, h:i A', strtotime($row['order_time'])) . "</td>
                                <td>" . htmlspecialchars($row['symp_list'] ?? 'N/A') . "</td>
                                <td>
                                    <a href='edit_health.php?id={$row['id']}&type={$row['type']}' class='btn-action edit'>✏️ Edit</a>
                                    <a href='delete.php?id={$row['id']}&type={$row['type']}' 
                                       onclick=\"return confirm('Delete this record?')\" 
                                       class='btn-action delete'>🗑️ Delete</a>
                                </td>
                              </tr>";
                    }
                    if (mysqli_num_rows($health_result) == 0) {
                        echo "<tr><td colspan='9' style='text-align:center; padding:30px;'>No records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

            <!-- REPORTED ISSUES LOG -->
            <div class="card">
                <div class="card-header">
                    <div style="margin-top: 12px; display: flex; justify-content: flex-end; width: 100%;">
                        <a href="export.php?type=issue" class="export-btn">
                        Export Issue Report (CSV)
                    </a>
                </div>
                <h2>Reported Issues Log</h2>
                    <--Comment-->
                
                
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Stall/Vendor</th>
                        <th>Reported Issue</th>
                        <th>Time Reported</th>
                        <th>Evidence</th>
                        <th>Actions</th>   <!-- ← NEW -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $issue_result = mysqli_query($conn, $issue_query_sql);
                    while ($row = mysqli_fetch_assoc($issue_result)) {
                        $displayID = "ISSUE" . str_pad($row['id'], 3, "0", STR_PAD_LEFT);
                        $photoName = $row['qc_photo'] ?? '';
                        $evidenceLink = !empty($photoName) 
                            ? "<a href='uploads/{$photoName}' target='_blank' style='color:#1e40af; font-weight:600;'>View Photo</a>" 
                            : "<span style='color:#94a3b8;'>No Photo</span>";
                        
                        echo "<tr>
                                <td><strong>{$displayID}</strong></td>
                                <td>" . htmlspecialchars($row['stall_id']) . "</td>
                                <td>" . htmlspecialchars($row['issues']) . "</td>
                                <td>" . date('M d, h:i A', strtotime($row['created_at'])) . "</td>
                                <td>{$evidenceLink}</td>
                                <td>
                                    <a href='edit_issue.php?id={$row['id']}' class='btn-action edit'>✏️ Edit</a>
                                    <a href='delete.php?id={$row['id']}&type=ISSUE' 
                                    onclick=\"return confirm('Delete this issue report?')\" 
                                    class='btn-action delete'>🗑️ Delete</a>
                                </td>
                            </tr>";
                    }
                    if (mysqli_num_rows($issue_result) == 0) {
                        echo "<tr><td colspan='6' style='text-align:center; padding:30px;'>No issues reported yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
