<?php
	/**
	 * Filename : accounts.php
	 * Description : page for managing accounts
	 * Date Created : November 29,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the accounts form class
	require_once("../includes/AccountsForm.php");
	$form = new AccountsForm();
	
	/**
	 * URL EVENTS:
	 *	statistics
 	 *	disabled
 	 * 	contributors
	 *	members
	 */
	
		switch($_GET["event"])
		{
            case "viewarticles";
                $event = "VIEW ARTICLES";
                
                // check if there are any actions in the url
                if($_GET["action"] == "refresh")
                {
                    // refresh the selected article
                    $form -> refreshArticleSubmit($_GET["account"], $_GET["article"]);
                }
                else if($_GET["action"] == "remove")
                {
                    // removes the selected article
                    $form -> removeArticle($_GET["account"], $_GET["article"]);
                }
                else if($_GET["action"] == "restore")
                {
                    // restores the selected article
                    $form -> restoreArticle($_GET["account"], $_GET["article"]);
                }
                else if($_GET["action"] == "delete")
                {
                    // deletes the selected article
                    $form -> deleteArticle($_GET["account"], $_GET["article"]);
                }
            
                break;
            
			case "statistics":
				$event = "STATISTICS";

				// check if there is an action in the URL
				switch($_GET["action"])
				{
					case "delsavedmails":
						// delete all saved mails of the user
						$form -> deleteMails($_GET["account"], "SAVED");
						
						break;
					
					case "deltrashmails":
						// delete all trash mails of the user
						$form -> deleteMails($_GET["account"], "DELETED");
						
						break;
					
					case "delallmails":
						// delete all mails of the user
						$form -> deleteMails($_GET["account"], null);
						
						break;
                    
                    case "delsavedimages":
                        // delete all the saved images of the user
                        $form -> deleteImages($_GET["account"], "SAVED");
                        
                        break;
                    
                    case "deltrashimages":
                        // delete all the trash images of the user
                        $form -> deleteImages($_GET["account"], "DELETED");
                        
                        break;
                    
                    case "delallimages":
                        // delete all the images of the user
                        $form -> deleteImages($_GET["account"], null);
                        
                        break;
				}
				
				break;
			
			case "disabled":
				$event = "DISABLED";
				
				// check for actions in the URL
				if($_GET["action"] == "enable")
				{
					// enable the account
					$form -> enableAccount($_GET["account"]);
				}
				else if($_GET["action"] == "delete")
				{
					// delete the account
					$form -> deleteAccount($_GET["account"]);
				}
				
				break;
				
			case "contributor":
				$event = "CONTRIBUTOR";
				
				// check for actions in the URL
				if($_GET["action"] == "disable")
				{
					// disable the account
					$form -> disableAccount($_GET["account"]);
				}
				
				break;
				
			default:
				$event = "MEMBER";
				
				// check for actions in the URL
				if($_GET["action"] == "disable")
				{
					// disable the account
					$form -> disableAccount($_GET["account"]);
				}
		}
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *	disable
	 *	enable
	 *	delete
     *  refreshSubmit
     *  deleteAllArticle
     *  removeArticle
     *  restoreArticle
     *  deleteArticle
	 */
	 	
		// DISABLE button
		if(isset($_POST["disable"]))
		{
			// get all checked accounts then disables them
			for($i=0; $i<count($_POST["accountIds"]); $i++)
			{
				$form -> disableAccount($_POST["accountIds"][$i]);
			}
		}
		
		// ENABLE button
		if(isset($_POST["enable"]))
		{
			// get all checked accounts then enables them
			for($i=0; $i<count($_POST["accountIds"]); $i++)
			{
				$form -> enableAccount($_POST["accountIds"][$i]);
			}
		}
		
		// DELETE button
		if(isset($_POST["delete"]))
		{
			// get all checked accounts and delete the accounts
			for($i=0; $i<count($_POST["accountIds"]); $i++)
			{
				$form -> deleteAccount($_POST["accountIds"][$i]);
			}
		}
        
        // REFRESH SUBMIT BUTTON
        if(isset($_POST["refreshSubmit"]))
        {
            // get all the checked articles and refresh the submits of the article
            for($i=0; $i<count($_POST["articleIds"]); $i++)
            {
                $form -> refreshArticleSubmit($_GET["account"], $_POST["articleIds"][$i]);
            }
        }
        
        // DELETE ALL BUTTON
        if(isset($_POST["deleteAll"]))
        {
            // delete all the trash articles permanently
            $form -> deleteTrashArticles($_GET["account"]);
        }
        
        // REMOVE ARTICLE BUTTON
        if(isset($_POST["removeArticle"]))
        {
            //  get all the checked articles and removes them
            for($i=0; $i<count($_POST["articleIds"]); $i++)
            {
                $form -> removeArticle($_GET["account"], $_POST["articleIds"][$i]);
            }
        }
        
        // RESTORE ARTICLE BUTTON
        if(isset($_POST["restoreArticle"]))
        {
            // get all the checked articles and restores them
            for($i=0; $i<count($_POST["articleIds"]); $i++)
            {
                $form -> restoreArticle($_GET["account"], $_POST["articleIds"][$i]);
            }
        }
        
        // DELETE ARTICLE BUTTON
        if(isset($_POST["deleteArticle"]))
        {
            // get all the checked articles and deletes them
            for($i=0; $i<count($_POST["articleIds"]); $i++)
            {
                $form -> deleteArticle($_GET["account"], $_POST["articleIds"][$i]);
            }
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
		
		// check the event which to display on the form
		switch($event)
		{
            case "VIEW ARTICLES":
                // display the articles of the user
                $form -> displayAccountArticles($_GET["account"], $_GET["status"]);
            
                break;
                
			case "STATISTICS":
				// display the STATISTIC of an account
				$form -> displayAccountStatistics($_GET["account"]);
				
				break;
			
			case "DISABLED":
				// display all DISABLED accounts
				$form -> displayAccounts("ALL", "DISABLED", $_GET["page"]);
				
				break;
				
			case "CONTRIBUTOR":
				// display all CONTRIBUTOR accounts
				$form -> displayAccounts("CONTRIBUTOR", "ENABLED", $_GET["page"]);
				
				break;
				
			case "MEMBER":
				// display all MEMBER accounts
				$form -> displayAccounts("MEMBER", "ENABLED", $_GET["page"]);
				
				break;
		}
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>