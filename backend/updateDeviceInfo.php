<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// 입력 받은 JSON 데이터 처리
$data = json_decode(file_get_contents('php://input'), true);
$serialNumber = $data['serialNumber'] ?? null;

if ($serialNumber) {
    // 예시로 업데이트된 정보를 로그 파일에 기록
    $log = "Serial Number: $serialNumber\n";
    file_put_contents('device_update_log.txt', $log, FILE_APPEND);
    
    // JSON 응답 생성
    $response = [
        'status' => 'success',
        'message' => '기기 정보가 성공적으로 업데이트되었습니다.',
        'serialNumber' => $serialNumber
    ];
    echo json_encode($response);
} else {
    // JSON 응답 생성
    $response = [
        'status' => 'error',
        'message' => '기기 시리얼 번호가 누락되었습니다.'
    ];
    echo json_encode($response);
}
?>
