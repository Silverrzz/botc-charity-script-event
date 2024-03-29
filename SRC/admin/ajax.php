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

function ajax_get_character($conn, $payload) {

    require_once('functions.php');

    $character = get_character($conn, $payload['id']);
    
    if ($character != null) {
        return array('status' => 'success', 'data' => $character);
    } else {
        return array('status' => 'error', 'data' => 'Character not found (ID: ' . $payload['id'] . ')');
    }
}

function ajax_save_script($conn, $payload) {

    require_once('functions.php');

    if (!isset($_SESSION['user_id'])) {
        return array('status' => 'error', 'data' => 'Not logged in');
    }

    //does user have 20 scripts?
    if (count(get_user_scripts($conn, $_SESSION['user_id'])) >= 20) {
        return array('status' => 'error', 'data' => 'You have reached the maximum number of scripts (20)');
    }

    if (!isset($payload['id'])) {
        return array('status' => 'error', 'data' => 'Undefined script ID');
    }

    if (!isset($payload['script'])) {
        return array('status' => 'error', 'data' => 'Undefined script');
    }

    if ($payload['id'] == 'new') {
        $id = new_object_id("script");
    } else {
        $id = $payload['id'];
    }

    $script = $payload['script'];

    $result = save_script($conn, $id, $script, $_SESSION['user_id']);
    
    //if mysql error
    if ($result != null) {
        return array('status' => 'success', 'data' => $id);
    } else {
        return array('status' => 'error', 'data' => 'Failed to save script');
    }
}

function ajax_save_character($conn, $payload) {

    require_once('functions.php');

    if (!isset($_SESSION['user_id'])) {
        return array('status' => 'error', 'data' => 'Not logged in');
    }

    if (!isset($payload['id'])) {
        return array('status' => 'error', 'data' => 'Undefined character ID');
    }

    if (!isset($payload['character'])) {
        return array('status' => 'error', 'data' => 'Undefined character');
    }

    if ($payload['id'] == 'new') {
        $id = new_object_id("character");
    } else {
        $id = $payload['id'];
    }

    $character = $payload['character'];

    $result = save_character($conn, $id, $character, $_SESSION['user_id']);
    
    //if mysql error
    if ($result != null) {
        return array('status' => 'success', 'data' => $id);
    } else {
        return array('status' => 'error', 'data' => 'Failed to save character');
    }
}

function ajax_get_script($conn, $payload) {

    require_once('functions.php');

    $id = $payload['id'];
    $script = get_script($conn, $id);
    
    if ($script != null) {
        return array('status' => 'success', 'data' => $script);
    } else {
        return array('status' => 'error', 'data' => 'Failed to get script');
    }
}

function ajax_sao_sort($conn, $payload) {

    require_once('functions.php');

    $result = sao_sort($conn, $payload['characters']);
    
    if ($result != null) {
        return array('status' => 'success', 'data' => $result);
    } else {
        return array('status' => 'error', 'data' => 'Failed to sort');
    }
}

function ajax_generate_night_order($conn, $payload) {
    
    require_once('functions.php');

    $result = generate_night_order($conn, $payload['characters']);
    
    if ($result != null) {
        return array('status' => 'success', 'data' => $result);
    } else {
        return array('status' => 'error', 'data' => 'Failed to generate night order');
    }
}

function ajax_import_character($conn, $payload) {
    
    require_once('functions.php');
    
    $character_id = $payload['id'];
    $script_id = $payload['script'];

    $sql = "SELECT * FROM scripts WHERE id = '$script_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $catalogue_characters = $row['catalogue_characters'];
    } else {
        return array('status' => 'error', 'data' => 'Failed to get script');
    }

    $catalogue_characters = explode(",", $catalogue_characters);
    $catalogue_characters[] = $character_id;
    $catalogue_characters = array_unique($catalogue_characters);
    $catalogue_characters = implode(",", $catalogue_characters);
    $catalogue_characters = trim($catalogue_characters, ",");

    $sql = "UPDATE scripts SET catalogue_characters = '$catalogue_characters' WHERE id = '$script_id'";

    $result = $conn->query($sql);
    if ($result === TRUE) {
        return array('status' => 'success', 'data' => 'Character imported');
    } else {
        return array('status' => 'error', 'data' => 'Failed to import character');
    }
}
function ajax_generate_catalogue_characters($conn, $payload) {

    require_once('functions.php');

    $teams = ['townsfolk', 'outsider', 'minion', 'demon', 'traveler', 'fabled'];

    $script_id = $payload['id'];
    $catalogue_characters = get_script_catalogue($conn, $script_id);

    //loop, if character doesn't exist, remove from catalogue
    foreach ($catalogue_characters as $character_id) {
        $char_data = get_character($conn, $character_id);
        if ($char_data == null) {
            $key = array_search($character_id, $catalogue_characters);
            unimport_character($conn, $script_id, $character_id);
            unset($catalogue_characters[$key]);
        }
    }

    $script_characters = $payload['characters'];
    $script_characters = array_unique($script_characters);

    //if any script characters are not in the catalogue, and are not official characters, import them
    foreach ($script_characters as $character_id) {
        //if character is an array, not string, set character_id to id
        if (is_array($character_id)) {
            $character_id = $character_id['id'];
        }
        if (!in_array($character_id, $catalogue_characters)) {
            $char_data = get_character($conn, $character_id);
            if ($char_data['owner'] != 0) {
                echo "importing character " . $character_id;
                ajax_import_character($conn, array('id' => $character_id, 'script' => $script_id));
                $catalogue_characters[] = $character_id;
            }
        }
    }

    $html = "";

    foreach ($teams as $team) {
        $characters = get_characters($conn, "0", ['official'], $team);
        $html .= '<div class="team show" data-team="' . $team . '">';
        $html .= '<div class="team-name">' . ucfirst($team) . '</div>';
        foreach ($characters as $character_id) {
            $char_data = get_character($conn, $character_id);
            $html .= '<div class="character show" data-id="' . $character_id . '" data-team="' . $team . '" data-category="official" data-name="' . $char_data['name'] . '" data-ability="' . $char_data['ability'] . '">';
            $html .= '<image src="/image/character/' . $character_id . '" class="character-image">';
            $html .= '<div class="character-name">' . $char_data['name'] . '</div>';
            $html .= '</div>';
        }

        foreach ($catalogue_characters as $character_id) {
            $char_data = get_character($conn, $character_id);
            if ($char_data['team'] != $team) {
                continue;
            }
            
            $html .= '<div class="character show" data-id="' . $character_id . '" data-team="' . $team . '" data-category="catalogue" data-name="' . $char_data['name'] . '" data-ability="' . $char_data['ability'] . '">';
            $html .= '<image src="/image/character/' . $character_id . '" class="character-image">';
            $html .= '<div class="character-name">' . $char_data['name'] . '</div>';
            if ($char_data['origin_script'] == $script_id) {
                $html .= '<div class="edit-character">Edit</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
    }

    return array('status' => 'success', 'data' => $html);
}