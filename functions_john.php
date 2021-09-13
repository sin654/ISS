<?php

// připojení databáze
require "database.php";
$pdo = new DatabaseActions();

// zahájení session
session_start();


/**
 * Vytvoří HTML hlavičku
 * @param $title - titulek stránky v prohlížeči (string)
 */
function make_header($title)
{
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title;?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles_john.css">
</head>
<body>
<?php
}

/**
 * Ukončí HTML dokument (do páru s make_header() )
 */
function make_footer()
{
?>
<!--   <footer>&copy; XDOLEZ81 2020</footer> -->
</body>
</html>
<?php
}

/**
 * Navbar pro nepřihlášeného uživatele
 */
function make_navbar_signin()
{
?>
<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">HOME</a>
        </div>
        <ul class="nav navbar-nav">
            <!--<li class="active"><a href="#">Home</a></li>-->
            <li><a href="groups_john.php">Groups</a></li>
            <li><a href="users_john.php">Users</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="login_john.php"><span class="glyphicon glyphicon-log-in"></span> Log IN</a></li>
            <li><a href="register_john.php"><span class="glyphicon glyphicon-user"></span> Register</a></li>
        </ul>
    </div>
</nav>
<?php
}

/**
 * Navbar pro přihlášeného uživatele
 */
function make_navbar_signout()
{
?>
<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">HOME</a>
        </div>
        <ul class="nav navbar-nav">
            <!--<li class="active"><a href="#">Home</a></li>-->
            <li><a href="groups_john.php">Groups</a></li>
            <li><a href="users_john.php">Users</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="profile_john.php?name=<?php echo $_SESSION["user"]; ?>"><span class="glyphicon glyphicon-user"></span><?php echo " ".$_SESSION["user"]; ?></a></li>
            <li><a href="logout_john.php"><span class="glyphicon glyphicon-log-out"></span> Log OUT</a></li>
        </ul>
    </div>
</nav>
<?php
}

/**
 * Vypíše obsah profilu uživatele
 * @param $profile - jeden řádek z uzivatel, odpovídá $profile_user
 */
function profile_content($profile)
{
    echo "<p>";
    echo "<b>nick:</b> " . $profile["nick"] . "<br>";
    echo "<b>description:</b> " . $profile["popis_uzivatel"] . "<br>";
    echo "</p>";
    global $pdo;
    $groups = $pdo->select_user_groups(array("id_uzivatel" => $profile["id_uzivatel"]));
    echo "<h3><u>Member of groups</u></h3>";
    echo "<p>";
    while ($value = $groups->fetch())
    {
        echo "<a href='group_john.php?id_skupina=". $value["id_skupina"] . "'>" . $value["nazev_skupina"] . "</a>" . "<br>";
    }
    echo "</p>";
}

/**
 * Vypíše obsah profilu uzivatele pro jeho majitele/admina modify věci, vytváření skupin atd.
 * @param $profile - jeden řádek z uzivatel, odpovídá $profile_user
 */
function profile_content_owner($profile)
{
    echo "<p>";
    echo "<b>nick:</b> " . $profile["nick"] . "<br>";
    echo "<b>description:</b> " . $profile["popis_uzivatel"] . "<br>";
    echo "<b>profile visibility:</b> " . $profile["prava_uzivatel"] . " (3-everyone, 2-registered, 1-group, 0-no one)" . "<br>";
    echo "</p>";

    // modifikace uživatel
    ?>
    <br><br>
    <h3>Modify user</h3>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        New description: <input type="text" name="description">
        <input type="submit">
    </form>
    <br>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        New password: <input type="password" name="password">
        <input type="submit">
    </form>
    <br>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        New profile visibility:
        <input type="radio" name="permissions" value="3">Everyone
        <input type="radio" name="permissions" value="2">Registered
        <input type="radio" name="permissions" value="1">Group
        <input type="radio" name="permissions" value="none">None
        <input type="submit">
    </form>
    <br><br>
    <h3>Create new Group</h3>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        Group name: <input type="text" name="group_name">
        <input type="submit">
    </form>
    <br><br>
    <?php
    global $pdo;
    $groups = $pdo->select_user_groups(array("id_uzivatel" => $profile["id_uzivatel"]));
    echo "<h3>Member of groups</h3>";
    echo "<p>";
    while ($value = $groups->fetch())
    {
        echo "<a href='group_john.php?id_skupina=". $value["id_skupina"] . "'>" . $value["nazev_skupina"] . "</a>" . "<br>";
    }
    echo "</p>";
}

/**
 * Vytvoří obsah skupiny pro neregistrovaného uživatele
 * @param $group - jeden řádek z skupina, odpovídá $profile_group
 */
