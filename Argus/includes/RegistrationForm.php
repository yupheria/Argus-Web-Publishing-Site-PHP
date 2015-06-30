<?php
	/**
	 * Filename : RegistrationForm.php
	 * Description : Contains functions for registering a new account
	 * Date Created : November 28,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	boolean registerAccount($idNumber, $username, $name, $password, $retypedPassword, $email, $position, $imageValue, $realImageValue)
	 *	array getErrors();
	 *	string validateEmail($email)
	 *	string validatePassword($password, $retypedPassword)
 	 *	string validateUsername($username)
	 *	string validateIdNumber($idNumber, $name)
     *  string validateImage($imageValue, $realImageValue)
	 */
	
	class RegistrationForm
	{
		var $errors;
		
		/**
		 * Register Account method: registers the account
		 * Parameters: $idNumber, $username, $name, $password, $retypedPassword, $email, $position, $imageValue, $realImageValue
		 * Return type: boolean
		 */
		function registerAccount($idNumber, $username, $name, $password, $retypedPassword, $email, $position, $imageValue, $realImageValue)
		{
            // escape the characters that needs to be escaped, to avoid sql injection
            $idNumber = mysql_escape_string($idNumber);
            $username = mysql_escape_string($username);
            $name = mysql_escape_string($name);
            $password = mysql_escape_string($password);
            $retypedPassword = mysql_escape_string($retypedPassword);
            $email = mysql_escape_string($email);
            
			// validate the id number and name and make sure it exists within SLU
			$idError = $this -> validateIdNumber($idNumber, $name);
			
			// validate the username if it has already been taken by someone else
			$usernameError = $this -> validateUsername($username);
			
			// validate the password and retyped password if it matches
			$passwordError = $this -> validatePassword($password, $retypedPassword);
			
			// validate the mail if it's in the correct format
			$emailError = $this -> validateEmail($email);
            
            // validate the image if it is correct
            $imageError = $this -> validateImage($imageValue, $realImageValue);
			
			// check for errors during the validation of inputs
			if($idError == null && $usernameError == null && $passwordError == null && $emaiLError == null && $imageError == null)
			{                
				// update the database of slu students that the user has successfully registered
				mysql_query("UPDATE argus_slu_students SET status = 'REGISTERED' WHERE id_number = '".$idNumber."'") or die(mysql_error());
			
				// insert into the database the new account
				mysql_query("INSERT INTO argus_accounts(id_number, username, password, name, position, email, last_login_date, date_registered, status)
							 VALUES ('".$idNumber."', '".$username."', '".$password."', '".$name."', '".$position."', '".$email."', '".time()."', '".time()."', 'ENABLED')") or die(mysql_error());
				
				return true;
			}
			else
			{
				// set the errors if the validation fails
				$this -> errors = array("id" => $idError, "username" => $usernameError, "password" => $passwordError, "email" => $emailError, "image" => $imageError);
				return false;
			}
			
			return;	
		}
		
		/**
		 * Get Errors method: get all the errors that was committed during the registration process
		 * Return Type: array
		 */
		function getErrors()
		{
			return $this -> errors;
		}
		
		
		/**
		 * Validate Email method: validates the email if it's in the correct format
		 * Parameters: $email
		 * Return Type: string
		 */
		function validateEmail($email)
		{
			// include the Email validator class and validate the email
			include("class_libraries/EmailValidator.php");
			$emailValidator = new EmailValidator();
			
			// validate the email
			$result = $emailValidator -> validateEmail($email);
			
			// check the result
			if($result == false)
			{
				// get the errors and return the error
				return $emailValidator -> getErrors();
			}
			// do extra validation for email
			else
			{
				// check if the email address has already been registered to someone else
				$emailQuery = mysql_query("SELECT email FROM argus_accounts WHERE email = '".$email."'") or die(mysql_error());
				
				if(mysql_num_rows($emailQuery) > 0)
				{
					// return a message that the email address has already been registered to someone else
					return "The email address you have provided has already been registered by another member";
				}
			}
		}
		
		/**
		 * Validate Password method: validates the password and retyped password if it matches
		 * Parameters: $password, $retypedPassword
		 * Return Type: string
		 */
		function validatePassword($password, $retypedPassword)
		{
			// include the Password Validator class and create a password validator with a minimum of 5 characters and maximum of 15 characters
			include("class_libraries/PasswordValidator.php");
			$passwordValidator = new PasswordValidator(5,15);
			
			// validate the password
			$result = $passwordValidator -> validatePassword($password, $retypedPassword);
			
			// check the result
			if($result == false)
			{
				// get the error committed and return the error
				return $passwordValidator -> getErrors();
			}
			
			return;
		}
		
		/**
		 * Validate Username method: validates the username if it is unique or not
		 * Parameters: $username
		 * Return Type: string
		 */
		function validateUsername($username)
		{		
			// include the Username Validator class then create a validator which accepts usernames 5 - 15 characters long
			include("class_libraries/UsernameValidator.php");
			$usernameValidator = new UsernameValidator(5, 15);
			
			// validate the username
			$result = $usernameValidator -> validateUsername($username);
			
			// check the result
			if($result == false)
			{
				// get the errors and return it
				return $usernameValidator -> getErrors();
			}
			// do extra validation. this validation is special for the registration page only
			else
			{
				// search the database if the username has already been taken
				$usernameQuery = mysql_query("SELECT username FROM argus_accounts WHERE username = '".$username."'") or die(mysql_error());
				
				if(mysql_num_rows($usernameQuery) > 0)
				{
					// return a message that the username has already been registered to someone else
					return "The username you have provided has already been registered to someone else";
				}
			}
			
			return;
		}
		
		/**
		 * Validate Id Number method: validats the SLU id number if it exists in the database
		 * Parameter: $idNumber, $name
		 * Return Type: string
		 */
		function validateIdNumber($idNumber, $name)
		{
			// include the ID validator class
			include("class_libraries/IdNumberValidator.php");
			$idNumberValidator = new IdNumberValidator();
			
			// validate the id number
			$result = $idNumberValidator -> validateIdNumber($idNumber, $name);
			
			// check the result
			if($result == false)
			{
				// get the errors committed durint the validation
				return $idNumberValidator -> getErrors();
			}
			
			return;
		}
        
        /**
         * Validate Image method: validates if the user inputted image value matches the real image value
         * Parameter: $imageValue, $realImageValue
         * Return Type: string
         */
        function validateImage($imageValue, $realImageValue)
        {
            // check if they are both equal
            if($imageValue != $realImageValue)
            {
                // if they are not equal, return an error that the image value provided is invalid
                return "Image verification failed";
            }
            
            return;
        }
	}
?>