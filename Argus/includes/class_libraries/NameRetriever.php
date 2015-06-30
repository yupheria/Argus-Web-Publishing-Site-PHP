<?php
	/**
	 * Filename : NameRetriever.php
	 * Description : searches the name from the database given a particular id
	 * Date Created : December 7,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
     *  NameRetriever($idType)
	 *	string getName($id)
	 */
	
	class NameRetriever
	{
        var $idType;
        
        /**
         * Constructor method
         * Parameter: $idType
         */
        function NameRetriever($idType)
        {
            // set the ID type so that when retrieving a name from the database, the program will know
            // which name is being retrieved
            $this -> idType = $idType;
            
            return;
        }
        
        /**
         * Get Name method: gets a name in the database depending on what type of ID is being passed as a parameter
         * Parameter: $id
         * Return Type: String
         */
		function getName($id)
        {
            // determin what type of ID is being passed
            switch($this -> idType)
            {
                case "category_id":
                    // query the category name from the database using the id
                    $nameQuery = mysql_query("SELECT name FROM argus_categories WHERE category_id = '".$id."'") or die(mysql_error());
                    
                    break;
                
                case "account_id":
                    // query the account name from the database using the id
                    $nameQuery = mysql_query("SELECT name FROM argus_accounts WHERE account_id = '".$id."'") or die(mysql_error());
                    
                    break;
                
                case "issue_id":
                    // query the issue name from the database using the id
                    $nameQuery = mysql_query("SELECT name FROM argus_issues WHERE issue_id = '".$id."'") or die(mysql_error());
            }
			
			// check if there is a name queried from the database
			if(mysql_num_rows($nameQuery) == 0)
			{
				// if no name, set the name to UNKOWN
				// reasons why the name was not queried is because the name was deleted
				return "UNKOWN";
			}
			else
			{
				// set the name
				return mysql_result($nameQuery,0,"name");
			}
			
			return;
		}
	}
?>