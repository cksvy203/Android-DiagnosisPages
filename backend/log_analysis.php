<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$adb_path = $_SESSION['adb_path'] ?? '';
if (empty($adb_path)) {
    echo '<p>ADB 경로가 설정되지 않았습니다.</p>';
    exit;
}

$device = $_GET['device'] ?? '';
if (empty($device)) {
    echo '<p>기기가 선택되지 않았습니다.</p>';
    exit;
}

$output = [];
$return_var = 0;

// adb logcat 명령어 실행 (로그 5줄 제한)
exec("$adb_path -s $device logcat -d -t 5 2>&1", $output, $return_var);

// 결과를 HTML 형식으로 출력
if (!empty($output)) {
    echo '<pre>' . htmlspecialchars(implode("\n", $output), ENT_QUOTES, 'UTF-8') . '</pre>';
} else {
    echo '<p>로그 데이터가 없습니다.</p>';
}
?>
