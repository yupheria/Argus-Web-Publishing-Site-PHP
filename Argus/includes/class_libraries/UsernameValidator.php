<?php
	/**
	 * Filename : UsernameValidator.php
	 * Description : Validates usernames if it's in the correct format
	 * Date Created : November 30,2007
	 * Author : Arugs Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	UsernameValidator($minimumLength, $maximumLength)
	 *	boolean validateUsername($username)
	 */
	
	class UsernameValidator
	{
		var $minimumLength;
		var $maximumLength;
		var $errors;
		
		/**
		 * Constructor Method
		 * Parameter: $minimumLength, $maximumLength
		 */
		function UsernameValidator($minimumLength, $maximumLength)
		{
			// set the maximum length and minimum length of the username
			$this -> minimumLength = $minimumLength;
			$this -> maximumLength = $maximumLength;
			
			return;
		}
		
		/**
		 * Validate username method: validates the username if it's in the correct format
		 * Parameters: $username
		 * Return Type: boolean
		 */
		function validateUsername($username)
		{
			// check if the username is blank
			if(empty($username))
			{
				// return a message that username is blank
				$this -> errors = "Please provide a username";
				
				return false;
			}
			// check if the username has the right length
			else if(strlen($username) < $this -> minimumLength || strlen($username) > $this -> maximumLength)
			{
				// return a message that the username length is invalid
				$this -> errors = "Usernames must ".$this -> minimumLength." - ".$this -> maximumLength." characters long";
				
				return false;
			}
			// check the pattern of the username if it is valid
			else if(eregi("[^a-zA-Z0-9\_\-]", $username))
			{
				// return a message that the user has an invalid username
				$this -> errors = "Please provide a valid username";
				
				return false;
			}
			else
			{
				// return successfull validation
				return true;
			}
			
			return;
		}
		
		/**
		 * Get Errors method: return the errors during the validation process
		 * Return Type: string
		 */
		function getErrors()
		{
			return $this -> errors;
		}
	}
?>