function group_content($group)
{
    global $pdo;
    $manager = $pdo->select_user_by_id(array("id_uzivatel" => $group["id_spravce"]));

    echo "<h1>" . $group["nazev_skupina"] . "</h1>";
    echo "<p>";
    echo "<b>Group manager:</b> " . $manager["nick"] . "<br>";
    echo "<b>description:</b> " . $group["popis_skupina"] . "<br>";
    echo "<b>group visibility:</b> " . $group["prava_skupina"] . " (3-everyone, 2-registered, 1-group, 0-no one)" . "<br>";
    echo "</p>";

    echo "<h3><u>Threads</u></h3>";
    $threads = $pdo->select_all_threads_in_group(array("id_skupina" => $group["id_skupina"]));
    echo "<p>";
    while ($value = $threads->fetch())
    {
        echo "<a href='thread_john.php?id_vlakno=". $value["id_vlakno"] . "'>" . $value["nazev_vlakno"] . "</a>" . "<br>";
    }
    echo "</p>";
}

/**
 * Vytvoří obsah skupiny pro registrovaného uživatele, který není členem skupiny
 * @param $group - jeden řádek z skupina, odpovídá $profile_group
 */
function group_content_nonmember($group)
{
    global $pdo;
    $manager = $pdo->select_user_by_id(array("id_uzivatel" => $group["id_spravce"]));

    echo "<h1>" . $group["nazev_skupina"] . "</h1>";
    echo "<p>";
    echo "<b>Group manager:</b> " . $manager["nick"] . "<br>";
    echo "<b>description:</b> " . $group["popis_skupina"] . "<br>";
    echo "</p>";
    // žádost o členství
    ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="hidden" name="request_membership" value="please">
        <input type="submit" value="Request Membership">
    </form>
    <?php

    echo "<h3><u>Threads</u></h3>";
    $threads = $pdo->select_all_threads_in_group(array("id_skupina" => $group["id_skupina"]));
    echo "<p>";
    while ($value = $threads->fetch())
    {
        echo "<a href='thread_john.php?id_vlakno=". $value["id_vlakno"] . "'>" . $value["nazev_vlakno"] . "</a>" . "<br>";
    }
    echo "</p>";
}

/**
 * Vytvoří obsah skupiny pro člena
 * @param $group - jeden řádek z skupina, odpovídá $profile_group
 */
function group_content_member($group)
{
    global $pdo;
    $manager = $pdo->select_user_by_id(array("id_uzivatel" => $group["id_spravce"]));

    echo "<h1>" . $group["nazev_skupina"] . "</h1>";
    echo "<p>";
    echo "<b>Group manager:</b> " . $manager["nick"] . "<br>";
    echo "<b>description:</b> " . $group["popis_skupina"] . "<br>";
    echo "</p>";

    echo "<h3><u>Threads</u></h3>";
    global $pdo;
    $threads = $pdo->select_all_threads_in_group(array("id_skupina" => $group["id_skupina"]));
    echo "<p>";
    while ($value = $threads->fetch())
    {
        echo "<a href='thread_john.php?id_vlakno=". $value["id_vlakno"] . "'>" . $value["nazev_vlakno"] . "</a>" . "<br>";
    }
    echo "</p>";
}

/**
 * Vytvoří obsah skupiny pro Moderátora
 * @param $group - jeden řádek z skupina, odpovídá $profile_group
 */
function group_content_mod($group)
{
    global $pdo;
    $manager = $pdo->select_user_by_id(array("id_uzivatel" => $group["id_spravce"]));

    echo "<h1>" . $group["nazev_skupina"] . "</h1>";
    echo "<p>";
    echo "<b>Group manager:</b> " . $manager["nick"] . "<br>";
    echo "<b>description:</b> " . $group["popis_skupina"] . "<br>";
    echo "<b>group visibility:</b> " . $group["prava_skupina"] . " (3-everyone, 2-registered, 1-group, 0-no one)" . "<br>";
    echo "</p>";

    echo "<h3><u>Threads</u></h3>";
    global $pdo;
    $threads = $pdo->select_all_threads_in_group(array("id_skupina" => $group["id_skupina"]));
    echo "<p>";
    while ($value = $threads->fetch())
    {
        echo "<a href='thread_john.php?id_vlakno=". $value["id_vlakno"] . "'>" . $value["nazev_vlakno"] . "</a>" . "<br>";
    }
    echo "</p>";
}

/**
 * Vytvoří obsah skupiny pro Správce skupiny
 * @param $group - jeden řádek z skupina, odpovídá $profile_group
 */
