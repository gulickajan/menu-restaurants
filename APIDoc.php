<?php

require_once "MyPDO.php";
require_once "Config.php";

$config = new Config();
$myPdo = new MyPDO("mysql:host=localhost; dbname=jedalny_listok", $config->getUsername(), $config->getPassword());
?>
<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="styles.css">
    <title>APIDoc</title>
</head>
<body>

<a href="index.php" class="link-primary position-absolute align-bottom">späť</a>

</body>
</html>
