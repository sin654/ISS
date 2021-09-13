<?php

require "functions_john.php";
$pdo = new DatabaseActions();

make_header("Groups");


/*
 * Zjištění jestli je někdo přihlášený
 * */
if (isset($_SESSION["user"]))   // již je někdo přihlášený $_SESSION["user"] je jeho nick
{
    make_navbar_signout();
    $log_user = $pdo->select_user_by_nick(array("nick" => $_SESSION["user"]));
}
else
{
    make_navbar_signin();
    $log_user = NULL;
}


/*
 * Vypsání skupin
 * */
$groups = $pdo->select_all_groups();
echo "<h1><u>" . "Group list" . "</u></h1>";
echo "<p>";
while ($value = $groups->fetch())
{
    echo "<a href='group_john.php?id_skupina=". $value["id_skupina"] . "'>" . $value["nazev_skupina"] . "</a>" . "<br>";
}
echo "</p>";


make_footer();
?>