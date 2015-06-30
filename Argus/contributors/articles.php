<?php
	/**
	 * Filename : articles.php
	 * Description : page for managing saved articles
	 * Date Created : December 3,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "CONTRIBUTOR");
	
	// import the saved articles form
	require_once("../includes/SavedArticlesForm.php");
	$form = new SavedArticlesForm($_COOKIE["argus"]);
	
	/**
	 * URL EVENTS:
	 *	submitted
	 *	view
	 *	deleted
	 *	saved
	 */
	
		switch($_GET["event"])
		{
            case "submitted":
                $event = "SUBMITTED";
                
                break;
               
            case "view":
                $event = "VIEW";
                
                break;
                
			case "deleted":
				$event = "DELETED";
				
				// check if there is an action from the URL
				if($_GET["action"] == "restore")
				{
					// restore the article
					$form -> restoreArticle($_GET["article"]);
				}
                else if($_GET["action"] == "delete")
                {
                    // delete the article
                    $form -> deleteArticle($_GET["article"]);
                }
				
				break;
				
			default:
				$event = "SAVED";
				
				// check if there is an action from the URL
				if($_GET["action"] == "remove")
				{
					// remove the article
					$form -> removeArticle($_GET["article"]);
				}
                else if($_GET["action"] == "submit")
                {
                    // submit the article
                    $form -> submitArticle($_GET["article"]);
                    
                    // after it has been submitted, redirect the user to the Submitted sections to see the status of the article
                    header("Location: articles.php?event=submitted");
                }
				
				break;
		}
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *	remove
	 *	restore
	 *	delete
	 *	deleteAll
	 *	submit
	 */
	
		// REMOVE button
		if(isset($_POST["remove"]))
		{
			// get all checked articles and remove them
			for($i=0; $i<count($_POST["articleIds"]); $i++)
			{
				$form -> removeArticle($_POST["articleIds"][$i]);
			}
		}
		
		// RESTORE button
		if(isset($_POST["restore"]))
		{
			// get all checked articles and then restores them
			for($i=0; $i < count($_POST["articleIds"]); $i++)
			{
				$form -> restoreArticle($_POST["articleIds"][$i]);
			}
		}
        
        // DELETE button
        if(isset($_POST["delete"]))
        {
            // get all checked articles and then deletes them
            for($i=0; $i < count($_POST["articleIds"]); $i++)
            {
                $form -> deleteArticle($_POST["articleIds"][$i]);
            }
        }
        
        // DELETE ALL button
        if(isset($_POST["deleteAll"]))
        {
            // delete all deleted articles
            $form -> deleteAllArticles();
        }
        
        // SUBMIT button
        if(isset($_POST["submit"]))
        {
            // get all checked articles and then submits them
            for($i=0; $i < count($_POST["articleIds"]); $i++)
            {
                $form -> submitArticle($_POST["articleIds"][$i]);
            }
            
            // after it has been submitted, redirect the user to the Submitted sections to see the status of the article
            header("Location: articles.php?event=submitted");
        }
	
	/**
	 * END OF BUTTON TRIGGER EVENTS
	 */
	
	// display the header
	$page -> displayHeader();
	
	// display the banner
	$page -> displayBanner();
?>
<!-- page content -->
<div id='content'>
	<!-- right side bar: contains the tool bars and features of each account -->
	<?php
        $page -> displayDivCode("RIGHT");
        
		// display the tools
		$page -> displayTools();
        
        echo "</div>";
	?>
	<!-- left side column: contains sub options and articles and where manipulation of tools occurs -->
	<?php
        $page -> displayDivCode("LEFT");
        
		// display the banner
		$form -> displayBanner();
		
		// determine which form is to be displayed
		switch($event)
		{
            case "SUBMITTED":
                // display all submitted articles
                $form -> displaySubmitted();
            
                break;
            
            case "VIEW":
                // display the article that is to be viewed
                $form -> viewArticle($_GET["article"]);
                
                break;
                
			case "DELETED":
				// display all delete articles
				$form -> displayArticles("DELETED", $_GET["page"]);
				
				break;
				
			case "SAVED":
				// display all saved articles
				$form -> displayArticles("SAVED", $_GET["page"]);
			
				break;
		}
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>