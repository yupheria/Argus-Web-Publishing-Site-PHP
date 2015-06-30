<?php
	/**
	 * Filename : imagesupload.php
	 * Description : page for uploading images
	 * Date Created : December 4,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "CONTRIBUTOR");
	
	// import the images form class
	require_once("../includes/ImagesForm.php");
	$form = new ImagesForm($_COOKIE["argus"]);
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *  upload
	 */
    
        // UPLOAD button
        if(isset($_POST["upload"]))
        {   
            // create a variable where to store all success message and error messages
            $successMessages;
            $errorMessages;
            
            // include the id generator class
            include("../includes/class_libraries/IdGenerator.php");
            $idGenerator = new IdGenerator();
            
            // get the inputs from the user
            $names = $_FILES["file"]["name"];
            $tmpNames = $_FILES["file"]["tmp_name"];
            
            // validate each image, each successfully validated image will be uploaded
            // start the iteration with 0
            for($i=0; $i<count($names); $i++)
            {
                // validate if the name has a value, and do not do anything if the name has no value
                if(!empty($names[$i]))
                {
                    // create an image id
                    $imageId = $idGenerator -> generateId("image_id");
                    
                    // upload the image
                    $result = $form -> uploadImage($imageId, $names[$i], $tmpNames[$i]);
                    
                    // check the result
                    if($result == true)
                    {
                        // set the success message appending the last success message using the character ","
                        // which will be exploded below for display
                        $successMessages .= "The image '".$names[$i]."' has been successfully uploaded,";
                    }
                    else
                    {
                        // if upload failed, get the error that was commited then append the error with the previous
                        // errors with the character "," which will be exploded below for display
                        $errorMessages .= $form -> getErrors().",";
                    }
                }
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
	?>
    <h3>Upload</h3>
    <div class='bg1'>
        <form method='post' action='<?php $_SERVER['PHP_SELF'] ?>' enctype='multipart/form-data'>
            <?php
                // display the errors that was committed during the upload of images
                if(isset($_POST["upload"]) && $errorMessages != null)
                {
                    echo "<p><font color='red'>";
                    
                    // convert the error messages into an array
                    $errors = explode(",", $errorMessages);
                    
                    // display the errors
                    for($i=0; $i<count($errors); $i++)
                    {
                        echo $errors[$i]."<br>";
                    }
                    
                    echo "</font></p>";
                }
                
                // display the success upload of image
                if(isset($_POST["upload"]) && $successMessages != null)
                {
                    echo "<p align='center'><font color='green'>";
                    
                    // conver the success messages into an array
                    $messages = explode(",", $successMessages);
                    
                    // display the messages
                    for($i=0; $i<count($messages); $i++)
                    {
                        echo $messages[$i]."<br>";
                    }
                    
                    echo "</font></p>";
                }
            ?>
            <p>Upload images</p>
            <p id='box'>
                <?php
                    // import the multi file uploader class and create an uploader class that only
                    // accepts a maximum of 3 images
                    require_once("../includes/ajax_libraries/MultiFileUploader.php");
                    $fileUploader = new MultiFileUploader(3);
                    
                    // set the upload script
                    $fileUploader -> displayScript();
                ?>
                Image<br>
                <input id="my_file_element" type="file" name="file[]" ><br>
                Type here the image constraints like size of the image that should be uploaded.
            </p>
            <div id='box'>
                Files:
                <?php
                    // display the file queue box where upload queue will be displayed
                    $fileUploader -> displayFileQueueBox();
                ?>
            </div>
            <p align='center'>
                <input type='submit' id='submit2' name='upload' value='upload'>
            </p>
        </form>
    </div>
	</div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>