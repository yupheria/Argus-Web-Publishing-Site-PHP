<?php
	/**
	 * Filename : profilesedit.php
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
	 * URL EVENTS
	 */
    
        // query the database if the user exists
        $accountQuery = mysql_query("SELECT username, password, email, photo_path FROM argus_accounts WHERE account_id = '".$_COOKIE["argus"]."'") or die(mysql_error());
        
        // set the attributes which will be displayed below
        $username = mysql_result($accountQuery,0,"username");
        $password = mysql_result($accountQuery,0,"password");
        $retypedPassword = $password;
        $email = mysql_result($accountQuery,0,"email");
        $photoPath = mysql_result($accountQuery,0,"photo_path");
        
    /**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *  update
	 *  upload photo
	 */
    
        // UPDATE BUTTON
        if(isset($_POST["update"]))
        {
            // get all the inputs from the user
            $username = $_POST["username"];
            $password = $_POST["password"];
            $retypedPassword = $_POST["retypedPassword"];
            $email = $_POST["email"];
            
            // update the profile of the user
            $result = $form -> updateProfile($username, $password, $retypedPassword, $email);
            
            // check the result
            if($result == true)
            {
                // set a success message
                $successMessage = "Profile has been successfully updated";
            }
            else
            {
                // get the errors that was committed during the update of profile
                $errors = $form -> getErrors();
            }
        }
        
        // UPLOAD PHOTO button
        if(isset($_POST["uploadPhoto"]))
        {
            // get the input from the user
            $photoName = $_FILES["photo"]["name"];
            $photoTmpName = $_FILES["photo"]["tmp_name"];

            // upload the photo
            $result = $form -> uploadPhoto($photoName, $photoTmpName, $_COOKIE["argus"]);
            
            // check the result
            if(result == false)
            {
                // get the errors that was encountered which will be displayed below
                $errors = $form -> getErrors();
            }
            else
            {
                // query the new photo path of the user
                $photoPathQuery = mysql_query("SELECT photo_path FROM argus_accounts WHERE account_id = '".$_COOKIE["argus"]."'") or die(mysql_error());
                $photoPath = mysql_result($photoPathQuery,0,"photo_path");
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
    ?>
    <h3>Edit</h3>
    <div class='bg1'>
        <form method='post' action='<?php echo $_SERVER["PHP_SELF"] ?>' enctype='multipart/form-data'>
            <?php
                // display errors for photo uploads
                if(isset($_POST["uploadPhoto"]) && $result == false)
                {
                    echo "<p><font color='red'>";
                    
                    // display errors for photo
                    if($errors["photo"] != null)
                    {
                        echo $errors["photo"]."<br />";
                    }
                    
                    echo "</font></p>";
                }
                
                // display the errors that was committed
                if(isset($_POST["update"]) && $result == false)
                {
                    echo "<p><font color='red'>";
                    
                    // display errors for username
                    if($errors["username"] != null)
                    {
                        echo $errors["username"]."<br>";
                    }
                    
                    // display errors for password
                    if($errors["password"] != null)
                    {
                        echo $errors["password"]."<br>";
                    }
                    
                    // display errors for email
                    if($errors["email"] != null)
                    {
                        echo $errors["email"]."<br>";
                    }
                    
                    echo "</font></p>";
                }
                // display success message
                else if(isset($_POST["update"]) && $result == true)
                {
                    echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
                }
            ?>
            <p>
                Account Information
            </p>
            <!-- Photos -->
            <p align='center'>
                <?php
                    // check if the photo is available or not
                    if($photoPath == null)
                    {
                        // display the default photo
                        echo "<img src='../images/accounts/default.png'>";
                    }
                    else
                    {
                        // display the real photo of the user
                        echo "<img src='".$photoPath."'>";
                    }
                ?>
            </p>
            <p id='box'>
                <b>Upload Picture</b><br>
                <input type='file' name='photo'><br>
                <p align='center'>
                    <input type='submit' id='submit2' value='Upload Photo' name='uploadPhoto'>
                </p>
            </p>
            <!-- Username -->
            <p id='box'>
                <b>Username</b><br />
                <input type='text' id='textbox' name='username' value='<?php echo $username ?>' />
            </p>
            <!-- Password -->
            <p id='box'>
                <b>Password</b><br />
                <input type='password' id='textbox' name='password' value='<?php echo $password ?>'  /><br />
                Password shoud be 5 - 15 characters long.<br />
                <b>Retype Password</b><br />
                <input type='password' id='textbox' name='retypedPassword' value='<?php echo $retypedPassword ?>' />
            </p>
            <!-- Email -->
            <p id='box'>
                <b>Current Email</b><br />
                <input type='text' id='textbox' name='email' value='<?php echo $email ?>' /><br />
                Please ensure that the spam filter of this email address will allow email from www.argus.com.
            </p>
            <p align='center'>
                <input type='submit' id='submit2' value='Update' name='update'>
            </p>
        </form>
    </div>
	</div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>