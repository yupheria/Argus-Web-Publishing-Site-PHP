<?php
	/**
	 * Filename : PositionValidator.php
	 * Description : Validates an array of position numbers and checks duplicates
	 * Date Created : December 1, 2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	boolean validatePosition($positionNumbers)
	 *	string getErrors()
	 */
	
	class PositionValidator
	{
		var $errors;
		
		/**
		 * Validate Position method: validates the position for duplicates
		 * Paramaters: ($positionNumbers[])
		 * Return Type : boolean
		 */
		function validatePosition($positionNumbers)
		{
			// check for duplicate numbers
			for($i=0; $i<count($positionNumbers); $i++)
			{
				// monitor the number of instance a duplicate is found
				$numberOfInstance = 0;
				
				for($j=0; $j<count($positionNumbers); $j++)
				{
					if($positionNumbers[$j] == $positionNumbers[$i])
					{
						// increment the value if a duplicate was found
						$numberOfInstance = $numberOfInstance + 1;
						
						if($numberOfInstance > 1)
						{
							$this -> error = "Duplicate position number has been detected";
							
							return false;
						}
					}
				}
			}
			
			// return successful validation
			return true;
		}
		
		/**
		 * Get Errors Method: returns the error that was committed during the validation of position numbers
		 * Return type: string
		 */
		function getErrors()
		{
			return $this -> errors;
		}
	}
?>