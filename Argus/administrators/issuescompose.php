<?php
	/**
	 * Filename : issuescompose.php
	 * Description : page for creating and editing issues
	 * Date Created : December 2,2007
	 * Author : Argus Team
	 */
	 
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the issues form
	require_once("../includes/IssuesForm.php");
	$form = new IssuesForm();
	
	/**
	 * URL EVENTS
	 *	edit
	 */
	 	
		switch($_GET["event"])
		{
			case "edit":
				$event = "EDIT";
				
				// if in edit mode, get the attributes of the issue that is to be edited
				$issueQuery = mysql_query("SELECT name, description, date_created, date_publish, status FROM argus_issues WHERE issue_id = '".$_GET["issue"]."'") or die(mysql_error());
				
				// check if there is an issue queried from the database
				if(mysql_num_rows($issueQuery) == 0)
				{
					// set back the EVENT to default if no issue was queried
					$event = "COMPOSE";
				}
				else
				{
					// set the attributes
					$name = mysql_result($issueQuery,0,"name");
					$description = mysql_result($issueQuery,0,"description");
					$dateCreated = mysql_result($issueQuery,0,"date_created");
					$status = mysql_result($issueQuery,0,"status");
				}
				
				break;
				
			default:
				$event = "COMPOSE";
				
				break;
		}
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS
	 *	create
	 *	update
	 */
	
		// CREATE button
		if(isset($_POST["create"]))
		{
			// get all the inputs from the user
			$name = $_POST["name"];
			$description = $_POST["description"];
			$status = $_POST["status"];
			
			// add the issue
			$result = $form -> addIssue($name, $description, $status);
			
			// check the result
			if($result == true)
			{
				// set a successful message which is to be displayed below
				$successMessage = "Saved";
			}
			else
			{
				// get the errors which will be displayed below
				$errors = $form -> getErrors();
			}
		}
		
		// UPDATE button
		if(isset($_POST["update"]))
		{
			// get all the inputs from the user
			$name = $_POST["name"];
			$description = $_POST["description"];
			$status = $_POST["status"];
            
            // check if the status is empty or not
            if(empty($status))
            {
                // if the status is empty, then that means that the select box is disabled
                // if the select box is disables, then that means that the issue being edited is the current published issue
                // set the status as published
                $status = "PUBLISHED";
            }
			
			// update the issue
			$result = $form -> updateIssue($_GET["issue"], $name, $description, $status);
			
			// check the result
			if($result == true)
			{
				// set a successful message which is to be displayed below
				$successMessage = "Saved";
			}
			else
			{
				// get the errors which will be displayed below
				$errors = $form -> getErrors();
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
        
        // set the form and title
		if($event == "EDIT")
        {
            // title for enabled and disabled issues
            echo "
            <h3><a href='issues.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo; ".$name."</h3>
            <div class='bg1'>
            <form method='post' action='".$_SERVER['PHP_SELF']."?event=edit&issue=".$_GET["issue"]."'>";
        }
        else
        {
            // display the default form and title which is compose mode
            echo "
            <h3>Create</h3>
            <div class='bg1'>
            <form method='post' action='".$_SERVER['PHP_SELF']."'>";
        }

		// display the errors during the adding and editing of issues
		if((isset($_POST["create"]) || isset($_POST["update"])) && $result == false)
		{
			echo "<p><font color='red'>";
			
			// display errors for name
			if($errors["name"] != null)
			{
				echo $errors["name"]."<br>";
			}
			
			// display errors for description
			if($errors["description"] != null)
			{
				echo $errors["description"]."<br />";
			}
			
			echo "</font></p>";
		}
		// display successful message during the adding editing of issues
		else if((isset($_POST["create"]) || isset($_POST["update"])) && $result == true)
		{
			echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
			
			// clear the values for adding of accounts only
			if(isset($_POST["create"]))
			{
				$name = null;
				$description = null;
			}
		}
		?>
		<!-- form tag is defined above -->
			<p>Issue Information</p>
			<p id='box'>
				<b>Name</b><br>
				<input type='text' id='textbox' value='<?php echo $name ?>' name='name'><br>
			</p>
			<!-- description-->
			<p id='box'>
				<b>Description</b><br />
				<input type='text' id='textbox' value='<?php echo $description ?>' name='description' /><br />
			</p>
			<p id='box'>
				<b>Status</b><br>
				<?php
                    // if the current status of the issue is PUBLISHED, the user is not allowed to play with the status
                    if($status != "PUBLISHED")
                    {
                        echo "<select name='status' id='textbox' name='status'>";
                    }
                    else
                    {
                        echo "<select id='textbox' disabled='disabled'>";
                    }
                    
					// set the default selected value
					if($status == "ENABLED")
					{
						// set this as the default selected value
						echo "<option value='ENABLED' selected='selected'>Enabled</option>";
					}
					else
					{
						echo "<option value='ENABLED'>Enabled</option>";
					}
					
					if($status == "DISABLED")
					{
						// set this as the default selected value
						echo "<option value='DISABLED' selected='selected'>Disabled</option>";
					}
					else
					{
						echo "<option value='DISABLED'>Disabled</option>";
					}
				?>
				</select>
			</p>
			<p align='center'>
				<?php
					// display the appropriate button depending on the event
					if($event == "COMPOSE") {
						// button for COMPOSE mode
						echo "<input type='submit' id='submit2' value='create' name='create'>";
					}
					else
					{
						// button for EDIT mode
						echo "<input type='submit' id='submit2' value='update' name='update'>";
					}
				?>
			</p>
        </form>
	</div>
	</div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>