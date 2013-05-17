<?php

try {
	$DBH = new PDO('mysql:host=localhost;dbname=devaneym_phpauth', 'devaneym_phpauth', '-----');
	$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch(PDOException $e) {
	/* Deployment error handling */
	//echo "MySQL connection error. Check log for details";
	//file_put_contents('error_log.txt', $e->getMessage(), FILE_APPEND);
	/* Development error handling */
	echo $e->getMessage();
}

class User {
	private $username;
	private $password;
	private $email;

	function __construct($username, $password, $email) {
		$this->username = $username;
		$this->password = $password;
		$this->email = $email;
	}

	/*
		Validate()
		Params:
			$data (array): an array of arrays detailing what data to validate e.g
				[
					[
						'validate_syntax': 'username,password,email';
					],
					
					[
						'validate_unique': 'username,email';
					]
				]
		the key details the validation routine to put the value through
		i.e username,password,email should have their syntax validated
	*/
	function validate($data) {
		global $DBH;
		$valid = true;
		foreach($data as $value) {
			foreach($value as $innerKey => $innerValue) {
				switch($innerKey) {
					//validate syntax
					case 'validate_syntax':
						$toValidate = explode(',', $innerValue);
						foreach($toValidate as $innerInnerValue) {
							switch($innerInnerValue) {
								case 'username':
									if(strlen($this->username) > 15 || strlen($this->username) < 3) {
										$valid = 'Your username must be between 3 and 15 characters.';
									}
									if(preg_match('/[^a-z0-9]/i', $this->username)) {
										$valid = 'Your username must only contain letters and numbers.';
									}
									break;
								case 'password':
									if(strlen($this->password) > 15 || strlen($this->password) < 3) {
										$valid = 'Your password must be between 3 and 15 characters.';
									}
									break;
								case 'email':
									if(strlen($this->email) > 60) {
										$valid = 'Your email address must not be longer than 60 characters.';
									}

									if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
										$valid = 'Your email address is not valid.';
									}
									break;
							}
						}
						break;
					//validate data is unique
					case 'validate_unique':
						$toValidate = explode(',', $innerValue);
						foreach($toValidate as $innerInnerValue) {
							switch($innerInnerValue) {
								case 'username':
									$query = 'SELECT COUNT(*) username FROM users WHERE username=?'; 
									$STH = $DBH->prepare($query);
									$STH->bindParam(1, $this->username);
									$STH->execute();
									if($STH->fetchColumn() > 0) {
										$valid = 'This username is already in use. Please try another.';
									}
									break;
								case 'email':
									$query = 'SELECT COUNT(*) email FROM users WHERE email=?';
									$STH = $DBH->prepare($query);
									$STH->bindParam(1, $this->email);
									$STH->execute();
									if($STH->fetchColumn() > 0) {
										$valid = 'This email is already in use. Please try another.';
									}
									break;
							}
						}
						break;
				}
			}
		}
		
		return $valid;
	}
}

function error($message) {
	//log error to file and die
	file_put_contents("error_log.txt", $message, FILE_APPEND | LOCK_EX);
	die($message);
}

?>