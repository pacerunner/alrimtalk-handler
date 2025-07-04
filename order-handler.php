<?php
// 1. 카페24에서 날아온 데이터를 받아요
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// 2. 이벤트 종류에 따라 템플릿을 다르게 해요
$eventType = $data['event'];
$apiKey = '톡드림에서 받은 API 키';
$senderKey = '카카오 발신 키';

if ($eventType === 'member_join') {
    $phone = $data['member']['cell_phone'];
    $name = $data['member']['name'];
    $templateCode = 'JOIN_001';
    $message = "[슬로큐어] {$name}님, 회원가입을 환영합니다!";
}
elseif ($eventType === 'order_paid') {
    $phone = $data['order']['orderer_cell_phone'];
    $templateCode = 'ORDER_001';
    $message = "[슬로큐어] 주문이 완료되었습니다. 감사합니다!";
}
elseif ($eventType === 'order_shipping') {
    $phone = $data['order']['receiver_cell_phone'];
    $invoice = $data['order']['invoice_no'];
    $templateCode = 'SHIP_001';
    $message = "[슬로큐어] 상품이 발송되었어요. 송장번호: {$invoice}";
}
else {
    exit; // 이외 이벤트는 처리 안 함
}

// 3. 톡드림에 알림톡 보내기 요청
$postData = [
    "apiKey" => $apiKey,
    "senderKey" => $senderKey,
    "templateCode" => $templateCode,
    "recipientList" => [
        [
            "recipient" => $phone,
            "message" => $message
        ]
    ]
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode($postData)
    ]
];

$result = file_get_contents("https://api.talkdream.co.kr/v1/kakao/alimtalk/send", false, stream_context_create($options));
?>
