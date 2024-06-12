<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');  // Content-Type을 HTML로 설정

// 입력 받은 JSON 데이터 처리
$data = json_decode(file_get_contents('php://input'), true);
$vendorId = $data['vendorId'] ?? null;
$productId = $data['productId'] ?? null;

// 시스템에 맞는 adb 경로 설정
$adb_path = 'C:\\adb\\adb.exe';  // ADB 파일이 있는 정확한 경로로 수정
$_SESSION['adb_path'] = $adb_path;

$output = [];
$return_var = 0;

// adb devices 명령어 실행
exec("$adb_path devices 2>&1", $output, $return_var);

$devices = [];
foreach ($output as $line) {
    if (strpos($line, 'device') !== false && strpos($line, 'List') === false) {
        $device = explode("\t", $line)[0];
        $devices[] = $device;
    }
}

// 결과를 HTML 형식으로 출력
if (!empty($devices)) {
    echo '<ul>';
    foreach ($devices as $device) {
        echo '<li>' . htmlspecialchars($device, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>디바이스 목록이 비어 있습니다.</p>';
}

// 추가 작업: 기기 정보를 기반으로 처리
if ($vendorId && $productId) {
    // 예시로 기기 정보를 로그 파일에 기록
    $log = "Vendor ID: $vendorId, Product ID: $productId\n";
    file_put_contents('device_log.txt', $log, FILE_APPEND);
    
    // JSON 응답 생성
    $response = [
        'status' => 'success',
        'message' => '기기 정보가 성공적으로 처리되었습니다.',
        'vendorId' => $vendorId,
        'productId' => $productId
    ];
    echo json_encode($response);
} else {
    // JSON 응답 생성
    $response = [
        'status' => 'error',
        'message' => '기기 정보가 누락되었습니다.'
    ];
    echo json_encode($response);
}
?>

