<?php

try {
	//$DBH = new PDO('mysql:host=localhost;dbname=devaneym_phpauth', 'devaneym_phpauth', '-----');
	$DBH = new PDO('sqlite:/var/www/phpauth/common/phpauth.db');
	$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
	/* Deployment error handling */
	//error('MySQL connection error. Check log for details');
	/* Development error handling */
	error($e->getMessage());
}

class User {
	private $id;
	private $username;
	private $password;
	private $email;
	private $TOS;
	private $DBH;

	function __construct(PDO $DBH, $username='', $password='', $email='', $TOS='', $id='') {
		$this->DBH = $DBH;
		$this->username = $username;
		$this->password = $password;
		$this->email = $email;
		$this->TOS = $TOS;
		$this->id = $id;
	}

	/*
		Validate()
		Params:
			$data (array): an array of arrays detailing what data to validate e.g
				[
					[
						'validate_syntax': 'username,password,email';	//validate length, etc of username,password&email
					],
					
					[
						'validate_unique': 'username,email';	//validate that the username and email specified do not exist in the database
					]
				]
		the key details the validation routine to put the value through
		i.e username,password,email should have their syntax validated
	*/
	function validate($data) {
		$valid = true;

		foreach($data as $key=>$value) {
			$toValidate = explode(',', $value);

			switch($key) {
				//validate syntax
				case 'validate_syntax':
					foreach($toValidate as $innerValue) {
						switch($innerValue) {
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
							case 'TOS':
								if(!$this->TOS) {
									$valid = 'You must accept the TOS to register.';
								}
								break;
						}
					}
					break;

				//validate data is unique (i.e is not already in the database)
				case 'validate_unique':
					foreach($toValidate as $innerValue) {
						switch($innerValue) {
							case 'username':
								try {
									$query = 'SELECT COUNT(*) username FROM users WHERE username=?'; 
									$STH = $this->DBH->prepare($query);
									$STH->bindParam(1, $this->username);
									$STH->execute();
									if($STH->fetchColumn() > 0) {
										$valid = 'This username is already in use. Please try another.';
									}
								} catch(PDOException $e) {
									error($e->getMessage());
								}
								break;
							case 'email':
								try {
									$query = 'SELECT COUNT(*) email FROM users WHERE email=?';
									$STH = $this->DBH->prepare($query);
									$STH->bindParam(1, $this->email);
									$STH->execute();
									if($STH->fetchColumn() > 0) {
										$valid = 'This email is already in use. Please try another.';
									}
								} catch(PDOException $e) {
									error($e->getMessage());
								}
								break;
						}
					}
					break;
			}
		}
		
		return $valid;
	}
	
	function insert() {
		try {
			$query = 'INSERT INTO users (username, password, email) VALUES(?, ?, ?)';
			$STH = $this->DBH->prepare($query);
			$STH->execute(array($this->username, $this->password, $this->email));
		} catch(PDOException $e) {
			error($e->getMessage());
		}
		
		$this->userID = $this->DBH->lastInsertId();
		return true;
	}
	
	function checkLogin() {
		try {
			$query = 'SELECT COUNT(*) FROM users WHERE username=? AND password=?';
			$STH = $this->DBH->prepare($query);
			$STH->execute(array($this->username, crypt($this->password, '< 9J<dpH]R]EYcotR<XXX0ZOp39;4W1F/Z<T.4kAU{oK`Ufkyp7!`Mx~>iqvQm)X')));
			if($STH->fetchColumn() > 0) {
				return true;
			} else {
				return 'Your username or password is incorrect.';
			}
		} catch(PDOException $e) {
			error($e->getMessage());
		}
	}
	
	function propagate($selector) {
		if($selector !== 'id' and $selector !== 'username') {
			throw new Exception('Selector must be unique data (i.e. username or user ID). Supplied: '.$selector);
		}

		try {
			$query = 'SELECT id, username, password, email FROM users WHERE '.$selector.'=?';
			$STH = $this->DBH->prepare($query);
			$STH->execute(array($this->{$selector}));
			$STH->setFetchMode(PDO::FETCH_ASSOC);
			$data = $STH->fetch();
			
			foreach($data as $colName=>$colValue) {
				$this->{$colName} = $colValue;
			}
		} catch(PDOException $e) {
			error($e->getMessage());
		}
	}

	function getID() {
		if(empty($this->id)) {
			throw new Exception('ID is not set.');
		}
		return $this->id;
	}
	
	function getUsername() {
		if(empty($this->username)) {
			throw new Exception('Username has not been set');
		}
		
		return $this->username;
	}
	
	function dump() {
		print_r($this);
	}
}

class Session {
	private $id;
	private $hash;

	function __construct() {
		session_start();
		//map session variables to object
		foreach($_SESSION as $key=>$value) {
			$this->{$key} = $value;
		}
	}
	
	//set object vars
	function setVars($data) {
		foreach($data as $key=>$value) {
			$this->{$key} = $value;
		}
		
		return $this;
	}
	
	//generate a hash for the session
	function generateHash() {
		$IPAddress = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		$userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'No user agent';
		$charset = !empty($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : 'No charset';
		$hash = md5($userAgent . $IPAddress . $charset);
		
		return $hash;
	}
	
	//validate if a session exists and if so check it has not been hi-jacked
	function validate() {
		//first, let's see if the session has any variables set. if not, then user is not logged in
		if(!isset($this->hash)) {
			return false;
		}
	
		//now lets check for session hi-jacking. when session is first created, a hash is made of the users IP, user agent and their charset. if an attacker hi-jacks the users session_id
		//they will need to use the users IP and imitate their useragent and charset in order to be able to take control of the session.
		//I.E. they will need physical access to the device the user has the active session on (to use their IP addr)
		//so lets generate a new hash and see if it is the same as the original hash
		if($this->generateHash() !== $this->hash) {
			//session may have been hi-jacked, let's destroy it to be safe
			echo 'Nice try!';
			$this->destroy();
			return false;
		}
		
		return true;
	}
	
	//map object vars to session
	function update() {
		foreach($this as $key=>$value) {
			$_SESSION["{$key}"] = $value;
		}
	}
	
	//destroy the session I.E. logout
	function destroy() {
		session_destroy();
	}
	
	function getID() {
		if(empty($this->id)) {
			throw new Exception('User ID is not set');
		}
		return $this->id;
	}
	
	function dump() {
		print_r($this);
	}
}

function error($message) {
	//log error to file and die
	//file_put_contents("error_log.txt", $message, FILE_APPEND | LOCK_EX);
	die($message);
}

?>