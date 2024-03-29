<?php
function get_character($conn, $id) {
    $sql = "SELECT * FROM characters WHERE id = '$id';";

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    return $row;
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