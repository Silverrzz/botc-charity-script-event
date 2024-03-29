<?php

$request_uri = $_SERVER['REQUEST_URI'];
$explode_uri = explode('/', $request_uri);
$page = $explode_uri[1];

if ($page == '') {
    $page = 'home';
}

session_start();

include('admin/admin.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Script Event | <?php echo ucfirst($page); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>var exports = {};</script>
    <script src="/assets/js/functions.js"></script>
    <script src="/assets/js/ajax.js"></script>
    <script src="/assets/js/modals.js"></script>
    <script src="/assets/js/notification.js"></script>
    <script src="/assets/js/loading_screen.js"></script>
</head>
<body style="display: none;">

<div id="notification-container"></div>

<div id="loading-screen">
    <img src="https://media4.giphy.com/media/kUTME7ABmhYg5J3psM/giphy.gif?cid=ecf05e47dpfgg5876pzkw5t171vb10npu8ftcm8nortvjpb1&ep=v1_gifs_search&rid=giphy.gif&ct=g" alt="loading animation" class="gif">
    <div class="message"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.body.style.display = 'block';
    });
</script>

<?php 
//if page file exists, include it
if (file_exists('pages/' . $page . '.php')) {
    include 'pages/' . $page . '.php';
} else {
    echo 'Page not found';
}
?>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
</body>
</html>