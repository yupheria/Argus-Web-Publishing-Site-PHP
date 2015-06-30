<?php
	/**
	 * Filename : DescriptionValidator.php
	 * Description : Validates a word if it conforms to the length and width expected
	 * Date Created : December 3,2007
	 * Author : Argus Team
	 */
	 
	/**
	 * METHODS SUMMARY:
	 *	DescriptionValidator($maximumLength)
	 *	boolean validateDescription($description)
	 *	string getErrors()
	 */
	
	class DescriptionValidator
	{
		var $maximumLength;
		var $errors;
		
		/**
		 * Constructor Method:
		 * Parameters: $maximumLength
		 */
		function DescriptionValidator($maximumLength)
		{
			$this -> maximumLength = $maximumLength;
			
			return;
		}
		
		/**
		 * Validate Description method: Validates the description if it's empty and the length of characters
		 * Parameter: $description
		 * Return type: boolean
		 */
		function validateDescription($description)
		{
			// check the length of the word
			if(strlen($description) > $this -> maximumLength)
			{
				// return a message that the word has invalid length
				$this -> errors = "Please make your description less than ".$this -> maximumLength." characters length";
				
				// return unsuccessful validation
				return false;
			}
			else
			{
				// return successful validation
				return true;
			}
			
			return;
		}
		
		/**
		 * Get Errors method: returns errors that was commited during the validation process
		 * Return type: string
		 */
		function getErrors()
		{
			// return the errors
			return $this -> errors;
		}
	}
?>