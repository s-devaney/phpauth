<?php

require_once('../common/include.php');

//let's check if the user is already logged in
$session = new Session();
if($session->validate()) {
	header('Location: ../');
}

$user = new User($DBH, $_POST['username'], $_POST['password']);
$userLoginResult = $user->checkLogin();
if($userLoginResult !== true) {
	error($userLoginResult);
}

try {
	$user->propagate('username');
	$user->dump();
	$session->setVars(array('id' => $user->getID(), 'hash' => $session->generateHash()))->update();
} catch(Exception $e) {
	error($e->getMessage());
}

header('Location: ../');

?>