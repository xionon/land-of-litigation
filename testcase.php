<?php
	session_start();
	include_once("user.inc.php");
	include_once("adventure.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
</head>

<body>
This is a test!<br/>
<?php 
    $myadv = new adventure(1,10);
    echo $myadv->showAdventure();
    $myadv->complete();
    echo "<br/><br/>completed<br/><br/>";
    echo $myadv->showAdventure();
?>
</body>
</html>