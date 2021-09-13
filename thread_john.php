<?php

require "functions_john.php";
$pdo = new DatabaseActions();



make_header("Thread");


/*
 * Zjištění jaké vlákno zobrazujeme a jestli je někdo přihlášený
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
 * Načtení profilu vlákna, která se má zobrazit (požadavek typu GET)
 * */
if (isset($_GET["id_vlakno"]))
{
    $_SESSION["watched_thread_id"] = $_GET["id_vlakno"];
    $profile_thread = $pdo->select_thread(array("id_vlakno" => $_GET["id_vlakno"]));
}
elseif (isset($_POST["text"]))  // byl zaslán nový příspěvek
{
    // přidání příspěvku do databáze
    $profile_thread = $pdo->select_thread(array("id_vlakno" => $_SESSION["watched_thread_id"]));
    $pdo->add_contribution(array("id_vlakno" => $profile_thread["id_vlakno"], "id_uzivatel" => $log_user["id_uzivatel"], "text" => $_POST["text"]));
}
else    // když se refreshne page
{
    $profile_thread = $pdo->select_thread(array("id_vlakno" => $_SESSION["watched_thread_id"]));
}




/*
 * Načtení profilu skupiny ve kterém se vlákno nachází (nutné kvůli právům)
 * */
$profile_group = $pdo->select_group(array("id_skupina" => $profile_thread["id_skupina"]));


/*
 * Zobrazení obsahu podle přáv na prohlížení skupiny
 * */
if ($log_user === NULL) // je to neregistrovaný uživatel
{
    if ($profile_group["prava_skupina"] == 3) // profil je viditelný pro všechny
    {
        echo "<h1><u>Browsing as unregistered user</u></h1>";
        thread_content($profile_thread);
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
    thread_content_mod($profile_thread);
}
elseif ($pdo->is_user_group_manager(array("id_skupina" => $profile_group["id_skupina"], "id_uzivatel" => $log_user["id_uzivatel"]))) // správce skupiny
{
    // content pro majitele (modify atd.) práva mě nezajímají
    echo "<h1><u>Browsing as group manager</u></h1>";
    thread_content_mod($profile_thread);
}
elseif ($pdo->is_user_group_mod(array("id_skupina" => $profile_group["id_skupina"], "id_uzivatel" => $log_user["id_uzivatel"])))    // mod skupiny
{
    // content pro majitele (modify atd.) práva mě nezajímají
    echo "<h1><u>Browsing as group moderator</u></h1>";
    thread_content_mod($profile_thread);
}
elseif ($pdo->is_user_group_member(array("id_skupina" => $profile_group["id_skupina"], "id_uzivatel" => $log_user["id_uzivatel"])))   // člen skupiny
{
    echo "<h1><u>Browsing as group member</u></h1>";
    thread_content_member($profile_thread);
}
else    // pouze přihlášený uživatel
{
    if ($profile_group["prava_skupina"] == 3 or $profile_group["prava_skupina"] == 2) // profil je viditelný pro všechny, nebo registrované
    {
        echo "<h1><u>Browsing as registered user</u></h1>";
        thread_content($profile_thread);
    }
    else    // nemám právo zobrazit profil
    {
        echo "<h1>You dont have rights to view this profile</h1>";
    }
}










make_footer();
?>