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
	
	// import the issues form class
	require_once("../includes/IssuesForm.php");
	$form = new IssuesForm();
	
	/**
	 * URL EVENTS:
	 *	statistics
	 *	disabled
	 *	enabled
	 */
	 	
		switch($_GET["event"])
		{
			case "statistics":
				// display the statistic of an accounts
				$event = "STATISTICS";
				
				break;
			
			case "disabled":
				$event = "DISABLED";
				
				// check if there are actions in the url
				if($_GET["action"] == "enable")
				{
					// enable the issue
					$form -> enableIssue($_GET["issue"]);
				}
				else if($_GET["action"] == "delete")
				{
					// delete the issue
					$form -> deleteIssue($_GET["issue"]);
				}
				
				break;
			
			default:
				$event = "ENABLED";
				
				// check if there are actions in the URL
				if($_GET["action"] == "disable")
				{
					// disable the issue
					$form -> disableIssue($_GET["issue"]);
				}
			
				break;
		}
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *	disable
	 *	enable
	 *	delete
	 */
	
		// DISABLE button
		if(isset($_POST["disable"]))
		{
			// get all checked issues and disables them
			for($i=0; $i<count($_POST["issueIds"]); $i++)
			{
				$form -> disableIssue($_POST["issueIds"][$i]);
			}
		}
		
		// ENABLE button
		if(isset($_POST["enable"]))
		{
			// get all checked issues and enables them
			for($i=0; $i<count($_POST["issueIds"]); $i++)
			{
				$form -> enableIssue($_POST["issueIds"][$i]);
			}
		}
		
		// DELETE button
		if(isset($_POST["delete"]))
		{
			// get all checked issues and delete them
			for($i=0; $i<count($_POST["issueIds"]); $i++)
			{
				$form -> deleteIssue($_POST["issueIds"][$i]);
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
		
		// determine which event is to be displayed
		switch($event)
		{
			case "STATISTICS":
				// display the statistics of a specific issue
				$form -> displayIssueStatistics($_GET["issue"]);
				
				break;
			
			case "DISABLED":
				// display all disabled issues
				$form -> displayIssues("DISABLED");
				
				break;
			
			case "ENABLED":
				// display all enabled issues
				$form -> displayIssues("ENABLED");
				
				break;
		}
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>