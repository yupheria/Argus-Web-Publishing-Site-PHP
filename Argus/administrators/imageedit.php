<?php
	/**
	 * Filename : imageedit.php
	 * Description : page for editing image names and description
	 * Date Created : January 21,2008
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the images form class
	require_once("../includes/ImagesForm.php");
	$form = new ImagesForm($_COOKIE["argus"]);
    
    /**
     * URL EVENT
     *  edit
     */
    
        switch($_GET["event"])
        {
            default:
                $event == "EDIT";
                
                // query the image from the database
                $imageQuery = mysql_query("SELECT name, description, path FROM argus_images WHERE image_id='".$_GET["image"]."' AND account_id = '".$_COOKIE["argus"]."'") or die(mysql_error());
                
                // check if an image is queried from the database
                if(mysql_num_rows($imageQuery) > 0)
                {
                    // set the attributes which will be displayed below
                    $name = mysql_result($imageQuery,0,"name");
                    $description = mysql_result($imageQuery,0,"description");
                    $path = mysql_result($imageQuery,0,"path");
                }
                else
                {
                    // redirect the user to the index page
                    header("Location: index.php");
                }
                
                break;
        }
    
    /**
     * END OF URL EVENT
     */
	
	/**
	 * BUTTON TRIGGER EVENTS:
     *  save
	 */
    
        // SAVE BUTTON
        if(isset($_POST["save"]))
        {   
            // get the input from the user
            $name = $_POST["name"];
            $description = $_POST["description"];
            
            // update the database
            $result = $form -> updateImage($_GET["image"], $name, $description);
            
            // check the result
            if($result == true)
            {
                // create a success message which will be displayed below
                $successMessage = "Saved";
            }
            else
            {
                // get the errors that was committed and displayed below
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
        
		// display the tools for ADMINISTRATORS
		$page -> displayTools();
        
        echo "</div>";
	?>
	<!-- left side column: contains sub options and articles and where manipulation of tools occurs -->
	<?php
        $page -> displayDivCode("LEFT");
    
		// display the banner
		$form -> displayBanner();
        
        // set the title of the page
        // check the status of the image
        echo "<h3><a href='images.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo; Edit</h3>";
	?>
    <div class='bg1'>
        <form method='post' action='<?php $_SERVER['PHP_SELF']."?event=edit&image=".$_GET["image"] ?>'>
            <?php
                // display errors that was committed
                if(isset($_POST["save"]) && $result == false)
                {
                    echo "<font color='red'><p>";
                    
                    // error for name
                    if($errors["name"] != null)
                    {
                        echo $errors["name"];
                    }
                    
                    echo "</font></p>";
                }
                
                // display successful save
                if(isset($_POST["save"]) && $result == true)
                {
                    echo "<font color='green'><p align='center'>".$successMessage."</p></font>";
                }
            ?>
            <p>Image Information</p>
            <!-- name -->
            <p id='box'>
                <b>Name</b><br />
                <input type='text' id='textbox' value='<?php echo $name ?>' name='name'>
            </p>
            <!-- description -->
            <p id='box'>
                <b>Description</b><br />
                <input type='text' id='textbox' value='<?php echo $description ?>' name='description'>
            </p>
            <!-- image -->
            <p align='center'><img src='<?php echo $path ?>'></p>
            <p align='center'>
                <input type='submit' id='submit2' value='Save' name='save'>
            </p>
        </form>
    </div>
	</div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>