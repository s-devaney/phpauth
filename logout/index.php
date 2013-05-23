<?php

require_once('../common/include.php');

$session = new Session();
$loggedIn = $session->validate();

if(!$loggedIn) {
	error('You are not logged in.');
}

$session->destroy();
header('Location: ../');

?>