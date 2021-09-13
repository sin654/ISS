<?php

require "functions_john.php";
$pdo = new DatabaseActions();

make_header("Profile");


/*
 * Zjištění jaký profil zobrazujeme a jestli je někdo přihlášený
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
 * Načtení profilu uživatele, který se má zobrazit (požadavek typu GET)
 * */
if (isset($_GET["name"]))
{
    $_SESSION["watched_user_name"] = $_GET["name"];
    $profile_user = $pdo->select_user_by_nick(array("nick" => $_GET["name"]));
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST")  // post požadavek
{
    // přidání příspěvku do databáze
    // TODO tady budou modifikace učtu/přidávání skupin atd.
    if (isset($_POST["description"]))
    {
        // změna popisu uživatele
        if (!empty($_POST["description"]))
            $pdo->modify_user_description(array("id_uzivatel" => $log_user["id_uzivatel"], "popis_uzivatel" => $_POST["description"]));
        else
            $pdo->modify_user_description(array("id_uzivatel" => $log_user["id_uzivatel"], "popis_uzivatel" => ""));
    }
    if (isset($_POST["password"]))
    {
        // změna hesla uživatele
        if (!empty($_POST["password"]))
            $pdo->modify_user_password(array("id_uzivatel" => $log_user["id_uzivatel"], "heslo" => $_POST["password"]));
    }
    if (isset($_POST["permissions"]))
    {
        // změna práv pro zobrazení profilu uživatele
        if (!empty($_POST["permissions"]) and $_POST["permissions"] != "none")
            $pdo->modify_user_permissions(array("id_uzivatel" => $log_user["id_uzivatel"], "prava_uzivatel" => (int)$_POST["permissions"]));
        elseif(!empty($_POST["permissions"]))
            $pdo->modify_user_permissions(array("id_uzivatel" => $log_user["id_uzivatel"], "prava_uzivatel" => 0));

    }
    if (isset($_POST["group_name"]))
    {
        // vytvoření nové skupiny
        if (!empty($_POST["group_name"]))
            $pdo->add_group(array("id_spravce" => $log_user["id_uzivatel"], "nazev_skupina" => $_POST["group_name"]));

    }
    $profile_user = $pdo->select_user_by_nick(array("nick" => $_SESSION["watched_user_name"]));
}
else    // může to sem dojít :) při refreshi stránky
{
    $profile_user = $pdo->select_user_by_nick(array("nick" => $_SESSION["watched_user_name"]));
}








/*
 * Zobrazení obsahu podle přáv na prohlížení účtu
 * */
if ($log_user === NULL) // je to neregistrovaný uživatel
{
    if ($profile_user["prava_uzivatel"] == 3) // profil je viditelný pro všechny
    {
        echo "<h1><u>Browsing as unregistered user</u></h1>";
        profile_content($profile_user);
    }
    else // nevidí nic
    {
        echo "<h1>You dont have permissions to view this profile</h1>";
    }
}
elseif ($pdo->is_user_administrator(array("nick" => $log_user["nick"])))  // přihlášený uživatel je administrátor
{
    echo "<h1><u>Browsing as admin</u></h1>";
    profile_content_owner($profile_user);
}
elseif ($profile_user["nick"] == $log_user["nick"]) // majitel účtu
{
    echo "<h1><u>Browsing as profile owner</u></h1>";
    profile_content_owner($profile_user);
}
elseif ($pdo->does_users_share_group(array("id_uzivatel1" => $profile_user["id_uzivatel"], "id_uzivatel2" => $log_user["id_uzivatel"])))   // přihlášený uživatel sdílí nějakou skupinu s uživatelem, jemuž patří profil
{
    if ($profile_user["prava_uzivatel"] == 3 or $profile_user["prava_uzivatel"] == 2 or $profile_user["prava_uzivatel"] == 1) // profil je viditelný pro všechny/reg/skupinu
    {
        echo "<h1><u>Browsing as group member</u></h1>";
        profile_content($profile_user);
    }
    else    // nemám právo zobrazit profil
    {
        echo "<h1>You dont have permissions to view this profile</h1>";
    }
}
else    // pouze přihlášený uživatel
{
    if ($profile_user["prava_uzivatel"] == 3 or $profile_user["prava_uzivatel"] == 2) // profil je viditelný pro všechny, nebo registrované
    {
        echo "<h1><u>Browsing as registered user</u></h1>";
        profile_content($profile_user);
    }
    else    // nemám právo zobrazit profil
    {
        echo "<h1>You dont have permissions to view this profile</h1>";
    }
}




make_footer();
?>