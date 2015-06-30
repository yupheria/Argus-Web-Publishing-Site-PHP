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
	 	
		// LOGIN Button
		if(isset($_POST["login"]))
		{
			// import the login form class
			require_once("includes/LoginForm.php");
			$form = new LoginForm();
			
			// get all the inputs from the user for login
			$username = $_POST["username"];
			$password = $_POST["password"];
			
			// login the account
			$result = $form -> loginAccount($username, $password);
			
			// check the result
			if($result == true)
			{
				// if successful login, it is assumed that the cookie has already been created
				// redirect the user to the index.php page and let that page do the validation of the cookie
				// and redirects the user to the pages that the account has access into
				header("Location: index.php");
			}
			else
			{
				// if not successful, get the errors that was committed during the login process
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
		<div class='bg2'>
			<br /><h2 align='center'>Sign In</h2><br />
			<?php
				// display errors for login
				if(isset($_POST["login"]) && $result == false)
				{
					echo "<p align='center'><font color='red'>";
					
					// display login error
					if(!empty($errors["login"]))
					{
						echo $errors["login"]."<br />";
					}
					
					echo "</font></p>";
				}
			?>
			<form method='post' action='<?php echo $_SERVER['PHP_SELF'] ?>'>
				<p align='center'>
					Enter your login credentials to access your account privileges.<br />
					Your username and password are case-sensitive, so please enter them carefully.
				</p>
				<br />
				<p align='right'>
					Username
					<input type='text' id='textbox' style='width:550px'  name='username'/><br />
					Password
					<input type='password' id='textbox' style='width:550px' name='password'/>
				</p>
				<p align='center'>
					<input type='submit' id='submit2' name='login' value='login'  />
				</p>
			</form>
			<p id='box' align='center'>
				<img src='miscs/images/Default/alert.png' align='top'> <i>Cookies must be enabled at this point</i>
			</p>
		</div>		
	</div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>