<?php
	/**
	 * Filename : archives.php
	 * Description : page for managing article archives
	 * Date Created : December 28,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the Archives Class Form
	require_once("../includes/ArchivesForm.php");
	$form = new ArchivesForm();
	
	/**
	 * URL EVENTS:
	 *  disabled
	 *  enabled
	 */
    
        switch($_GET["event"])
        {
            case "disabled":
                $event = "DISABLED";
                
                // check for actions from the URL
                if(isset($_GET["year"]))
                {
                    // change the event to VIEW YEAR
                    $event = "VIEW YEAR";
                    
                    // check for actions in the URL
                    if($_GET["action"] == "enableyear")
                    {
                        // enable all issues on that year
                        $form -> enableArchivesByYear($_GET["year"]);
                        
                        // change the event back to first
                        $event = "DISABLED";
                    }
                    else if($_GET["action"] == "removeyear")
                    {
                        // remove archived articles in the selected year
                        $form -> removeArchivesByYear($_GET["year"]);
                    }
                    else
                    {
                        // check for actions in the url
                        if($_GET["action"] == "enableissue")
                        {
                            // enable the articles on that issue
                            $form -> enableArchivesByIssue($_GET["issue"], $_GET["year"]);
                        }
                        else if($_GET["action"] == "removeissue")
                        {
                            // remove all archives articles in that issue
                            $form -> removeArchivesByIssue($_GET["issue"], $_GET["year"]);
                        }
                        else
                        {
                            // check if an issue under a year is being viewed
                            if(isset($_GET["issue"]))
                            {
                                // change the event to VIEW ISSUE
                                $event = "VIEW ISSUE";
                                
                                // check for actions
                                if($_GET["action"] == "enable")
                                {
                                    // enable the selected archive
                                    $form -> enableArchive($_GET["archive"]);
                                }
                                else if($_GET["action"] == "remove")
                                {
                                    // delete the selected archive
                                    $form -> removeArchive($_GET["archive"]);
                                }
                            }
                        }
                    }
                }
                
                break;
                
            default:
                $event = "ENABLED";
                
                // check which is being viewed
                if(isset($_GET["year"]))
                {
                    // change the event to VIEW YEAR
                    $event = "VIEW YEAR";
                    
                    // check for actions in the URL
                    if($_GET["action"] == "disableyear")
                    {
                        // disable the archived articles on that year
                        $form -> disableArchivesByYear($_GET["year"]);
                        
                        // then change the event back to default
                        $event = "ENABLED";
                    }
                    else
                    {                    
                        // check for actions on the issue
                        if($_GET["action"] == "disableissue")
                        {
                            // disable the archived articles on that issue
                            $form -> disableArchivesByIssue($_GET["issue"], $_GET["year"]);
                        }
                        else
                        {                    
                            // check if an issue under a year is being viewed
                            if(isset($_GET["issue"]))
                            {
                                // change the event to VIEW ISSUE
                                $event = "VIEW ISSUE";
                                
                                // check for actions
                                if($_GET["action"] == "disable")
                                {
                                    // disable the selected archive
                                    $form -> disableArchive($_GET["archive"]);
                                }
                            }
                        }
                    }
                }
                
                break;
        }
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *  enableArchive
	 *  disableArchive
	 *  enableArchivesByIssue
	 *  disableArchivesByIssue
	 *  enableArchivesByYear
	 *  disableArchivesByYear
	 *  removeArchivesByYear
	 *  removeArchivesByIssue
	 *  removeArchive
	 */
    
        // ENABLE ARCHIVE button
        if(isset($_POST["enableArchive"]))
        {
            // get all checked archives and enable them
            for($i=0; $i<count($_POST["archiveIds"]); $i++)
            {
                $form -> enableArchive($_POST["archiveIds"][$i]);
            }
        }
        
        // DISABLE ARCHIVE button
        if(isset($_POST["disableArchive"]))
        {
            // get all checked archvies and disable them
            for($i=0; $i<count($_POST["archiveIds"]); $i++)
            {
                $form -> disableArchive($_POST["archiveIds"][$i]);
            }
        }
        
        // ENABLE ARCHIVE by issue button
        if(isset($_POST["enableArchivesByIssue"]))
        {
            // get all checked issues and enable them
            for($i=0; $i<count($_POST["issues"]); $i++)
            {
                $form -> enableArchivesByIssue($_POST["issues"][$i], $_GET["year"]);
            }
        }
        
        // DISABLE ARCHIVE by issue button
        if(isset($_POST["disableArchivesByIssue"]))
        {
            // get all checked issues and disables them
            for($i=0; $i<count($_POST["issues"]); $i++)
            {
                $form -> disableArchivesByIssue($_POST["issues"][$i], $_GET["year"]);
            }
        }
        
        // ENABLE ARCHIVE by issue button
        if(isset($_POST["enableArchivesByIssue"]))
        {
            // get all checked issues and disables them
            for($i=0; $i<count($_POST["issues"]); $i++)
            {
                $form -> enableArchivesByIssue($_POST["issues"][$i], $_GET["year"]);
            }
        }
        
        // DISABLE ARCHIVE by year button
        if(isset($_POST["disableArchivesByYear"]))
        {
            // get all checked years and disables them
            for($i=0; $i<count($_POST["years"]); $i++)
            {
                $form -> disableArchivesByYear($_POST["years"][$i]);
            }
        }
        
        // ENABLE ARCHIVE by year button
        if(isset($_POST["enableArchivesByYear"]))
        {
            // get all checked years and enables them
            for($i=0; $i<count($_POST["years"]); $i++)
            {
                $form -> enableArchivesByYear($_POST["years"][$i]);
            }
        }
        
        // REMOVE ARCHIVES by year
        if(isset($_POST["removeArchivesByYear"]))
        {
            // get all the checked years and delete all the archives inside
            for($i=0; $i<count($_POST["years"]); $i++)
            {
                $form -> removeArchivesByYear($_POST["years"][$i]);
            }
        }
        
        // REMOVE ARCHIVES by issue
        if(isset($_POST["removeArchivesByIssue"]))
        {
            // get all the checked issues and delete all the archives inside
            for($i=0; $i<count($_POST["issues"]); $i++)
            {
                $form -> removeArchivesByIssue($_POST["issues"][$i], $_GET["year"]);
            }
        }
        
        // REMOVE ARCHIVE
        if(isset($_POST["removeArchive"]))
        {
            // get all the checked archives and delete them
            for($i=0; $i<count($_POST["archiveIds"]); $i++)
            {
                $form -> removeArchive($_POST["archiveIds"][$i]);
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
        
        // check the event
        switch($event)
        {
            case "VIEW ISSUE":
                // display all articles inside a selected issue within a selected year
                $form -> displayArchives(strtoupper($_GET["event"]), $_GET["year"], $_GET["issue"]);
                
                break;
                
            case "VIEW YEAR":
                // display all issues that are inside a particular selected year
                $form -> displayArchivesByIssue(strtoupper($_GET["event"]), $_GET["year"]);
                
                break;
            
            case "DISABLED":
                // display all disabled archives by year
                $form -> displayArchivesByYear("DISABLED");
                
                break;
                
            case "ENABLED":
                // display all enabled archives by year
                $form -> displayArchivesByYear("ENABLED");
            
                break;
        }
        
        echo "</div>";
    ?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>