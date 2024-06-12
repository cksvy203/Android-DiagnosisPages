<?php
// 에러를 JSON 형식으로 출력하도록 설정
function jsonErrorHandler($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error' => "$errstr in $errfile on line $errline"]);
    exit;
}
set_error_handler('jsonErrorHandler');

// 기존 코드 시작
session_start();
header('Content-Type: application/json; charset=UTF-8');

$adb_path = $_SESSION['adb_path'] ?? '';
if (empty($adb_path)) {
    echo json_encode(['error' => 'ADB 경로가 설정되지 않았습니다.']);
    exit;
}

$device = $_GET['device'] ?? '';
if (empty($device)) {
    echo json_encode(['error' => '기기가 선택되지 않았습니다.']);
    exit;
}

$output = [];
$return_var = 0;

// adb shell dumpsys battery 명령어 실행
exec("$adb_path -s $device shell dumpsys battery", $output, $return_var);

if ($return_var !== 0) {
    echo json_encode(['error' => '배터리 상태를 가져오는 데 실패했습니다.']);
    exit;
}

// 배터리 상태를 JSON 형식으로 반환
echo json_encode(['status' => implode("\n", $output)]);
?>
