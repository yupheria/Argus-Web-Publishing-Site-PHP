<?php
	/**
	 * Filename : mailbox.php
	 * Description : page for managing mails
	 * Date Created : December 12,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the mail class form
	require_once("../includes/MailsForm.php");
	$form = new MailsForm($_COOKIE["argus"]);
	
	/**
	 * URL EVENTS:
	 *  viewmail
	 *  deleted
	 *  contacts
	 *  saved
	 */
	
		switch($_GET["event"])
		{
            case "viewmail":
                $event = "VIEW MAIL";
                
                break;
            
            case "deleted":
                $event = "DELETED";
                
                // check for actions in the URL
                if($_GET["action"] == "restore")
                {
                    // restore the mail
                    $form -> restoreMail($_GET["mail"]);
                }
                else if($_GET["action"] == "delete")
                {
                    // delete the mail
                    $form -> deleteMail($_GET["mail"]);
                }
            
                break;
                
            case "contacts":
                $event = "CONTACTS";
            
                break;
                
			default:
				$event = "SAVED";
				
                // check for actions in the URL
                if($_GET["action"] == "remove")
                {
                    // remove the article
                    $form -> removeMail($_GET["mail"]);
                }
                
				break;
		}
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *  insert
	 *  remove
	 *  delete
	 *  restore
	 *  deleteAll
	 */
    
        // INSERT button
        if(isset($_POST["insert"]))
        {
            // get all the checked contact ids
            $contactIds = $_POST["contactIds"];
            
            // the the contact id's are in an array form
            // use the implode function to make the array into a single line string separating each contact ids using the
            // character ","
            $contactIds = implode(",", $contactIds);
            
            // send the imploded contact ids to the compose page
            header("Location: mailscompose.php?event=compose&contacts=".$contactIds);
        }
        
        // REMOVE button
        if(isset($_POST["remove"]))
        {
            // get all the checked mails and transfer them to the deleted section
            for($i=0; $i < count($_POST["mailIds"]); $i++)
            {
                $form -> removeMail($_POST["mailIds"][$i]);
            }
        }
        
        // DELETE button
        if(isset($_POST["delete"]))
        {
            // get all the checked mails and delete them permanently
            for($i=0; $i < count($_POST["mailIds"]); $i++)
            {
                $form -> deleteMail($_POST["mailIds"][$i]);
            }
        }
        
        // RESTORE button
        if(isset($_POST["restore"]))
        {
            // get all the checked mails and restore them
            for($i=0; $i < count($_POST["mailIds"]); $i++)
            {
                $form -> restoreMail($_POST["mailIds"][$i]);
            }
        }
        
        // DELETE ALL button
        if(isset($_POST["deleteAll"]))
        {
            // delete all removed mails of the current user
            $form -> deleteAllMails();
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
            case "VIEW MAIL":
                // display the contents of the mail
                $form -> viewMail($_GET["mail"]);
            
                break;
            
            case "DELETED":
                // display all deleted mails
                $form -> displayMails("DELETED", $_GET["page"]);
                
                break;
                
            case "CONTACTS":
                // display all contacts
                $form -> displayContacts();
                
                break;
                
			case "SAVED":
				// display all saved mails
				$form -> displayMails("SAVED", $_GET["page"]);
			
				break;
		}
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>