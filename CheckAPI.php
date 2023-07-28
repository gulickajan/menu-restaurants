<?php
require_once "MyPDO.php";
require_once "Config.php";
require_once "NewMenu.php";

$config = new Config();
$myPdo = new MyPDO("mysql:host=localhost; dbname=jedalny_listok", $config->getUsername(), $config->getPassword());

$freefoodURL = "http://www.freefood.sk/menu/#fayn-food";
$kolibaURL = "https://riverpark.bevanda.sk/";
$klubovnaURL = "https://www.nasaklubovna.sk/klubovne/karloveska/menu/denne-menu/";

function getPageContent($db, $url, $name) {
    $ch = curl_init();

    // Konfiguracia cURL: zadam stranku, ktoru chcem parsovat a navratovy typ -> 1=string.
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Vykonanie cURL dopytu.
    $output = curl_exec($ch);

    // Slusne ukoncim a uvolnim cURL.
    curl_close($ch);

    // Vlozim obsah stranky do databazy.
    $sql = "INSERT INTO sites (name, html) VALUES (:name, :html)";
    $stmt = $db->prepare($sql);

    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->bindParam(":html", $output, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "<p class='text-bg-danger align-text-bottom'>Stránka je uložená</p>";
    }

    unset($stmt);
}

function getMenuFromDB($db, $name) {
    $page_content = "";
    $sql = "SELECT html FROM sites WHERE name = :name LIMIT 1";

    $stmt = $db->prepare($sql);

    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        $page_content = $row["html"];
    } else {
        echo "Nenachadza sa v tabulke alebo je duplicitne.";
    }

    return $page_content;

}
function parseMenuToDBFreeFood($db) {
    $output = getMenuFromDB($db, "free-food");

    $dom = new DOMDocument();

    $dom->loadHTML($output);
    $xpath = new DOMXPath($dom);

    $menu_lists = $xpath->query('//ul[contains(@class, "daily-offer")]');

    $fayn_food = $menu_lists[1];
    $name = "Fayn-food";

    foreach ($fayn_food->childNodes as $day) {
        if ($day->nodeType === XML_ELEMENT_NODE) {
            $datum =  $day->firstElementChild->textContent;

            foreach ($day->lastElementChild->childNodes as $ponuka) {
                $typ = $ponuka->firstElementChild;
                $cena = $ponuka->lastElementChild;

                $ponuka->removeChild($typ); // Vymazanie por. cisla
                $ponuka->removeChild($cena); // Vymazanie ceny

                $newMenu = new NewMenu($db);
                $newMenu->setMenu($ponuka->textContent);
                $newMenu->setPrice($cena->textContent);
                $newMenu->setDay($datum);
                $newMenu->setPlace($name);
                $newMenu->save();
            }

        }
    }
}

function parseMenuToDBKlubovna($db) {
    $output = getMenuFromDB($db, "klubovna");

    $dom = new DOMDocument();
    $dom->loadHTML($output);
    $xpath = new DOMXPath($dom);

    $days = $xpath->query('//div[contains(@class, "daily-day")]');
    $soaps = $xpath->query('//div[contains(@class, "daily-soup")]');
    $menuA = $xpath->query('//div[contains(@class, "daily-menua")]');
    $menuB = $xpath->query('//div[contains(@class, "daily-menub")]');
    $price = $xpath->query('//div[contains(@class, "daily-price")]');
    $name = "Klubovňa";
    $soapPrice = "1,69€";
    $priceCounter = 0;
    $menuCCounter = 0;
    for ($i = 0; $i < 5; $i++) {
        $dayy = $days[$i]->textContent;
        $pricea = $price[$priceCounter]->textContent;
        $soap = $soaps[$i]->textContent;

        $newMenu = new NewMenu($db);
        $newMenu->setDay($dayy);
        $newMenu->setMenu($soap);
        $newMenu->setPlace($name);
        $newMenu->setPrice($soapPrice);
        $newMenu->save();

        $menuAa = $menuA[$i]->lastElementChild->textContent;
        $newMenu = new NewMenu($db);
        $newMenu->setDay($dayy);
        $newMenu->setMenu($menuAa);
        $newMenu->setPlace($name);
        $newMenu->setPrice($pricea);
        $newMenu->save();

        $menuBb = $menuB[$menuCCounter]->lastElementChild->textContent;
        $priceb = $price[$priceCounter+1]->textContent;
        $newMenu = new NewMenu($db);
        $newMenu->setDay($dayy);
        $newMenu->setMenu($menuBb);
        $newMenu->setPlace($name);
        $newMenu->setPrice($priceb);
        $newMenu->save();

        $menuCc = $menuB[$menuCCounter + 1]->lastElementChild->textContent;
        $pricec = $price[$priceCounter+2]->textContent;
        $newMenu = new NewMenu($db);
        $newMenu->setDay($dayy);
        $newMenu->setMenu($menuCc);
        $newMenu->setPlace($name);
        $newMenu->setPrice($pricec);
        $newMenu->save();

        $priceCounter += 3;
        $menuCCounter += 2;
    }


}

function parseMenuToDBBevanda($db)
{
    $output = getMenuFromDB($db, "bevanda");
    $dom = new DOMDocument();

    $dom->loadHTML($output);
    $xpath = new DOMXPath($dom);

    $days = $xpath->query('//div[@class = "day"][1]');
    $dayMenu = $xpath->query('//div[contains(@class, "dayly-menu-list")]');
    $regex = '/([\d,]+[lg]+)\s+(\S.*)\s+(\d+,\d{2}\s+€)/u';
    $name = "Bevanda River";
    for ($i = 1; $i < 6; $i++) {
        preg_match_all($regex, $dayMenu[$i]->textContent, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $menu = trim($match[2]);
            $price = trim($match[3]);
            $newMenu = new NewMenu($db);
            $newMenu->setPlace($name);
            $newMenu->setDay($days[$i]->textContent);
            $newMenu->setMenu($menu);
            $newMenu->setPrice($price);
            $newMenu->save();
        }
    }
}

if (isset($_POST["stiahni"])) {
    getPageContent($myPdo, $klubovnaURL, "klubovna");
    getPageContent($myPdo, $kolibaURL, "bevanda");
    getPageContent($myPdo, $freefoodURL, "free-food");
}



if (isset($_POST["vymaz"])) {
    $myPdo->run("DELETE FROM sites");
    $myPdo->run("DELETE FROM parsed_data");
}

if (isset($_POST["rozparsuj"])) {
    parseMenuToDBFreeFood($myPdo);
    parseMenuToDBBevanda($myPdo);
    parseMenuToDBKlubovna($myPdo);
}

?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CheckAPI</title>
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="styles.css">
</head>
<header>
    <h1 class="h1 text-center bg-dark-subtle">Overenie API dokumentácie</h1>
</header>
<body>
<div class="position-absolute top-50 start-50 translate-middle btn-group">
    <form action="CheckAPI.php" method="post">
        <button class="btn btn-light" type="submit" name="stiahni">Stiahni</button>
    </form>
    <form action="CheckAPI.php" method="post">
        <button class="btn btn-light" type="submit" name="rozparsuj">Rozparsuj</button>
    </form>
    <form action="CheckAPI.php" method="post">
        <button class="btn btn-danger" type="submit" name="vymaz">Vymaž</button>
    </form>
</div>
<div class="bg-dark-subtle position-absolute start-0 bottom-0">
    <a href="index.php" class="link-primary">späť</a>
</div>
</body>
</html>
