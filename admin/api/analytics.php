<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

$cabang_id = $_SESSION['cabang_id'] ?? null;
$days = isset($_GET['days']) ? intval($_GET['days']) : 7;

// Add debug info to response
$response_debug = [
    'session_cabang_id' => $cabang_id,
    'session_role_id' => $_SESSION['role_id'] ?? null,
    'days_requested' => $days
];

// Initialize response structure
$response = [
    'dates' => [],
    'throughput' => [],
    'avg_wait' => [],
    'per_staff' => [],
    'hourly_dist' => ['hours' => [], 'counts' => []],
    'service_breakdown' => ['cs' => [], 'teller' => [], 'kredit' => []],
    'staff_performance' => [],
    'peak_hour' => null,
    'debug_session' => $response_debug
];

// Generate date range
for ($i = $days - 1; $i >= 0; $i--) {
    $response['dates'][] = date('Y-m-d', strtotime("-$i days"));
}

// Get throughput and service breakdown by date
foreach ($response['dates'] as $date) {
    $cs_count = 0;
    $teller_count = 0;
    $kredit_count = 0;
    
    // CS
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM tbl_antrian WHERE DATE(tanggal) = ? " . ($cabang_id ? "AND cabang_id = ?" : ""));
    if ($cabang_id) {
        $stmt->bind_param("si", $date, $cabang_id);
    } else {
        $stmt->bind_param("s", $date);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $cs_count = $result['cnt'] ?? 0;
    
    // Teller
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM tbl_antrian_teller WHERE DATE(tanggal_teller) = ? " . ($cabang_id ? "AND cabang_id = ?" : ""));
    if ($cabang_id) {
        $stmt->bind_param("si", $date, $cabang_id);
    } else {
        $stmt->bind_param("s", $date);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $teller_count = $result['cnt'] ?? 0;
    
    // Kredit
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM tbl_antrian_kredit WHERE DATE(tanggal_kredit) = ? " . ($cabang_id ? "AND cabang_id = ?" : ""));
    if ($cabang_id) {
        $stmt->bind_param("si", $date, $cabang_id);
    } else {
        $stmt->bind_param("s", $date);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $kredit_count = $result['cnt'] ?? 0;
    
    $response['service_breakdown']['cs'][] = $cs_count;
    $response['service_breakdown']['teller'][] = $teller_count;
    $response['service_breakdown']['kredit'][] = $kredit_count;
    $response['throughput'][] = $cs_count + $teller_count + $kredit_count;
}

// Get average wait times
foreach ($response['dates'] as $date) {
    $total_wait = 0;
    $count = 0;
    
    // CS wait times
    $stmt = $mysqli->prepare("SELECT AVG(durasi) as avg_dur FROM tbl_antrian WHERE DATE(tanggal) = ? AND durasi IS NOT NULL " . ($cabang_id ? "AND cabang_id = ?" : ""));
    if ($cabang_id) {
        $stmt->bind_param("si", $date, $cabang_id);
    } else {
        $stmt->bind_param("s", $date);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result['avg_dur']) {
        $total_wait += $result['avg_dur'];
        $count++;
    }
    
    // Teller wait times
    $stmt = $mysqli->prepare("SELECT AVG(durasi) as avg_dur FROM tbl_antrian_teller WHERE DATE(tanggal_teller) = ? AND durasi IS NOT NULL " . ($cabang_id ? "AND cabang_id = ?" : ""));
    if ($cabang_id) {
        $stmt->bind_param("si", $date, $cabang_id);
    } else {
        $stmt->bind_param("s", $date);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result['avg_dur']) {
        $total_wait += $result['avg_dur'];
        $count++;
    }
    
    // Kredit wait times
    $stmt = $mysqli->prepare("SELECT AVG(durasi) as avg_dur FROM tbl_antrian_kredit WHERE DATE(tanggal_kredit) = ? AND durasi IS NOT NULL " . ($cabang_id ? "AND cabang_id = ?" : ""));
    if ($cabang_id) {
        $stmt->bind_param("si", $date, $cabang_id);
    } else {
        $stmt->bind_param("s", $date);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result['avg_dur']) {
        $total_wait += $result['avg_dur'];
        $count++;
    }
    
    $response['avg_wait'][] = $count > 0 ? round($total_wait / $count) : 0;
}

// Get hourly distribution (last 7 days)
for ($hour = 8; $hour <= 17; $hour++) {
    $response['hourly_dist']['hours'][] = sprintf('%02d:00', $hour);
    $count = 0;
    
    $start_date = date('Y-m-d', strtotime("-$days days"));
    $end_date = date('Y-m-d');
    
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM tbl_antrian WHERE DATE(tanggal) BETWEEN ? AND ? AND HOUR(tanggal) = ? " . ($cabang_id ? "AND cabang_id = ?" : ""));
    if ($cabang_id) {
        $stmt->bind_param("ssii", $start_date, $end_date, $hour, $cabang_id);
    } else {
        $stmt->bind_param("ssi", $start_date, $end_date, $hour);
    }
    $stmt->execute();
    $count += $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
    
    $response['hourly_dist']['counts'][] = $count;
}

// Get peak hour
$max_count = max($response['hourly_dist']['counts']);
$peak_index = array_search($max_count, $response['hourly_dist']['counts']);
$response['peak_hour'] = $peak_index !== false ? $response['hourly_dist']['hours'][$peak_index] : null;

// Get per staff/section activity
$bagian_map = [];
$start_date = date('Y-m-d', strtotime("-$days days"));
$end_date = date('Y-m-d');

// Debug logging
error_log("Analytics Debug - Start Date: $start_date, End Date: $end_date, Cabang ID: " . ($cabang_id ?? 'ALL'));

// CS (Customer Service)
$stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM tbl_antrian WHERE DATE(tanggal) BETWEEN ? AND ? " . ($cabang_id ? "AND cabang_id = ?" : ""));
if ($cabang_id) {
    $stmt->bind_param("ssi", $start_date, $end_date, $cabang_id);
} else {
    $stmt->bind_param("ss", $start_date, $end_date);
}
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row['cnt'] > 0) {
    $bagian_map['Customer Service'] = $row['cnt'];
}
error_log("CS Count: " . ($row['cnt'] ?? 0));

// Teller - separate into Default, A, and B based on bagian field
$teller_query = "SELECT 
    CASE 
        WHEN bagian = '1' THEN 'teller_a'
        WHEN bagian = '2' THEN 'teller_b'
        ELSE 'teller_default'
    END as bagian_type,
    COUNT(*) as cnt 
    FROM tbl_antrian_teller 
    WHERE DATE(tanggal_teller) BETWEEN ? AND ? " 
    . ($cabang_id ? "AND cabang_id = ? " : "") 
    . "GROUP BY bagian_type";
error_log("Teller Query: $teller_query");
$stmt = $mysqli->prepare($teller_query);
if ($cabang_id) {
    $stmt->bind_param("ssi", $start_date, $end_date, $cabang_id);
} else {
    $stmt->bind_param("ss", $start_date, $end_date);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $bagian_type = $row['bagian_type'];
    $count = $row['cnt'];
    error_log("Teller Row - Type: $bagian_type, Count: $count");
    // Convert bagian type to readable labels
    if ($bagian_type == 'teller_a') {
        $key = 'Teller A';
    } elseif ($bagian_type == 'teller_b') {
        $key = 'Teller B';
    } else {
        $key = 'Teller';
    }
    $bagian_map[$key] = ($bagian_map[$key] ?? 0) + $row['cnt'];
}

// Kredit
$stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM tbl_antrian_kredit WHERE DATE(tanggal_kredit) BETWEEN ? AND ? " . ($cabang_id ? "AND cabang_id = ?" : ""));
if ($cabang_id) {
    $stmt->bind_param("ssi", $start_date, $end_date, $cabang_id);
} else {
    $stmt->bind_param("ss", $start_date, $end_date);
}
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row['cnt'] > 0) {
    $bagian_map['Kredit'] = $row['cnt'];
}
error_log("Kredit Count: " . ($row['cnt'] ?? 0));

error_log("Final Bagian Map: " . json_encode($bagian_map));

foreach ($bagian_map as $label => $value) {
    $response['per_staff'][] = ['label' => $label, 'value' => $value];
}

// Debug: Add raw bagian_map to response for troubleshooting
$response['debug_bagian_map'] = $bagian_map;

// Staff performance (mock data - customize based on your schema)
foreach ($bagian_map as $bagian => $total) {
    $response['staff_performance'][] = [
        'name' => $bagian ?: 'Unknown',
        'total_served' => $total,
        'avg_wait' => rand(30, 120),
        'avg_service_time' => rand(60, 300),
        'efficiency' => rand(60, 95)
    ];
}

echo json_encode($response);
