<?php
/*
There is only one database that will be used through this site, so it makes sense to abstract it by creating a simple includeable file that automatically makes the connection on any page that needs it.
*/
    $conn = mysql_connect("turing", "hipsheaa", "hipsheaa9623")
        or die(mysql_error());
    $db = mysql_select_db("hipsheaa")
        or die(mysql_error());
?>