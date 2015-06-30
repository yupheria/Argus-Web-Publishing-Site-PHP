<?php
	/**
	 * Filename : profiles.php
	 * Description : page for editing profile of the user
	 * Date Created : December 27,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "CONTRIBUTOR");
	
	// import the mail class form
	require_once("../includes/ProfilesForm.php");
	$form = new ProfilesForm($_COOKIE["argus"]);
	
	/**
	 * URL EVENTS:
	 *  statistics
	 */
    
        switch($_GET["event"])
        {                
            default:
                $event = "STATISTICS";
                
                break;
        }
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *  update
	 *  create
	 */
	
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
        
        // check the event
        switch($event)
        {
            case "STATISTICS":
                // display the statistics of the user
                $form -> displayAccountInformation();
            
                break;
        }
        
        echo "</div>";
    ?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>