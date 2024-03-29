<?php

function get_characters($conn, $owner = '', $tags = [], $team = '') {
    $sql = "SELECT * FROM characters ";

    if (!empty($tags)) {
        $sql .= "WHERE ";
        foreach ($tags as $tag) {
            $sql .= "tags LIKE '%$tag%' AND ";
        }
        $sql = rtrim($sql, ' AND ');
    }

    if ($team != '') {
        if (empty($tags)) {
            $sql .= "WHERE ";
        } else {
            $sql .= " AND ";
        }
        $sql .= "team = '$team' ";
    }

    if ($owner != '') {
        if (empty($tags) && $team == '') {
            $sql .= "WHERE ";
        } else {
            $sql .= " AND ";
        }
        $sql .= "owner = '$owner' ";
    }

    $sql .= ";";

    $result = $conn->query($sql);

    $character_ids = [];

    while ($row = $result->fetch_assoc()) {
        $character_ids[] = $row['id'];
    }

    return $character_ids;

}

function get_character($conn, $id) {
    $sql = "SELECT * FROM characters WHERE id = '$id';";

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    return $row;
}

function user_nickname($conn, $id) {
    $sql = "SELECT * FROM profiles WHERE discord_id = '$id';";

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    return $row['nickname'];
}

function new_object_id($type) {
    return uniqid($type . '_', false);
}

function get_user_scripts($conn, $user_id) {
    $sql = "SELECT * FROM scripts WHERE owner = '$user_id';";

    $result = $conn->query($sql);

    $scripts = [];

    while ($row = $result->fetch_assoc()) {
        $scripts[] = $row;
    }

    return $scripts;
}

function get_script($conn, $id) {
    $sql = "SELECT * FROM scripts WHERE id = '$id';";

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    //Build JSON
    $script = [];
    $meta = array(
        'id' => '_meta',
        'name' => $row['name'],
        'overview' => $row['overview'],
        'synopsis' => $row['synopsis'],
        'tags' => explode(',', $row['tags']),
        'difficulty' => $row['difficulty'],
        'type' => $row['type'],
        'author' => $row['author']
    );

    $script[] = $meta;

    $characters = json_decode($row['characters'], true);
    foreach ($characters as $character) {
        $script[] = $character;
    }

    return $script;
}

function get_script_catalogue($conn, $id) {
    $sql = "SELECT * FROM scripts WHERE id = '$id';";

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    return explode(',', $row['catalogue_characters']);

}

function unimport_character($conn, $script_id, $character_id) {
    $sql = "SELECT * FROM scripts WHERE id = '$script_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $catalogue_characters = $row['catalogue_characters'];
    } else {
        return array('status' => 'error', 'data' => 'Failed to get script');
    }

    $catalogue_characters = explode(",", $catalogue_characters);

    if (($key = array_search($character_id, $catalogue_characters)) !== false) {
        unset($catalogue_characters[$key]);
    }

    $catalogue_characters = implode(",", $catalogue_characters);

    $sql = "UPDATE scripts SET catalogue_characters = '$catalogue_characters' WHERE id = '$script_id';";
    $result = $conn->query($sql);

    return $result;
}

function save_script($conn, $id, $script, $user_id) {

    //Breakdown
    $name = $script[0]['name'];
    $overview = $script[0]['overview'];
    $synopsis = $script[0]['synopsis'];
    $thumbnail = $script[0]['thumbnail'];
    $tags = implode(',', $script[0]['tags']);
    $difficulty = $script[0]['difficulty'];
    $type = $script[0]['type'];
    $author = $script[0]['author'];
    $characters = json_encode(array_slice($script, 1));
    $owner = $user_id;

    //Format thumbnail
    //thumbnail is in format "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAREBUSEh..."
    //We only want the base64 part for the mediumblob column
    $thumbnail = explode(',', $thumbnail);
    $thumbnail = $thumbnail[1];

    //Check if script exists
    $sql = "SELECT * FROM scripts WHERE id = '$id';";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row != null) {
        //Get script owner
        $owner = $row['owner'];

        if ($owner != $user_id) {
            return null;
        }

        //Update existing script
        $sql = "UPDATE scripts SET ";
        $sql .= "name = '$name', ";
        $sql .= "overview = '$overview', ";
        $sql .= "synopsis = '$synopsis', ";
        $sql .= "thumbnail = '$thumbnail', ";
        $sql .= "tags = '$tags', ";
        $sql .= "difficulty = '$difficulty', ";
        $sql .= "type = '$type', ";
        $sql .= "characters = '$characters', ";
        $sql .= "author = '$author', ";
        $sql .= "owner = '$owner' ";
        $sql .= "WHERE id = '$id';";
    } else {
        //Create new script
        $sql = "INSERT INTO scripts (id, name, overview, synopsis, thumbnail, tags, difficulty, type, characters, author, owner) ";
        $sql .= "VALUES ('$id', '$name', '$overview', '$synopsis', '$thumbnail', '$tags', '$difficulty', '$type', '$characters', '$author', '$owner');";
    }

    $result = $conn->query($sql);

    return $result;
}

