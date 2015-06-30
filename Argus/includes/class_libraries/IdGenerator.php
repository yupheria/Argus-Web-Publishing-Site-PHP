<?php
	/**
	 * Filename : IdGenerator.php
	 * Description : generates unique ID which will be used as primary keys
	 * Date Created : December 3,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	string generateId($primaryId)
	 */
	
	class IdGenerator
	{
		/**
		 * Generate ID method: generates an ID which will be used as an ID
		 * return type: string
		 */
		function generateId($primaryId)
		{
			// keep on generating an ID until the id that is generated is UNIQUE
			do 
			{
				// generate an ID
				$id = rand();
				
                // check the type of primary id which kind of unique id to create
                switch($primaryId)
                {
                    case "saved_article_id":
                        // check if the ID has already been used by other saved articles
                        $idQuery = mysql_query("SELECT saved_article_id FROM argus_saved_articles WHERE saved_article_id = '".$id."'") or die(mysql_error());
                        
                        break;
                    
                    case "image_id":
                        // check if the id has already been used by other images
                        $idQuery = mysql_query("SELECT image_id FROM argus_images WHERE image_id = '".$id."'") or die(mysql_error());
                        
                        break;
                }
			}
			// check if the ID is unique, if it's not unique, generate another ID	
			while(mysql_num_rows($idQuery) > 0);
			
			// return the generated ID
			return $id;
		}
	}
?>