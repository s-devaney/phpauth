<?php
	require_once('common/include.php');

	$session = new Session();
	$loggedIn = $session->validate();

	//if user is logged in create new user object and propagate it with user data from database using the user ID stored in session
	if($loggedIn) {
		try {
			$user = new User($DBH, '', '', '', '', $session->getID());
			$user->propagate('id');
		} catch(Exception $e) {
			error($e->getMessage());
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>PHPAuth</title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="author" content="Sam Devaney" />
	<meta name="robots" content="index, follow" />
	<meta http-equiv="content-language" content="en-gb" />
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<!-- stylesheet -->
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
</head>
<body>
	<h1>Homepage</h1>
	<?php
		if($loggedIn) {
			echo '<p>Welcome back, ' . $user->getUsername() . '.</p>';
		}
	?>
	<a href="login">Login</a><br />
	<a href="register">Register</a>
	<?php
		if($loggedIn) {
			echo '<br /><a href="logout">Logout</a>';
		}
	?>
</body>
</html>
