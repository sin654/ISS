<?php

require "functions_john.php";
$pdo = new DatabaseActions();

make_header("Group");



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
 * Načtení profilu skupiny, která se má zobrazit (požadavek typu GET)
 * */
if (isset($_GET["id_skupina"]))
{
    $_SESSION["watched_group_id"] = $_GET["id_skupina"];
    $profile_group = $pdo->select_group(array("id_skupina" => $_GET["id_skupina"]));
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST")  // post požadavek
{
    // TODO tady budou modifikace učtu/přidávání skupin atd.
    if (isset($_POST["description"]))
    {
        // změna popisu skupiny
        if (!empty($_POST["description"]))
            $pdo->modify_group_description(array("id_skupina" => $_SESSION["watched_group_id"], "popis_skupina" => $_POST["description"]));
        else
            $pdo->modify_group_description(array("id_skupina" => $_SESSION["watched_group_id"], "popis_skupina" => ""));
    }
    if (isset($_POST["permissions"]))
    {
        // změna práv pro zobrazení profilu skupiny
        if (!empty($_POST["permissions"]))
            $pdo->modify_group_permissions(array("id_skupina" => $_SESSION["watched_group_id"], "prava_skupina" => (int)$_POST["permissions"]));
    }
    if (isset($_POST["thread_name"]))
    {
        // vytvoření nového vlákna
        if (!empty($_POST["thread_name"]))
            $pdo->add_thread(array("id_skupina" => $_SESSION["watched_group_id"], "nazev_vlakno" => $_POST["thread_name"]));
    }
    if (isset($_POST["request_membership"]))
    {
        // žádost o členství ve skupině
        if (!empty($_POST["request_membership"]))
            $pdo->add_request_member(array("id_skupina" => $_SESSION["watched_group_id"], "id_uzivatel" => $log_user["id_uzivatel"]));
    }
    $profile_group = $pdo->select_group(array("id_skupina" => $_SESSION["watched_group_id"]));
}
else    // při refreshi stránky
{
    $profile_group = $pdo->select_group(array("id_skupina" => $_SESSION["watched_group_id"]));
}




/*
 * Zobrazení obsahu podle přáv na prohlížení skupiny
 * */
if ($log_user === NULL) // je to neregistrovaný uživatel
{
    if ($profile_group["prava_skupina"] == 3) // profil je viditelný pro všechny
    {
        echo "<h1><u>Browsing as unregistered user</u></h1>";
        group_content($profile_group);
    }
    else // nevidí nic
    {
        echo "<h1>You dont have rights to view this profile</h1>";
    }
}
elseif ($pdo->is_user_administrator(array("nick" => $log_user["nick"])))  // přihlášený uživatel je administrátor
{
    // content pro majitele (modify atd.) práva mě nezajímají
    echo "<h1><u>Browsing as admin</u></h1>";
    group_content_manager($profile_group);
}
elseif ($pdo->is_user_group_manager(array("id_skupina" => $profile_group["id_skupina"], "id_uzivatel" => $log_user["id_uzivatel"])))    // správce skupiny
{
    echo "<h1><u>Browsing as group manager</u></h1>";
    group_content_manager($profile_group);
}
elseif ($pdo->is_user_group_mod(array("id_skupina" => $profile_group["id_skupina"], "id_uzivatel" => $log_user["id_uzivatel"])))   // přihlášený uživatel je mod skupiny
{
    echo "<h1><u>Browsing as group moderator</u></h1>";
    group_content_mod($profile_group);
}
elseif ($pdo->is_user_group_member(array("id_skupina" => $profile_group["id_skupina"], "id_uzivatel" => $log_user["id_uzivatel"]))) // přihlášený uživatel je členem skupiny
{
    if ($profile_group["prava_skupina"] == 3 or $profile_group["prava_skupina"] == 2 or $profile_group["prava_skupina"] == 1) // dávám jen pro jistotu, skupiny by práva 0 nikdy mít neměla
    {
        echo "<h1><u>Browsing as group member</u></h1>";
        group_content_member($profile_group);
    }
    else    // nemám právo zobrazit profil
    {
        echo "<h1>You dont have rights to view this profile</h1>";
    }
}
else    // pouze přihlášený uživatel
{
    if ($profile_group["prava_skupina"] == 3 or $profile_group["prava_skupina"] == 2) // profil je viditelný pro všechny, nebo registrované
    {
        echo "<h1><u>Browsing as registered user</u></h1>";
        group_content_nonmember($profile_group);
    }
    else    // nemám právo zobrazit profil
    {
        echo "<h1>You dont have rights to view this profile</h1>";
    }
}



make_footer();
?>