function group_content_manager($group)
{
    global $pdo;
    $manager = $pdo->select_user_by_id(array("id_uzivatel" => $group["id_spravce"]));

    echo "<h1>" . $group["nazev_skupina"] . "</h1>";
    echo "<p>";
    echo "<b>Group manager:</b> " . $manager["nick"] . "<br>";
    echo "<b>description:</b> " . $group["popis_skupina"] . "<br>";
    echo "<b>group visibility:</b> " . $group["prava_skupina"] . " (3-everyone, 2-registered, 1-group, 0-no one)" . "<br>";
    echo "</p>";

    echo "<h3><u>Threads</u></h3>";
    $threads = $pdo->select_all_threads_in_group(array("id_skupina" => $group["id_skupina"]));
    echo "<p>";
    while ($value = $threads->fetch())
    {
        echo "<a href='thread_john.php?id_vlakno=". $value["id_vlakno"] . "'>" . $value["nazev_vlakno"] . "</a>" . "<br>";
    }
    echo "</p>";
    // modifikace uživatel
    ?>
    <br><br>
    <h3>Modify group</h3>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        New description: <input type="text" name="description">
        <input type="submit">
    </form>
    <br>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        New group visibility:
        <input type="radio" name="permissions" value="3">Everyone
        <input type="radio" name="permissions" value="2">Registered
        <input type="radio" name="permissions" value="1">Members
        <input type="submit">
    </form>
    <br><br>
    <h3>Create new Thread</h3>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        Thread name: <input type="text" name="thread_name">
        <input type="submit">
    </form>
    <?php
}

function thread_content_member($thread)
{
    thread_content($thread);

    // formulář pro odeslání nové zprávy
    ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="text" name="text" autofocus placeholder="Type message...">
        <br>
        <input type="submit">
    </form>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="submit" value="refresh">
    </form>

    <?php
}

function thread_content($thread)
{
    global $pdo;
    $group = $pdo->select_group(array("id_skupina" => $thread["id_skupina"]));

    echo "<h1>" . "Thread: " . $thread["nazev_vlakno"] . "</h1>";
    echo  "<b>". "In group: </b>" . $group["nazev_skupina"] . "<br>";
    echo "<br><br>";

    echo "<h2><u>Contribution</u></h2>";
    global $pdo;
    $contribution = $pdo->get_all_contributions(array("id_vlakno" => $thread["id_vlakno"]));
    echo "<p>";
    while ($value = $contribution->fetch()) // vypsání všech příspěvků
    {
        $author = $pdo->select_user_by_id(array("id_uzivatel" => $value["id_uzivatel"]));
        if (!empty($author["nick"]) and $author["nick"] !== NULL)
            echo "<b>" . $author["nick"] . ": </b>" . $value["text"] . "<br>";
        else
            echo "Anonymous: " . $value["text"] . "<br>";
    }
    echo "</p>";
}


function thread_content_mod($thread)
{
    global $pdo;
    $group = $pdo->select_group(array("id_skupina" => $thread["id_skupina"]));

    echo "<h1>" . "Thread: " . $thread["nazev_vlakno"] . "</h1>";
    echo  "<b>". "In group: </b>" . $group["nazev_skupina"] . "<br>";
    echo "<br><br>";

    echo "<h2><u>Contribution</u></h2>";
    global $pdo;
    $contribution = $pdo->get_all_contributions(array("id_vlakno" => $thread["id_vlakno"]));
    echo "<p>";
    while ($value = $contribution->fetch()) // vypsání všech příspěvků
    {
        // tady musím doplnit, aby to byl <a href="" odkaz na modifikaci/smazání příspěvku
        $author = $pdo->select_user_by_id(array("id_uzivatel" => $value["id_uzivatel"]));
        if (!empty($author["nick"]) and $author["nick"] !== NULL)
            echo "<b>" . $author["nick"] . ": </b>" . $value["text"] . "<br>";
        else
            echo "Anonymous: " . $value["text"] . "<br>";
    }
    echo "</p>";

    // formulář pro odeslání nové zprávy
    ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="text" name="text" autofocus placeholder="Type message...">
        <br>
        <input type="submit">
    </form>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="submit" value="refresh">
    </form>


    <?php
}


/**
 * Přesměrování
 * @param $dest
 */
function redirect($dest)
{
    $script = $_SERVER["PHP_SELF"];
    if (strpos($dest,'/') === 0) {
        $path = $dest;
    } else {
        $path = substr($script, 0, strrpos($script, '/')) . "/$dest";
    }
    $name = $_SERVER["SERVER_NAME"];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: http://$name$path");
}



?>