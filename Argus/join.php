<?php
	/**
	 * Filename	: join.php
	 * Description : Login-in and Registration page
	 * Date Created : November 29,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("includes/Page.php");
	$page = new Page($_COOKIE["argus"], null);
    
    // import the image verification class which will be used whenever someone is going to register
    require_once("includes/class_libraries/ImageVerification.php");
    $imageVerification = new ImageVerification();
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *	join button
	 *	login button
	 */
	 	
		// JOIN Button
		if(isset($_POST["join"]))
		{
			// import the registration form class
			require_once("includes/RegistrationForm.php");
			$form = new RegistrationForm();
			
			// get all the values entered by the user for registration
			$idNumber = $_POST["idNumber"];
			$username = $_POST["username"];
			$password = $_POST["password"];
			$retypedPassword = $_POST["retypedPassword"];
			$email = $_POST["email"];
            $imageValue = $_POST["imageValue"];
            $realImageValue = $_POST["realImageValue"];
            $firstName = $_POST["firstName"];
            $lastName = $_POST["lastName"];
            $middleInitial = $_POST["middleInitial"];
			
			// fix the name in a FIRST NAME -> LAST NAME -> MIDDLE INITIAL
			$name = $firstName." ".$lastName." ".$middleInitial;
			
			// register the account and set position to MEMBER since this is a member registration page
			$result = $form -> registerAccount($idNumber, $username, $name, $password, $retypedPassword, $email, "MEMBER", $imageValue, $realImageValue);
			
			// create a message from the result
			if($result == true)
			{
				// create a message that the account has been registered
				$successMessage = "The account '".$username."' has been successfully created";
			}
			else
			{
				// get the error messages that was created during the registration which is to be displayed at the bottom
				$errors = $form -> getErrors();
			}
		}
		
	/**
	 * END OF BUTTON EVENT TRIGGERS
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
        
		// display the tools for NON-MEMBERS
		$page -> displaySearchBar();
		$page -> displayCategoryBar();
        
        echo "</div>";
	?>
	<!-- left side column: contains sub options and articles and where manipulation of tools occurs -->
	<?php
        $page -> displayDivCode("LEFT");
    ?>
		<!-- display the registration page for NON-MEMBERS -->
		<h3 align='center'>Registration</h3>
		<div class='bg1'>
			<?php
				// display errors for registration
				if(isset($_POST["join"]) && $result == false)
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
                    
                    // display image error
                    if(!empty($errors["image"]))
                    {
                        echo $errors["image"]."<br />";
                    }
					
					echo "</font></p>";
				}
				// display successful registratrion
				else if(isset($_POST["join"]) && $result == true)
				{
					echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
					
					// clear the textboxes current values
					$idNumber = null;
					$username = null;
					$firstName = null;
					$lastName = null;
					$middleInitial = null;
					$password = null;
					$retypedPassword = null;
					$email = null;
				}
			?>
			<p>By joining the <em>Argus Online Publication</em>, you will be able to contribute ideas and/or comment on published articles. Just fill in the fields below and be a member of the Argus community. Check out our <a href='#'><strong>Terms and Policies</strong></a> if you have privacy issues, or consider writing to us. Email <a href='mailto:admin@argus.com'>admin@argus.com</a> for questions, problems, comments, or suggestions.
			</p>
			<form method="post" action="<?php $_SERVER["PHP_SELF"]; ?>">
				<!-- Saint louis ID number -->
				<p id="box">
					<b>Saint Louis University Id Number</b><br>
					<input type="text" id="textbox" name="idNumber" value="<?php echo $idNumber; ?>"><br>
				</p>
				<!-- Username -->
				<p id="box">
					<b>Username</b><br />
					<input type="text" id="textbox" name="username" value="<?php echo $username; ?>" /><br>
					<i>Your Username will identify you when you're on Argus.com. Your username must be at least 5 up to 15 characters long.</i>
				</p>
				<!-- Firstname, Lastname, Middle Initial -->
				<p id="box">
					<b>First Name</b><br />
					<input type="text" id="textbox" name="firstName" value="<?php echo $firstName; ?>" /><br>
					<i>Enter the First Name that you have used when you enrolled at Saint Louis University. Nick names are not allowed.</i><br>
					<b>Last Name</b><br>
					<input type="text" id="textbox" name="lastName" value="<?php echo $lastName; ?>" /><br>
					<b>Middle Initial</b><br>
					<input type="text" id="textbox" name="middleInitial" maxlength="1" value="<?php echo $middleInitial; ?>"><br>
					<i>Leave blank if you do not have a middle initial.</i>
				</p>
				<!-- password, retype password -->
				<p id="box">
					<b>Password</b><br />
					<input type="password" id="textbox" name="password" value="<?php echo $password ?>" /><br>
					<i>Password should be 5 - 15 characters long.</i><br />
					<b>Re-type password</b><br />
					<input type="password" id="textbox" name="retypedPassword" value="<?php echo $retypedPassword ?>" /><br>
				</p>
				<!-- email address -->
				<p id="box">
					<b>Current Email</b><br />
					<input type="text" id="textbox" name="email" value="<?php echo $email ?>" /><br>
					<i>We will never share your email address with anyone without your permission. Please ensure that your spam filters will allow email from www.argus.com.</i>
				</p>
                <!-- image verification -->
                <p id="box">
                    <b>Image Verification</b><br />
                    <?php
                        // create a random image for the user to see for verification
                        $imageVerification -> createImage();
                        
                        // after creating the random image, get the REAL VALUE of the image which is to be used for validation
                        $realImageValue = $imageVerification -> getImageValue();
                        
                        // after the image is created, display the image
                        echo "<img src='miscs/images/Default/imageverification.jpg' border='1'>";
                    ?><br />
                    <input type="text" id="textbox" name="imageValue">
                    <input type="hidden" name="realImageValue" value="<?php echo $realImageValue ?>">
                </p>
				<p>By clicking on JOIN, you must have read and agreed with the terms and policies provided by the online publication and appropriate action will occur if violated. Please read the <a href='index.php?event=termsandpolicies'><strong>Terms and Policies</strong></a> for more information.
				</p>
				<p align="center">
					<input type='submit' id="submit2" value='join' name='join'/>
				</p>
	  		</form>
		</div>
	</div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>