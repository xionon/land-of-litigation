<?php
session_start();

include (user.inc);

$localuser = new user($_POST['username'], $_POST['password']);

if ($localuser->isLoggedIn() == true)
{
?>
<html><head><title>WELCOME</title></head><body>Welcome, you</body></html>
<?php
}
else
{
echo "you suck";
}

/*

if ( isset($_SESSION['user']) ) //'user' object exists
{
    $localuser = unserialize($_SESSION['user'];
    if ( $localuser->isLogedIn() ) //'user' is logged in
    {
        header("Refresh: 5; URL=userProfile.php");
        echo "redirecting...";
    }
    else //'user' is not logged in
    {
        if (isset($_POST['username']) && isset($_POST['password'])) //user has posted username/password
        {
            $localuser->log_in($_POST['username'] && $_POST['password']);
            if ($localuser->isLoggedIn()) //username/password are correct
            {
                header("Refresh: 5; URL=userProfile.php");
                echo "redirecting...";
            }
            else
            {
                $invalid = true;
            }
        }
    }
}
else //user object does not exist
{
    if (isset($_POST['username']) && isset($_POST['password'])) //user has posted username/password
    {
        $localuser = new user($_POST['username'], $_POST['password']));
        if ($localuser->isLoggedIn()) //username/password correct
        {
            header("Refresh: 5; URL=userProfile.php");
            echo "redirecting...";
        }
    }
}
?>
<html>
<head><title>Login!</title></head>
<body>
<?php
    if ($invalid == true)
    {
        echo "something about that login info is invalid";
    }
?>
    <form action="login.php" method="post">
        Username: <input type="text" name="username" />&nbsp;||&nbsp;
        Password: <input type="password" name="password" />
        <input type="submit" name="submit" value="Login" />
    </form>
</body>
</html>
<?php
/*

//If the user has already started a session, unserialize their object.
//If the user has not started a session, create a new user and try to log them in
if ($_SESSION["user"])
{
    $localuser = unserialize($_SESSION["user"]);
}
else if ($_POST["username"] != "" && $_POST["password"] != "");
{
    $localuser = new user($_POST["username"],$_POST["password"]);
    echo "You are logged in as " . user->get_username() . ".";
}
else
{
?>
<html>
<head><title>Login!</title></head>
<body>
    <form action="login.php" method="post">
        Username: <input type="text" name="username" />&nbsp;||&nbsp;
        Password: <input type="password" name="password" />
        <input type="submit" name="submit" value="Login" />
    </form>
</body>
</html>
<?php
}
if (!$_SESSION["user"])
{
    session_register("user");
}
$_SESSION["user"] = $localuser;
?>
*/
?>