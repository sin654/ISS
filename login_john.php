<?php

//echo $_SERVER["SERVER_NAME"];
//echo $_SERVER["PHP_SELF"];

require "functions_john.php";
$pdo = new DatabaseActions();


// kontrola že jsou povinná pole vyplněná
$nameErr   = $passwordErr = "";
$name   = $password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if (empty($_POST["name"]))
    {
        $nameErr = "Name is required";
        $name = NULL;
    }
    else
        $name = $_POST["name"];

    if (empty($_POST["password"]))
    {
        $passwordErr = "Password is required";
        $password = NULL;
    }
    else
        $password = $_POST["password"];

    // kontrola nicku a hesla v databázi
    if ($name !== NULL and $password !== NULL)   // všechny údaje byly zadány
    {
        $user = $pdo->select_user_by_nick(array("nick" => $name));
        if ($user == FALSE) // uživatel s nickem $name neexistuje
        {
            $nameErr = "This user does not exist";
        }
        elseif($user["heslo"] != $password) // uživatel existuje, ale heslo bylo zadané špatně
        {
            $passwordErr = "Wrong password given";
        }
        else    // vše proběhlo ok -> redirect na profil uživatele
        {
            // uložení uživatele do $_SESSION
            $_SESSION["user"] = $name;
            // zde bude redirect na profil uživatele po přihlášení
            redirect("profile_john.php?name=" . $name);
        }

    }
}



// HTML header
make_header("Log IN");

// navbar s odkazem na přihlášení
make_navbar_signin();




/*


// vytvoření formuláře pro registraci OLD
?>

<h2>Log IN</h2>
<p><span class="error">* required field</span></p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    Name: <input type="text" value="<?php if ($name !== NULL) {echo $name;} ?>" name="name">
    <span class="error">* <?php echo $nameErr;?></span>
    <br><br>
    Password: <input type="password" name="password">
    <span class="error">* <?php echo $passwordErr;?></span>
    <br><br>
    <input type="submit" name="submit" value="Submit">
</form>

<?php
*/



// vytvoření formuláře pro registraci
?>
<div class="container" style="width: 30%;">
    <h2>Log IN</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="input-group input-lg">
            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            <input type="text"  id="name" placeholder="Name" name="name" style="background-color: white"
                   value="<?php if ($name !== NULL) {echo $name;} ?>" onchange="activate_submit()" required>
            <?php if($nameErr)
            {?>
                <script> document.getElementById('name').style.backgroundColor = 'pink'; </script>
            <?php } ?>

        </div>
        <div class="input-group input-lg ">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
            <input type="password"  id="pwd" placeholder="Enter password" name="password"
                   onchange="activate_submit()" required style="background-color: white">
            <?php if($passwordErr)
            {?>
                <script> document.getElementById('pwd').style.backgroundColor = 'pink'; </script>
            <?php } ?>
        </div>
        <button id="submit"  type="submit" class="btn btn-default disabled">Submit</button>
    </form>
</div>

<!-- vizuální nastavení tlačítka a polí -->
<script>
    function activate_submit() {
        if (document.getElementById('name').value !== '' && document.getElementById('pwd').value !== '')
        {
            document.getElementById('submit').className = "btn btn-default active";

        }
        else {
            document.getElementById('submit').className = "btn btn-default disabled";
        }

        if (document.getElementById('name').value !== '')
        {
            document.getElementById('name').style.backgroundColor ="white";
        }

        if (document.getElementById('pwd').value !== '')
        {
            document.getElementById('pwd').style.backgroundColor = 'white';
        }

    }
</script>
<?php





make_footer();


?>