function save_character($conn, $id, $character, $user_id) {
    //Breakdown
    $name = $character['name'];
    $author = $character['author'];
    $team = $character['team'];
    $firstNightReminder = $character['firstNightReminder'];
    $otherNightReminder = $character['otherNightReminder'];
    $firstNight = $character['firstNight'];
    $otherNight = $character['otherNight'];
    $reminders = $character['reminders'];
    $remindersGlobal = $character['remindersGlobal'];
    $setup = $character['setup'];
    $flavor = $character['flavor'];
    $overview = $character['overview'];
    $howToRun = $character['howToRun'];
    $examples = $character['examples'];
    $tip = $character['tip'];
    $ability = $character['ability'];
    $tags = $character['tags'];
    $sao_category = $character['sao_category'];
    $origin_script = $character['origin_script'];

    $owner = $user_id;

    //Check if character exists
    $sql = "SELECT * FROM characters WHERE id = '$id';";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row != null) {
        //Get character owner
        $owner = $row['owner'];

        if ($owner != $user_id) {
            return null;
        }

        //Update existing character
        $sql = "UPDATE characters SET ";
        $sql .= "name = '$name', ";
        $sql .= "author = '$author', ";
        $sql .= "team = '$team', ";
        $sql .= "firstNightReminder = '$firstNightReminder', ";
        $sql .= "otherNightReminder = '$otherNightReminder', ";
        $sql .= "firstNight = '$firstNight', ";
        $sql .= "otherNight = '$otherNight', ";
        $sql .= "reminders = '$reminders', ";
        $sql .= "remindersGlobal = '$remindersGlobal', ";
        $sql .= "setup = '$setup', ";
        $sql .= "flavor = '$flavor', ";
        $sql .= "overview = '$overview', ";
        $sql .= "howToRun = '$howToRun', ";
        $sql .= "examples = '$examples', ";
        $sql .= "tip = '$tip', ";
        $sql .= "ability = '$ability', ";
        $sql .= "tags = '$tags', ";
        $sql .= "sao_category = '$sao_category', ";
        $sql .= "origin_script = '$origin_script', ";
        $sql .= "owner = '$owner' ";
        $sql .= "WHERE id = '$id';";
    } else {
        //Create new character
        $sql = "INSERT INTO characters (id, name, author, team, firstNightReminder, otherNightReminder, firstNight, otherNight, reminders, remindersGlobal, setup, flavor, overview, howToRun, examples, tip, ability, tags, sao_category, origin_script, owner) ";
        $sql .= "VALUES ('$id', '$name', '$author', '$team', '$firstNightReminder', '$otherNightReminder', '$firstNight', '$otherNight', '$reminders', '$remindersGlobal', '$setup', '$flavor', '$overview', '$howToRun', '$examples', '$tip', '$ability', '$tags', '$sao_category', '$origin_script', '$owner');";
    }

    $result = $conn->query($sql);

    return $result;
}

function sao_sort($conn, $characters) {
    //sao_category column in characters table contains numbers 1-6, order low to high, then by 'ability' colunn length
    $character_data = [];

    foreach ($characters as $character) {
        $character_data[$character] = get_character($conn, $character);
    }

    $sorted_characters = [];

    //sort by 'sao_category' and length of 'ability'
    usort($character_data, function($a, $b) {
        if ($a['sao_category'] == $b['sao_category']) {
            return strlen($a['ability']) - strlen($b['ability']);
        }
        return $a['sao_category'] - $b['sao_category'];
    });

    foreach ($character_data as $character) {
        $sorted_characters[] = $character['id'];
    }

    return $sorted_characters;
}

function generate_night_order($conn, $character_ids) {
    $firstNight = [];
    $otherNight = [];

    //select and order by first night if firstNight is not 0
    $sql = "SELECT * FROM characters WHERE id IN (";
    
    foreach ($character_ids as $character) {
        $sql .= "'$character',";
    }

    $sql = rtrim($sql, ',');
    $sql .= ") AND firstNight != 0 ORDER BY firstNight DESC;";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $firstNight[] = $row['id'];
    }

    //select and order by other nights if otherNight is not 0
    $sql = "SELECT * FROM characters WHERE id IN (";

    foreach ($character_ids as $character) {
        $sql .= "'$character',";
    }

    $sql = rtrim($sql, ',');
    $sql .= ") AND otherNight != 0 ORDER BY otherNight DESC;";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $otherNight[] = $row['id'];
    }

    return array('firstNight' => $firstNight, 'otherNight' => $otherNight);
}