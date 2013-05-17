<?php

require_once('../common/include.php');

$user = new User($_POST['username'], $_POST['password'], $_POST['email']);
$validation = $user->validate(array(array('validate_syntax' => 'username,password,email'), array('validate_unique' => 'username,email')));
//if validation failed print error message and die
if($validation !== true) {
	error($validation);
}

//validation pass

?>