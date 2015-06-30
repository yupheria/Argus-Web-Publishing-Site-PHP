<?php
	/**
	 * Filename : EmailValidator.php
	 * Description : validates emails if it's in the correct syntax
	 * Date Created : November 30,2007
	 * Authir: Argus Team
	 */
	 
	/**
	 * METHODS SUMMARY:
	 *	boolean validateEmail($email)
	 *	string getErrors()
	 */
	 
	 class EmailValidator
	 {
	 	var $errors;
		
	 	/**
		 * Validate Email method: validates the syntax of the email
		 * Parameters: $email
		 * Return Type: boolean
		 */
		function validateEmail($email)
		{
            // remove the spaces found from the email
            $email = trim($email);
            
			// check if email is empty
			if(empty($email))
			{
				// return a message that the email is empty
				$this -> errors = "Please provide an Email address";
				
				return false;
			}
			// check the format of the email if it is correct
			else if(!preg_match("/^[a-z0-9.+_-]+@([a-z0-9-]+.)+[a-z]+$/", $email))
			{
				// return a message that the email is invalid
				$this -> errors = "Please provide a valid Email address";
				
				return false;
			}
			else
			{
				// return successful validation of email
				return true;
			}
		}
		
		/**
		 * Get Errors method: returns the errors committed during the validation of email
		 * Return Type: string
		 */
		function getErrors()
		{
			return $this -> errors;
		}
	 }
?>