<?php

// Don't disturb
require __DIR__ . "/../../vendor/autoload.php";

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Function
// Function
function getHijrResponse($adjustment)
{
    $api_url = "https://api.myquran.com/v2/cal/hijr?adj=" . $adjustment;
    $json_data = file_get_contents($api_url);
    $data = json_decode($json_data, true);
    return $data['data']['date'];
}

// Make sure JSON data is not incomplete
if (!empty($data->query) && !empty($data->appPackageName) && !empty($data->messengerPackageName) && !empty($data->query->sender) && !empty($data->query->message)) {
    $appPackageName = $data->appPackageName;
    $messengerPackageName = $data->messengerPackageName;
    $sender = $data->query->sender;
    $message = $data->query->message;
    $isGroup = $data->query->isGroup;
    $groupParticipant = $data->query->groupParticipant;
    $ruleId = $data->query->ruleId;
    $isTestMessage = $data->query->isTestMessage;

    // Process messages here

    /* It looks like this code will not be used
    if (isset($_SERVER["HTTP_COMMAND"])) {
        $command = $_SERVER["HTTP_COMMAND"];
        if (strpos($message, $command) === 0) {
            $message = trim(substr($message, strlen($command)));
            http_response_code(200);
            echo json_encode(["replies" => [["message" => $message]]]);
            exit();
        }
    } */

    $result = getHijrResponse(-2);
    $response = "• " . $result[0] . "\n• " . $result[1] . "\n• " . $result[2];
    http_response_code(200);
    echo json_encode(["replies" => [["message" => $response]]]);
} else {
    http_response_code(400);
    echo json_encode(["replies" => [["message" => "❌ Error!"], ["message" => "JSON data is incomplete. Was the request sent by AutoResponder?"]]]);
}
?>