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
function getSimsimiResponse($message, $language, $apiKey = "")
{
    $apiUrl = "https://api.simsimi.vn/v1/simtalk";

    $postData = [
        "text" => $message,
        "lc" => $language,
        "key" => $apiKey,
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return null;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    return isset($result["message"]) ? $result["message"] : null;
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
    if (isset($_SERVER["HTTP_COMMAND"])) {
        $command = $_SERVER["HTTP_COMMAND"];
        if (strpos($message, $command) === 0) {
            $message = trim(substr($message, strlen($command)));

            // Headers
            $language = $_SERVER["HTTP_LANGUAGE"];
            $apiKey = $_SERVER["HTTP_APIKEY"];

            $response = getSimsimiResponse($message, $language, $apiKey);
            http_response_code(200);
            echo json_encode(["replies" => [["message" => $response]]]);
            exit();
        }
    }

    // Headers
    $language = $_SERVER["HTTP_LANGUAGE"];
    $apiKey = $_SERVER["HTTP_APIKEY"];

    $response = getSimsimiResponse($message, $language, $apiKey);
    http_response_code(200);
    echo json_encode(["replies" => [["message" => $response]]]);
} else {
    http_response_code(400);
    echo json_encode([
        "replies" => [
            ["message" => "❌ Error!"],
            [
                "message" => "JSON data is incomplete. Was the request sent by AutoResponder?",
            ],
        ],
    ]);
}
?>