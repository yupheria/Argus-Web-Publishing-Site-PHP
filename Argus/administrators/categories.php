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
	
	// import the categories form class
	require_once("../includes/CategoriesForm.php");
	$form = new CategoriesForm();
	
	/**
	 * URL EVENTS:
	 *	disabled
	 *	enabled
	 */
	
		switch($_GET["event"])
		{	
			case "statistics":
				$event = "STATISTICS";
				
				// check for actions in the URL
				switch($_GET["action"])
				{
					case "disable":
						// disable the category
						$form -> disableCategory($_GET["category"]);
						
						break;
						
					case "enable":
						// enable the category
						$form -> enableCategory($_GET["category"]);
						
						break;
				}
				
				break;
			
			case "disabled":
				$event = "DISABLED";
				
				// check if there is an action from the URL
				if($_GET["action"] == "enable")
				{
					// enable the category
					$form -> enableCategory($_GET["category"]);
				}
				else if($_GET["action"] == "delete")
				{
					// delete the category
					$form -> deleteCategory($_GET["category"]);
				}
				
				break;
				
			default:
				$event = "ENABLED";
				
				// check if there is an action from the URL
				if($_GET["action"] == "disable")
				{
					// disable the category
					$form -> disableCategory($_GET["category"]);
				}
				else if($_GET["action"] == "moveup" || $_GET["action"] == "movedown")
				{
					// move the category up or down
					$form -> moveCategory($_GET["category"], strtoupper($_GET["action"]));
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
	 *	update
	 */
	
		// DISABLE button
		if(isset($_POST["disable"]))
		{
			// get all checked category ids and disable them
			for($i=0; $i<count($_POST["categoryIds"]); $i++)
			{
				// disable the category
				$form -> disableCategory($_POST["categoryIds"][$i]);
			}
		}
		
		// ENABLE button
		if(isset($_POST["enable"]))
		{
			// get all checked category ids and enables them
			for($i=0; $i<count($_POST["categoryIds"]); $i++)
			{
				// enable the category
				$form -> enableCategory($_POST["categoryIds"][$i]);
			}
		}
		
		// DELETE button
		if(isset($_POST["delete"]))
		{
			// get all checked category ids and deletes them
			for($i=0; $i<count($_POST["categoryIds"]); $i++)
			{
				// delete the category
				$form -> deleteCategory($_POST["categoryIds"][$i]);
			}
		}
		
		// UPDATE button
		if(isset($_POST["update"]))
		{
			// get all the positions of that were set and update there positions
			$form -> updatePositions($_POST["positions"]);
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
		
		// determine which event to display
		switch($event)
		{
			case "STATISTICS":
				// display the statistics of the category
				$form -> displayCategoryStatistics($_GET["category"]);
				
				break;
			
			case "DISABLED":
				// display all DISABLED categories
				$form -> displayCategories("DISABLED");
				
				break;
				
			case "ENABLED":
				// display all ENABLED categories
				$form -> displayCategories("ENABLED");
				
				break;
		}
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>