<?php

require "functions_john.php";
$pdo = new DatabaseActions();

make_header("Users");


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
 * Vypsání uživatelů
 * */
$users = $pdo->select_all_users();
echo "<h1><u>" . "User list" . "</u></h1>";
echo "<p>";
while ($value = $users->fetch())
{
    echo "<a href='profile_john.php?name=" . $value["nick"] .  "'>" . $value["nick"] . "</a>" . "<br>";
}
echo "</p>";


make_footer();
?>