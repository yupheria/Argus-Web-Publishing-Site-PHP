<?php
	/**
	 * Filename : categoriescompose.php
	 * Description : page for creating/editing of categories
	 * Date Created : December 1,2007
	 * Author : Argus Team
	 */
     
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the accounts form class
	require_once("../includes/CategoriesForm.php");
	$form = new CategoriesForm();
	
	/**
	 * URL EVENTS
	 *	edit
	 * 	compose
	 */
	
		switch($_GET["event"])
		{
			case "edit":
				// if in edit mode, it is expected that there is a category id from the URL
				// validate the category if it exists or not
				$categoryQuery = mysql_query("SELECT name, status FROM argus_categories WHERE category_id = '".$_GET["category"]."'") or die(mysql_error());
				
				// check if there is a category queried from the database
				if(mysql_num_rows($categoryQuery) > 0)
				{
					$event = "EDIT";
					
					// set the properties
					$name = mysql_result($categoryQuery,0,"name");
					$status = mysql_result($categoryQuery,0,"status");
				}
				else
				{
					// if no category exists, change the event back to default
					$event = "COMPOSE";
				}
				
				break;
				
			default:
				$event = "COMPOSE";
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
			
			// add the category
			$result = $form -> addCategory($name, $description, $status);
			
			// check the result
			if($result == true)
			{
				// set a success message which is to be displayed below
				$successMessage = "Saved";
			}
			else
			{
				// get the errors which is to be displayed below
				$errors = $form -> getErrors();
			}
		}
		
		// UPDATE button
		if(isset($_POST["update"]))
		{
			// get all inputs from the user
			$name = $_POST["name"];
			$description = $_POST["description"];
			$status = $_POST["status"];
			
			// update the category
			$result = $form -> updateCategory($_GET["category"], $name, $description, $status);
			
			// check the result
			if($result == true)
			{
				// set a success message which is to be displayed below
				$successMessage = "Saved";
			}
			else
			{
				// get the errors which is to be displayed below
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
        
        if($event == "EDIT")
        {
            // title and form
            echo "
            <h3><a href='categories.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo; ".$name."</h3>
            <div class='bg1'>
            <form method='post' action='".$_SERVER['PHP_SELF']."?event=edit&category=".$_GET["category"]."'>";
        }
		else
        {	
			// default title and form for compose
			echo "
            <h3>Create</h3>
            <div class='bg1'>
			<form method='post' action='".$_SERVER['PHP_SELF']."'>";
		}
		
		// display the errors committed during the adding and editing of category
		if((isset($_POST["create"]) || isset($_POST["update"])) && $result == false)
		{
			echo "<p><font color='red'>";
			
			// display the name error
			if($errors["name"] != null)
			{
				echo $errors["name"]."<br>";
			}
			
			// display the description error
			if($errors["description"] != null)
			{
				echo $errors["description"]."<br />";
			}
			
			echo "</font></p>";
		}
		// display the successful update or add of category
		else if((isset($_POST["create"]) || isset($_POST["update"])) && $result == true)
		{
			echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
			
			// clear only the values if in create mode
			if(isset($_POST["create"]))
			{
				$name = null;
				$description = null;
			}
		}
	?>
		<!-- form tag defined above -->
			<p>
                Category Information
			</p>
			<!-- category name -->
			<p id="box">
				<b>Category Name</b><br>
				<input type="text" id="textbox" name="name" value="<?php echo $name; ?>"/><br>
			</p>
			<!-- description -->
			<p id="box">
				<b>Description</b><br />
				<input type="text" id="textbox" name="description" maxlength="255" value="<?php echo $description; ?>"/><br />
			</p>
			<!-- status -->
			<p id="box">
				<b>Status</b><br>
				<select name="status" id="textbox">
					<?php
						// set the default selected status
						if($status == "ENABLED")
						{
							echo "<option value='ENABLED' selected='selected'>Enabled</option>";
						}
						else
						{
							echo "<option value='ENABLED'>Enabled</option>";
						}
						
						if($status == "DISABLED")
						{
							echo "<option value='DISABLED' selected='selected'>Disabled</option>";	
						}
						else
						{
							echo "<option value='DISABLED'>Disabled</option>";
						}
					?>
				</select>
			</p>
			<!-- buttons -->
			<p align='center'>
				<?php
					// set the appropriate buttons
					if($event == "EDIT")
					{
						// button when editing a category
						echo "<input type='submit' id='submit2' value='update' name='update'>";
					}
					else
					{
						// button when creating a new category
						echo "<input type='submit' id='submit2' value='create' name='create'>";
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