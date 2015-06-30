<?php
	/**
	 * Filename : images.php
	 * Description : page for managing images
	 * Date Created : December 4,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "CONTRIBUTOR");
	
	// import the images form class
	require_once("../includes/ImagesForm.php");
	$form = new ImagesForm($_COOKIE["argus"]);
	
	/**
	 * URL EVENTS:
	 *  deleted
	 *  saved
	 */
    
        switch($_GET["event"])
        {
            case "statistics":
                $event = "STATISTICS";
                
                break;
                
            case "deleted":
                $event = "DELETED";
                
                // check if there are actions in the URL
                if($_GET["action"] == "restore")
                {
                    // restore the deleted image
                    $form -> restoreImage($_GET["image"]);
                }
                else if($_GET["action"] == "delete")
                {
                    // delete the image
                    $form -> deleteImage($_GET["image"]);
                }
            
                break;
            
            default:
                $event = "SAVED";
                
                // check if there are actions in the URL
                if($_GET["action"] == "remove")
                {
                    // remove the image and send it to the trash section
                    $form -> removeImage($_GET["image"]);
                }
                
                break;
        }
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *  remove
	 *  restore
	 *  delete
	 *  delete all
	 */
    
        // REMOVE button
        if(isset($_POST["remove"]))
        {
            // get all checked images and remove them
            for($i=0; $i<count($_POST["imageIds"]); $i++)
            {
                $form -> removeImage($_POST["imageIds"][$i]);
            }
        }
        
        // RESTORE button
        if(isset($_POST["restore"]))
        {
            // get all checked images and restores them
            for($i=0; $i<count($_POST["imageIds"]); $i++)
            {
                $form -> restoreImage($_POST["imageIds"][$i]);
            }
        }
        
        // DELETE button
        if(isset($_POST["delete"]))
        {
            // get all checked images and deletes them
            for($i=0; $i<count($_POST["imageIds"]); $i++)
            {
                $form -> deleteImage($_POST["imageIds"][$i]);
            }
        }
        
        // DELETE ALL button
        if(isset($_POST["deleteAll"]))
        {
            // delete all removed article
            $form -> deleteRemovedImages();
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
        
        // determine which form to display
        switch($event)
        {
            case "STATISTICS":
                // display the statistics of the image
                $form -> displayImageStatistics($_GET["image"]);
            
                break;
            
            case "DELETED":
                // display all deleted images
                $form -> displayImages("DELETED");
                
                break;
                
            case "SAVED":
                // display all saved images
                $form -> displayImages("SAVED");
                
                break;
        }
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>