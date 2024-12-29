<?php
include "../classes/User.php";

#create/instantiate an object
$user = new User;

#call the method(=function) through the object
$user->update($_POST, $_FILES);

/*
* $_POST: to receive the text data
* $_FILE: to receive the image/file
*/



?>