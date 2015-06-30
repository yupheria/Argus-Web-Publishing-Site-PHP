<?php
	/**
	 * Filename : PasswordValidator.php
	 * Description : validates password and retyped password if it matches
	 * Date Created : November 30,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	PasswordValidator($minimumLength, $maximumLength)
	 *	boolean validatePassword($password, $retypedPassword)
	 *	string getErrors()
	 */
	
	class PasswordValidator
	{	
		/**
		 * Contstructor method
		 * Paramaters: $minimumLength, $maximumLength
		 */
		function PasswordValidator($minimumLength, $maximumLength)
		{
			// set the minimum and maximum length
			$this -> minimumLength = $minimumLength;
			$this -> maximumLength = $maximumLength;
			
			return;
		}
		
		/**
		 * Validate Password method: validates if the password, and retyped password matches
		 * Parameters: $password, $retypedPassword
		 */
		function validatePassword($password, $retypedPassword)
		{
            // trim the password removing the extra spaces from the left and right
            $password = trim($password);
            
			// check if the password is blank
			if(empty($password))
			{
				// set a message the the password is blank
				$this -> errors = "Please provide a password";
				
				return false;
			}
			// validate the length of the password
			else if(strlen($password) < $this -> minimumLength || strlen($password) > $this -> maximumLength)
			{
				// set the error and return false if the password does not match with the criteria
				$this -> errors = "Passwords must be ".$this -> minimumLength." - ".$this -> maximumLength." characters long";
				return false;
			}
			// validate if password is equal to the written password
			else if($password != $retypedPassword)
			{
				// return false if the password and retypedPassword is not equal
				$this -> errors = "You have mistyped your password";
				return false;
			}
			else
			{
				// return true if validation succeeded
				return true;
			}
			
			return;
		}
		
		/**
		 * Get Errors method: returns the error committed during the validation of password
		 * Return type: String
		 */
		function getErrors()
		{
			return $this -> errors;
		}
	}
?>