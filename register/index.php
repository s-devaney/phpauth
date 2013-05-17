<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>PHPAuth - register</title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="author" content="Sam Devaney" />
	<meta name="robots" content="index, follow" />
	<meta http-equiv="content-language" content="en-gb" />
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<!-- CSS reset -->
	<link rel="stylesheet" href="css/reset.css" type="text/css" media="screen" />
	<!-- stylesheet -->
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
	<!-- JS -->
	<script type="text/javascript" src="js/script.js"></script>
</head>
<body>
	<h1>Register</h1>
	<form action="register.php" method="POST">
		<label for="username">Username: </label><input type="text" name="username" /><br />
		<label for="password">Password: </label><input type="password" name="password" /><br />
		<label for="email">Email: </label><input type="text" name="email" /><br />
		<input type="checkbox" name="acceptTOS"><label for="acceptTOS">I accept the TOS</label><br />
		<input type="submit" value="Register" />
	</form>
</body>
</html>
