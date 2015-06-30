<?php
	/**
	 * Filename : IdNumberValidator.php
	 * Description : validates the saint louis university ID if it exists in the database and matches the name
	 * Date Creted : November 30,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	boolean validateIdNumber($idNumber, $name)
	 *	string getErrors()
	 */
	
	class IdNumberValidator
	{
		var $errors;
		
		/**
		 * Validate Id Number method: validates the id number if it matches with the name
		 * Parameters : $idNumber, $name
		 * Return Type: boolean
		 */
		function validateIdNumber($idNumber, $name)
		{
			// check if the name is blank
			if(empty($idNumber))
			{
				// return a message that the id number is blank
				$this -> errors = "Please provide an ID Number";
				
				return false;
			}
			// check if the name is blank
			else if(empty($name))
			{
				// return a message that the name is blank
				$this -> error = "Please provide a name";
				
				return false;
			}
			else
			{
				// check the id number if it exists in the database
				$studentQuery = mysql_query("SELECT id_number, first_name, last_name, middle_initial, status FROM argus_slu_students WHERE id_number = '".$idNumber."'") or die(mysql_error());
				
				if(mysql_num_rows($studentQuery) == 0)
				{
					// return a statement that the id number does not exist in the database
					$this -> errors =  "The ID number you have provided is invalid";
					
					return false;
				}
				// check if the user has already been registered or not
				else if(mysql_result($studentQuery,0,"status") == "REGISTERED")
				{
					// return a statement that the id number is already registered
					$this -> errors = "The ID number has already been registered";
					
					return false;
				}
				// check if the name and id number matches in the database
				else
				{
					$idNumber = mysql_result($studentQuery,0,"id_number");
						
					// arrange the name in a FIRST NAME -> LAST NAME -> MIDDLE INITIAL
					$queriedName = mysql_result($studentQuery,0,"first_name")." ".mysql_result($studentQuery,0,"last_name")." ".mysql_result($studentQuery,0,"middle_initial");
					
					// check if the queried name is equal to the name entered by the user
					if(strtolower($name) != strtolower($queriedName))
					{
						//return a message that the entered name and queried does not match
						$this -> errors =  "The ID number and Name you have provided does not match. Please make sure that you have entered the correct information";
						
						return false;
					}
					else
					{
						// return successfull validation
						return true;
					}
				}
			}
			
			return;
		}
		
		/**
		 * Get Errors method: gets all error that was committed during the validation of Id number
		 */
		function getErrors()
		{
			return $this -> errors;
		}
	}
?>