<?php
	/**
	 * Filename : settings.php
	 * Description : page for managing the web settings of the website
	 * Date Created : December 23,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the settings form class
	require_once("../includes/SettingsForm.php");
	$form = new SettingsForm();
	
	/**
	 * URL EVENTS:
	 *  welcome banner
	 *  themes
	 *  contact us
	 *  terms and policies
	 */
     
        switch($_GET["event"])
        {            
            case "termsandpolicies":
                $event = "TERMS AND POLICIES";
            
                break;
            
            case "themes":
                $event = "THEMES";
                
                // check for actions from the url
                if($_GET["action"] == "load")
                {
                    // load the theme
                    $form -> loadTheme($_GET["theme"]);
                }
                
                break;
                
            case "contactus":
                $event = "CONTACT US";
                
                break;
                            
            default:
                $event = "WELCOME BANNER";
                
                break;
        }
	
	/**
	 * END OF URL EVENTS
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
      
        // determine which page to display
        switch($event)
        {                
            case "THEMES":
                // display themes
                $form -> displayThemes();
                break;
            
            case "WELCOME BANNER":
                // display the welcome banner page
                $form -> displayInfo("WELCOME BANNER");
                
                break;
                
            case "CONTACT US":
                // display the contact us page
                $form -> displayInfo("CONTACT US");
                
                break;
                
            case "TERMS AND POLICIES":
                // display the terms and policies page
                $form -> displayInfo("TERMS AND POLICIES");
                
                break;
        }
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>