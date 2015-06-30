<?php
	/**
	 * Filename : TitleValidator.php
	 * Description : validates the title if it has the correct length
	 * Date Created : December 3, 2007
	 * Author : Argus_team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	TitleValidator($minimumLength, $maximumLength)
	 *	boolean validateTitle($title);
	 *	getErrors()
	 */
	
	class TitleValidator
	{
		var $minimumLength;
		var $maximumLength;
		var $errors;
		
		/**
		 * Constructor method
		 * Parameter: $minimumLength, $maximumLength
		 */
		function TitleValidator($minimumLength, $maximumLength)
		{
			// set the minimumlength, and maximumlength
			$this -> minimumLength = $minimumLength;
			$this -> maximumLength = $maximumLength;
				
			return;
		}
		
		/**
		 * Validate Title method: validates the length of the title
		 * Parameters: $title
		 * Return type: boolean
		 */
		function validateTitle($title)
		{
            // trim the title to remove extra spaces
            $title = trim($title);
            
			// check if the title is empty
			if(empty($title))
			{
				// set the error that the title is empty
				$this -> errors = "Please provide a title";
				
				// return unsuccessful validation
				return false;
			}
			// check the length
			else if(strlen($title) < $this -> minimumLength || strlen($title) > $this -> maximumLength)
			{
				// set an error that the length is invalid
				$this -> errors = "Title should be ".$this -> minimumLength."-".$this -> maximumLength." characters long";
				
				// return uncessful validation
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
		 * Get Errors method: returns the errors that was committed during the validation of the textbox
		 * return type: string
		 */
		function getErrors()
		{
			// return the errors
			return $this -> errors;
		}
	}
?>