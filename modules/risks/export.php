<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();
require_once __DIR__ . '/helpers/risk_helper.php';

// Support format parameter for future PDF placeholder
$format = $_GET['format'] ?? 'csv';

if ($format === 'pdf') {
    // Placeholder for PDF implementation
    die("PDF export functionality is currently under development.");
}

// Reuse the exact same filters from index.php
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$department_id = $_GET['department_id'] ?? '';
$level = $_GET['level'] ?? '';
$treatment = $_GET['treatment'] ?? '';
$sort = $_GET['sort'] ?? 'new';

$whereSql = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereSql .= " AND (r.risk_id LIKE ? OR r.title LIKE ? OR r.description LIKE ? OR u.username LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}
if (!empty($status)) {
    $whereSql .= " AND r.status = ?";
    $params[] = $status;
}
if (!empty($category_id)) {
    $whereSql .= " AND r.category_id = ?";
    $params[] = $category_id;
}
if (!empty($department_id)) {
    $whereSql .= " AND r.department_id = ?";
    $params[] = $department_id;
}
if (!empty($treatment)) {
    $whereSql .= " AND r.treatment_strategy = ?";
    $params[] = $treatment;
}
if (!empty($level)) {
    if ($level === 'Critical') {
        $whereSql .= " AND (r.likelihood * r.impact) >= 16";
    } elseif ($level === 'High') {
        $whereSql .= " AND (r.likelihood * r.impact) >= 11 AND (r.likelihood * r.impact) <= 15";
    } elseif ($level === 'Medium') {
        $whereSql .= " AND (r.likelihood * r.impact) >= 6 AND (r.likelihood * r.impact) <= 10";
    } elseif ($level === 'Low') {
        $whereSql .= " AND (r.likelihood * r.impact) <= 5";
    }
}

$orderBySql = "ORDER BY r.id DESC";
if ($sort === 'score_desc') $orderBySql = "ORDER BY (r.likelihood * r.impact) DESC";
if ($sort === 'score_asc') $orderBySql = "ORDER BY (r.likelihood * r.impact) ASC";
if ($sort === 'updated') $orderBySql = "ORDER BY r.updated_at DESC";
if ($sort === 'review') $orderBySql = "ORDER BY r.review_date ASC";
if ($sort === 'id') $orderBySql = "ORDER BY r.risk_id ASC";

$query = "
    SELECT r.*, c.category_name, d.name as department_name, u.username as owner_name,
    (r.likelihood * r.impact) as risk_score
    FROM risks r
    LEFT JOIN risk_categories c ON r.category_id = c.id
    LEFT JOIN departments d ON r.department_id = d.id
    LEFT JOIN users u ON r.owner_id = u.id
    $whereSql
    $orderBySql
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$risks = $stmt->fetchAll();

// Generate CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Enterprise_Risk_Register_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Risk ID', 'Title', 'Description', 'Category', 'Department', 'Owner', 'Status', 'Treatment Strategy', 'Likelihood', 'Impact', 'Risk Score', 'Risk Level', 'AI Recommendation', 'Review Date', 'Created At']);

foreach ($risks as $risk) {
    $score = $risk['risk_score'];
    $rLevel = getRiskLevel($score);
    $rec = getRecommendedTreatment($score);
    
    fputcsv($output, [
        $risk['risk_id'],
        $risk['title'],
        $risk['description'],
        $risk['category_name'],
        $risk['department_name'],
        $risk['owner_name'],
        $risk['status'],
        $risk['treatment_strategy'],
        $risk['likelihood'],
        $risk['impact'],
        $score,
        $rLevel,
        $rec,
        $risk['review_date'],
        $risk['created_at']
    ]);
}
fclose($output);
exit;
