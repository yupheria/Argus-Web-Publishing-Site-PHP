<?php
	/**
	 * Filename : infosedit.php
	 * Description : page for editing terms and policies and contacts us page
	 * Date Created : December 23,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the Settings form class
	require_once("../includes/SettingsForm.php");
	$form = new SettingsForm();
	
	/**
	 * URL EVENTS:
	 *  welcome banner
	 *  contact us
	 *  terms and policies
	 */
    
        switch($_GET["info"])
        {
            case "welcomebanner":
                $event = "WELCOME BANNER";
                
                // query the welcome banner from the database
                $welcomeBannerQuery = mysql_query("SELECT content FROM argus_infos WHERE name='welcome_banner'") or die(mysql_error());
                
                // query also the publication name from the database
                $publicationNameQuery = mysql_query("SELECT content FROM argus_infos WHERE name='publication_name'") or die(mysql_error());
                
                // arrange the publication name separating them into Title and Subtitle
                $titleAndSubtitle = mysql_result($publicationNameQuery,0,"content");
                $titleAndSubtitle = explode(";",$titleAndSubtitle);
                
                // set the attributes which will be displayed below
                $title = $titleAndSubtitle[0];
                $subtitle = $titleAndSubtitle[1];
                $content = mysql_result($welcomeBannerQuery,0,"content");
                
                break;
                
            case "contactus":
                $event = "CONTACT US";
                
                // query the contact us from the database
                $contactUsQuery = mysql_query("SELECT content FROM argus_infos WHERE name='contact_us'") or die(mysql_error());
                
                // set the attributes which will be displayed below
                $content = mysql_result($contactUsQuery,0,"content");
                
                break;
                
            case "termsandpolicies":
                $event = "TERMS AND POLICIES";
            
                // query the terms and policies from the database
                $termsAndPoliciesQuery = mysql_query("SELECT content FROM argus_infos WHERE name='terms_and_policies'") or die(mysql_error());
            
                // set the attributes which will be displayed below
                $content = mysql_result($termsAndPoliciesQuery,0,"content");
                
                break;
        }
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS
	 *  update
	 */
    
        // UPDATE button
        if(isset($_POST["update"]))
        {
            // get the inputs from the user
            $content = $_POST["content"];
            
            // check what is being updated
            if($event == "WELCOME BANNER")
            {
                // if the event is welcome banner, additional attributes is to be updated
                $title = $_POST["title"];
                $subtitle = $_POST["subtitle"];
                
                // arrange the title and subtitle
                $publicationName = $title.";".$subtitle;
                
                // update the publication name
                $form -> updateInfos("PUBLICATION NAME", $publicationName);
            }
            
            // update the argus infos
            $form -> updateInfos($_GET["info"], $content);
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

        // set the title of the page
        if($event == "TERMS AND POLICIES")
        {
            // title for terms and policies
            echo "<h3>Terms and Policies</h3>";
        }
        else if($event == "CONTACT US")
        {
            // title for the contacts
            echo "<h3>Contact Us</h3>";
        }
        else
        {
            // title for welcome banner
            echo "<h3>Welcome Banner</h3>";
        }
    ?>
    <div class='bg1'>
    <form method='post' action='<?php echo $_SERVER["PHP_SELF"]."?info=".$_GET["info"] ?>'>
        <?php
            // display successful save of terms and policies or contact us
            if(isset($_POST["update"]))
            {
                echo "<p align='center'><font color='green'>Saved</font></p>";
            }
        ?>
        <p>
            <?php
                echo "<p>";
                
                // set the information which is being edited
                if($event == "TERMS AND POLICIES")
                {
                    echo "Terms and Policies Information";
                }
                else if($event == "CONTACT US")
                {
                    echo "Contact Us Information";
                }
                else
                {
                    echo "Welcome Banner Information";
                }
                
                echo "</p>";
            ?>
        </p>
        <?php
            // display additional things to be edited when editing the welcome banner
            if($event == "WELCOME BANNER")
            {
                echo "<p id='box'>";
                echo "<b>Publication Title</b><br />";
                echo "<input type='text' id='textbox' name='title' value='".$title."'><br />";
                echo "Publication Title should be 5 - 15 characters long.<br />";
                echo "<b>Publication Subtitle</b><br />";
                echo "<input type='text' id='textbox' name='subtitle' value='".$subtitle."'>";
                echo "</p>";
            }
        ?>
        <p>
            <?php
                // include the TextEditor class and set up the javascript code
                include("../includes/ajax_libraries/TextEditor.php");
                $textEditor = new TextEditor();
                $textEditor -> setupTextEditor("ADVANCED");
            ?>
            <textarea name='content' style='width:100%; height:500px'><?php echo stripslashes($content); ?></textarea>
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