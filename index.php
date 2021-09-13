<?php

require "functions_john.php";
$pdo = new DatabaseActions();

make_header("Home");

if (isset($_SESSION["user"]))   // je přihlášený uživatel
{
    make_navbar_signout();
}
else
    make_navbar_signin();


// odkaz na dokumentaci
?>
<p>
    <a href="doc.html">Dokumentace</a>
</p>




<?php

make_footer();
?>