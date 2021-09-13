<?php

require "functions_john.php";
$pdo = new DatabaseActions();

unset($_SESSION["user"]);
unset($_SESSION["id_user"]);

make_header("Log OUT");

make_navbar_signin();
echo "<h1>Sign out was succesfull</h1>";

make_footer();



?>