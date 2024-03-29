<?php

include 'database.php';

$url = $_SERVER['REQUEST_URI'];

//domain/image/<type>/<id>
$url = preg_replace('/(\/+)/', '/', $url);
$explode_url = explode('/', $url);
$type = $explode_url[2];
$id = $explode_url[3];

//remove file extension and query string
$id = explode('.', $id)[0];
$id = explode('?', $id)[0];

$sql = "SELECT * FROM images WHERE id = '$id';";
mysqli_query($conn, $sql);
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    header("Content-type: image/jpg");
    
    $image_data = $row['image'];

    echo hex2bin($image_data);
} else {
    echo "0 results";
}

$conn->close();