<?php
	/**
	 * Filename : events.php
	 * Description : page for managing events
	 * Date Created : December 16,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the mail class form
	require_once("../includes/EventsForm.php");
	$form = new EventsForm();
	
	/**
	 * URL EVENTS:
	 *  view
	 *  deleted
	 *  saved
	 */
    
        switch($_GET["event"])
        {
            case "view":
                $event = "VIEW";
                
                break;
                
            case "deleted":
                $event = "DELETED";
                
                // check for actions from the URL
                if($_GET["action"] == "restore")
                {
                    // restore the event
                    $form -> restoreEvent($_GET["aevent"]);
                }
                else if($_GET["action"] == "delete")
                {
                    // delete an event
                    $form -> deleteEvent($_GET["aevent"]);
                }
                
                break;
            
            default:
                $event = "SAVED";
                
                // check for actions from the URL
                if($_GET["action"] == "remove")
                {
                    // remove the event
                    $form -> removeEvent($_GET["aevent"]);
                }
                
                break;
        }
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *  deleteAll
	 *  delete
	 *  remove
	 *  restore
	 */
        
        // DELETE ALL button
        if(isset($_POST["deleteAll"]))
        {
            // delete all the removed events
            $form -> deleteAllEvents();
        }
    
        // DELETE button
        if(isset($_POST["delete"]))
        {
            // get all the checked events and then deletes them
            for($i=0; $i < count($_POST["eventIds"]); $i++)
            {
                $form -> deleteEvent($_POST["eventIds"][$i]);
            }
        }
    
        // REMOVE button
        if(isset($_POST["remove"]))
        {
            // get all the checked events and then removes them
            for($i=0; $i < count($_POST["eventIds"]); $i++)
            {
                $form -> removeEvent($_POST["eventIds"][$i]);
            }
        }
        
        // RESTORE button
        if(isset($_POST["restore"]))
        {
            // get all the checked events and then restores them
            for($i=0; $i< count($_POST["eventIds"]); $i++)
            {
                $form -> restoreEvent($_POST["eventIds"][$i]);
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
        
		// display the tools for ADMINISTRATORS
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
            case "VIEW":
                // view the contents of a specific event
                $form -> viewEvent($_GET["aevent"]);
                
                break;
            
            case "DELETED":
                // display all deleted events
                $form -> displayEvents("DELETED");
            
                break;
            case "SAVED":
                // display all saved events
                $form -> displayEvents("SAVED");
                
                break;
		}
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>