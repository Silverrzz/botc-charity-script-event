<?php

include_once 'database.php';

session_start();

$postData = file_get_contents("php://input");
$data = json_decode($postData, true);

$action = $data['action'];
$payload = $data['payload'];

if (isset($action) && isset($payload)) {

    if(function_exists('ajax_' . $action)) {
        $result = call_user_func('ajax_' . $action, $conn, $payload);
        if ($result != null) {
            echo json_encode($result);
        }
    } else {
        // Handle invalid action
        echo json_encode(array('status' => 'error', 'data' => 'Invalid action [ajax_' . $action . ']'));
    }

}


// Add your ajax functions here