<?php
	/**
	 * Filename : accountscompose.php
	 * Description : page for creating and editing account information
	 * Date Created : November 30,2007
	 * Author : Argus Team
	 */
	 
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the accounts form class
	require_once("../includes/AccountsForm.php");
	$form = new AccountsForm();
	
	/**
	 * URL EVENTS
	 *	edit
	 */
	 	
		switch($_GET["event"])
		{
			case "edit":
				// if in edit mode, it is expected that there is an ACCOUNT ID in the URL which is to be edited
				// check the ID if it exists in the database
				$accountQuery = mysql_query("SELECT id_number, username, password, name, position, email, status FROM argus_accounts WHERE account_id = '".$_GET["account"]."'") or die(mysql_error());
				
				if(mysql_num_rows($accountQuery) > 0)
				{
					// set the appropriate event and set the properties of the account which is to be displayed for editing
					$event = "EDIT";
					$idNumber = mysql_result($accountQuery,0,"id_number");
					$username = mysql_result($accountQuery,0,"username");
					$password = mysql_result($accountQuery,0,"password");
					$retypedPassword = mysql_result($accountQuery,0,"password");
					$name = mysql_result($accountQuery,0,"name");
					$position = mysql_result($accountQuery,0,"position");
					$email = mysql_result($accountQuery,0,"email");
					$status = mysql_result($accountQuery,0,"status");
				}
				else
				{
					// if the ID does not exist in the database, change the event back to default
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
     *  fillValues
	 *	create
	 *	update
	 */
     
        // FILL VALUES button
        if(isset($_POST["fillValues"]))
        {
            // get the input from the user
            $idNumber = $_POST["idNumber"];
            
            // get the user infos
            $userInfo = $form -> getAccountInfo($idNumber);
            
            // set the attributes to be displayed
            $username = $_POST["idNumber"];
            $firstName = $userInfo["firstName"];
            $lastName = $userInfo["lastName"];
            $middleInitial = $userInfo["middleInitial"];
            $password = $_POST["idNumber"];
            $retypedPassword = $_POST["idNumber"];
        }
	 	
		// CREATE button
		if(isset($_POST["create"]))
		{
			// get all the values entered by the user
			$idNumber = $_POST["idNumber"];
			$username = $_POST["username"];
			$firstName =$_POST["firstName"];
			$lastName = $_POST["lastName"];
			$middleInitial = $_POST["middleInitial"];
			$password = $_POST["password"];
			$retypedPassword = $_POST["retypedPassword"];
			$email = $_POST["email"];
			$position = $_POST["position"];
			$status = $_POST["status"];
			
			// arrange the name in a FIRST NAME, LASTNAME, MIDDLE INITIAL
			$name = $firstName." ".$lastName." ".$middleInitial;
			
			// add the account
			$result = $form -> addAccount($idNumber, $username, $name, $password, $retypedPassword, $email, $position, $status);
			
			// check result
			if($result == true)
			{
				// set a message that the registration is successful
				$successMessage = "The account has been successfully created";
			}
			else
			{
				// get the errors that was encountered during the adding of accounts
				$errors = $form -> getErrors();
			}
		}
		
		//UPDATE Button
		if(isset($_POST["update"]))
		{
			// get all the inputs from the user that is only enabled to be updated
			$username = $_POST["username"];
			$password = $_POST["password"];
			$retypedPassword = $_POST["retypedPassword"];
			$email = $_POST["email"];
			$status = $_POST["status"];
			
			// update the account
			$result = $form -> updateAccount($_GET["account"], $username, $password, $retypedPassword, $email, $status);
			
			// check for result
			if($result == true)
			{
				// set a message that the update is sucessful
				$successMessage = "The account has been successfully updated";
			}
			else
			{
				// get the errors that was encountered during the updating of accounts
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
		
		// set where the link came from if it's from the ENABLED(contributor/member) or DISABLED accounts page
		// so as to set the correct menu and page form after a transaction has happened.
		// then set the title for the form
        if($event == "EDIT")
        {
            if($status == "ENABLED")
            {
                // title for the contributors and members that are enabled
                echo "<h3><a href='accounts.php?event=".strtolower($position)."'>".ucfirst(strtolower($position))."</a> &raquo; ".$name."</h3>";
            }
            else
            {
                // title  for contributors and members that are disabled
                echo "<h3><a href='accounts.php?event=".strtolower($status)."'>Disabled</a> &raquo; ".$name."</h3>";
            }
            
            // set the form
            echo "<div class='bg1'>";
            echo "<form method='post' action='".$_SERVER["PHP_SELF"]."?event=".$_GET["event"]."&account=".$_GET["account"]."'>";
        }
        else
        {
			// default title and form. Form when COMPOSING a new account
			echo "<h3>Create</h3>";
            echo "<div class='bg1'>";
			echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
		}

		// display the errors that were encountered during the ADDING/UPDATING of accounts
		if((isset($_POST["create"]) || isset($_POST["update"])) && $result == false)
		{
			echo "<p><font color='red'>";
		
			// display id error
			if(!empty($errors["id"]))
			{
				echo $errors["id"]."<br />";
			}
			
			// display username error
			if(!empty($errors["username"]))
			{
				echo $errors["username"]."<br />";
			}
			
			// display password error
			if(!empty($errors["password"]))
			{
				echo $errors["password"]."<br />";
			}
			
			// display email error
			if(!empty($errors["email"]))
			{
				echo $errors["email"]."<br />";
			}
			
			echo "</font></p>";
		}
		// display the successful ADDING/CREATION of account
		else if((isset($_POST["create"]) || isset($_POST["update"])) && $result == true)
		{
			echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
			
			if(isset($_POST["create"]))
			{
				// clear the values if only creating a new account
				$idNumber = null;
				$username = null;
				$firstName = null;
				$lastName = null;
				$middleInitial = null;
				$password = null;
				$retypedPassword = null;
				$email = null;
				$position = null;
				$status = null;
			}
		}
	?>
		<!-- Form tag is set above -->
			<p>
                Account Information
			</p>
			<!-- saint louis university id -->
			<p id='box'>
				<b>Saint Louis University Id Number</b><br />
				<?php
					// if in edit mode, disble the editing of ID number. Id numbers are not allowed to be edited
					if($event == "EDIT")
					{
						echo "<input type='text' id='textbox' name='idNumber' disabled='disabled' value='".$idNumber."' /><br />";
						echo "You are not allowed to edit this acccount's ID number.";
					}
					else
					{
						echo "<input type='text' id='textbox' name='idNumber' value='".$idNumber."' /><br />";
					}
				?>
			</p>
            <?php
                if($event != "EDIT")
                {
                    // display the auto generate values button
                    echo "<p align='right'><input type='submit' id='submit1' value='Fill Values' name='fillValues'></p>";
                }
            ?>
			<!-- Username -->
			<p id='box'>
				<b>Username</b><br />
				<input type='text' id='textbox' name='username' value='<?php echo $username ?>' />
			</p>
			<!-- Name -->
			<p id='box'>
				<?php
					// if in edit mode, it is expected that the name is already arranged and not allowed to be edited
					if($event == "EDIT")
					{
						echo "Name<br />";
						echo "<input type='text' id='textbox' value='".$name."' disabled='disabled'><br />";
						echo "You are not allowed to edit this account's name.";
					}
					else
					{
						echo "<b>First Name</b><br />";
						echo "<input type='text' id='textbox' name='firstName' value='".$firstName."' /><br />";
                        echo "<b>Middle Initial</b><br />";
                        echo "<input type='text' id='textbox' maxlength='1' name='middleInitial' value='".$middleInitial."' /><br />";
						echo "<b>Last Name</b><br />";
						echo "<input type='text' id='textbox' name='lastName' value='".$lastName."' /><br />";
					}
				?>
			</p>
			<!-- Password -->
			<p id='box'>
				<b>Password</b><br />
				<input type='password' id='textbox' name='password' value='<?php echo $password ?>'  /><br />
				Password should be 5 - 15 characters long.<br />
				<b>Retype Password</b><br />
				<input type='password' id='textbox' name='retypedPassword' value='<?php echo $retypedPassword ?>' /><br />
			</p>
			<!-- Email -->
			<p id='box'>
				<b>Current Email</b><br />
				<input type='text' id='textbox' name='email' value='<?php echo $email ?>' /><br />
				Please ensure that the spam filter of this email address will allow email from www.argus.com.
			</p>
			<!-- position -->
			<p id='box'>
				<?php
					// check which position is selected
					echo "<b>Position</b><br />";
					
					//  if in edit mode, positions are not allowed to be edited
					if($event == "EDIT")
					{
						echo "<select name='position' id='textbox' disabled='disabled'>";
					}
					else
					{
						echo "<select name='position' id='textbox'>";
					}
					
					if($position == "MEMBER")
					{
						// select the MEMBER as a current position
						echo "<option value='MEMBER' selected='selected'>Member</option>";
					}
					else
					{
						echo "<option value='MEMBER'>Member</option>";
					}
					
					if($position == "CONTRIBUTOR")
					{
						// select the CONTRUBUTOR as a current position
						echo "<option value='CONTRIBUTOR' selected='selected'>Contributor</option>";
					}
					else
					{
						echo "<option value='CONTRIBUTOR'>Contributor</option>";
					}
					
					echo "</select>";
				?>
			</p>
			<!-- status -->
			<p id='box'>
				<?php
					echo "<b>Status</b><br />";
					echo "<select name='status' id='textbox'>";
					
					if($status == "ENABLED")
					{
						// select ENABLED as current status
						echo "<option value='ENABLED' selected='selected'>Enabled</option>";
					}
					else
					{
						echo "<option value='ENABLED'>Enabled</option>";
					}
					
					if($status == "DISABLED")
					{
						// select DISABLED as current status
						echo "<option value='DISABLED' selected='selected'>Disabled</option>";
					}
					else
					{
						echo "<option value='DISABLED'>Disabled</option>";
					}
					
					echo "</select>";
				?>
			</p>
			<!-- Buttons -->
			<p align='center'>
				<?php
					// display the appropriate button on an event
					if($event == "EDIT")
					{
						// button for EDIT mode
						echo "<input type='submit' id='submit2' value='update' name='update' />";
					}
					else
					{
						// button for COMPOSE mode
						echo "<input type='submit' id='submit2' value='create' name='create' />";
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