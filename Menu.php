<?php

require_once "MyPDO.php";
require_once "Config.php";

$config = new Config();
$myPdo = new MyPDO("mysql:host=localhost; dbname=jedalny_listok", $config->getUsername(), $config->getPassword());
$day = "";
if (isset($_POST["pondelok"])) {
    $day = "pondelok";
}
if (isset($_POST["utorok"])) {
    $day = "utorok";
}
if (isset($_POST["streda"])) {
    $day = "streda";
}
if (isset($_POST["stvrtok"])) {
    $day = "štvrtok";
}
if (isset($_POST["piatok"])) {
    $day = "piatok";
}

if (isset($_POST["pondelok"]) || isset($_POST["utorok"]) || isset($_POST["streda"]) || isset($_POST["stvrtok"]) || isset($_POST["piatok"])) {
    try {
        $stmt1 = $myPdo->query("SELECT * FROM parsed_data WHERE day LIKE '%$day%'");
        $result1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Menu</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="bootstrap.css">
</head>
<header>
    <h1 class="h1 text-center bg-dark-subtle">Jedálne Lístky</h1>
</header>
<body>
<div class="btn-group position-absolute start-0 top-0">
    <form action="Menu.php" method="post">
        <button class="btn btn-light" type="submit" value="pondelok" name="pondelok">Pondelok</button>
    </form>
    <form action="Menu.php" method="post">
        <button class="btn btn-light" type="submit" value="utorok" name="utorok">Utorok</button>
    </form>
    <form action="Menu.php" method="post">
        <button class="btn btn-light" type="submit" value="streda" name="streda">Streda</button>
    </form>
    <form action="Menu.php" method="post">
        <button class="btn btn-light" type="submit" value="stvrtok" name="stvrtok">Štvrtok</button>
    </form>
    <form action="Menu.php" method="post">
        <button class="btn btn-light" type="submit" value="piatok" name="piatok">Piatok</button>
    </form>
</div>

<table class="table table-secondary table-hover sortable">
    <thead>
    <tr>
        <th scope="col">Deň</th>
        <th scope="col">Menu</th>
        <th scope="col">Cena</th>
        <th scope="col">Miesto</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($result1 as $item)
    {
        echo "<tr>";
        echo "<td>".$item["day"]."</td>";
        echo "<td>".$item["menu"]."</td>";
        echo "<td>".$item["price"]."</td>";
        echo "<td>".$item["place"]."</td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table>
<div class="bg-dark-subtle position-absolute end-0 top-0">
    <a href="index.php" class="link-primary">späť</a>
</div>
</body>
</html>
