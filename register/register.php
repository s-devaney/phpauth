<?php

require_once('../common/include.php');

//let's check if the user is already logged in
$session = new Session();
if($session->validate()) {
	header('Location: ../');
}

$user = new User($DBH, $_POST['username'], crypt($_POST['password'], '< 9J<dpH]R]EYcotR<XXX0ZOp39;4W1F/Z<T.4kAU{oK`Ufkyp7!`Mx~>iqvQm)X'), $_POST['email'], (isset($_POST['acceptTOS'])) ? $_POST['acceptTOS'] : false, '');
$validation = $user->validate(array('validate_syntax' => 'username,password,email,TOS', 'validate_unique' => 'username,email'));
if($validation !== true) {
	error($validation);
}

//validation pass
echo 'input valid<br />';

if($user->insert()) {
	echo 'insert successful<br />';
}

echo 'registration successful. please <a href="../login/">login</a>';

?>