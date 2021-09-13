<?php

//echo $_SERVER["SERVER_NAME"];
//echo $_SERVER["PHP_SELF"];

require "functions_john.php";
$pdo = new DatabaseActions();


// kontrola že jsou povinná pole vyplněná
$nameErr   = $passwordErr = $password_confirmErr = "";
$name   = $password = $password_confirm = "";

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

    if (empty($_POST["password_confirm"]))
    {
        $password_confirmErr = "Confirm password";
        $password_confirm = NULL;
    }
    else
        $password_confirm = $_POST["password_confirm"];

    // kontrola, že jsou stejná hesla
    if ($name !== NULL and $password !== NULL and $password_confirm !== NULL)   // všechny údaje byly zadány
    {
        $user = $pdo->select_user_by_nick(array("nick" => $name));
        if ($user) // uzivatel s daným jménem již existuje
        {
            $nameErr = "This username already exists";
        }
        else
        {
            if ($password == $password_confirm) // hesla jsou stejná
            {
                // vytvoření uživatele v databázi
                $pdo->add_user(array("nick" => $name, "heslo" => $password));
                // uložení uživatele do $_SESSION
                $_SESSION["user"] = $name;
                // zde bude redirect na profil uživatele po registraci
                redirect("profile_john.php?name=" . $name);
            }
            else
            {
                $password_confirmErr = "Passwords have to match";
            }
        }
    }
}

// HTML header
make_header("Register");

// navbar s odkazem na přihlášení
make_navbar_signin();






/*
// vytvoření formuláře pro registraci OLD
?>

<h2>Register</h2>
<p><span class="error">* required field</span></p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    Name: <input type="text" value="<?php if ($name !== NULL) {echo $name;} ?>" name="name">
    <span class="error">* <?php echo $nameErr;?></span>
    <br><br>
    Password: <input type="password" name="password">
    <span class="error">* <?php echo $passwordErr;?></span>
    <br><br>
    Password confirm: <input type="password" name="password_confirm">
    <span class="error">* <?php echo $password_confirmErr;?></span>
    <br><br>
    <input type="submit" name="submit" value="Submit">
</form>

<?php
*/

// new
// vytvoření formuláře pro registraci
?>
<div class="container" style="width:30%">
    <h2>Register</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div  class="input-group">
            <span class="input-group-addon">Nick</span>
            <input type="text" class="form-control" name="name" id="nick" onchange="activate_submit()" required value="<?php if ($name !== NULL) {echo $name;} ?>">
        </div>
        <div  class="input-group">
            <span class="input-group-addon">Password</span>
            <input type="password" class="form-control"  name="password" id="pswd" onchange="activate_submit()" required>
        </div>
        <div  class="input-group">
            <span class="input-group-addon">Confirm password</span>
            <input type="password" class="form-control" name="password_confirm" id="conf_pswd" onchange="activate_submit()" required>
        </div>
        <?php if($nameErr)
        {?>
            <script>
                document.getElementById('nick').style.backgroundColor = 'pink';
            </script>
        <?php } ?>
        <button type="submit"  id="submit" class="btn btn-dafault disabled" >Sign up</button><br>
    </form>
</div>

<script>
    function activate_submit(type) {
        if (document.getElementById('pswd').value === document.getElementById('conf_pswd').value )
        {
            if(document.getElementById('conf_pswd').value !== '')
            {
                document.getElementById('pswd').style.backgroundColor = "lightgreen";
                document.getElementById('conf_pswd').style.backgroundColor = "lightgreen";

                if (document.getElementById('nick').value !== '')
                {
                    document.getElementById('submit').className = "btn btn-dafault active";
                }
            }
        }
        else {
            document.getElementById('pswd').style.backgroundColor = "pink";
            document.getElementById('conf_pswd').style.backgroundColor = "pink";
            document.getElementById('submit').className = "btn btn-dafault disabled";
        }
    }
</script>


<?php



make_footer();


?>
