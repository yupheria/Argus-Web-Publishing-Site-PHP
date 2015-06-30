<?php
	/**
	 * Filename : eventscompose.php
	 * Description : page for creating and editing events
	 * Date Created : December 16,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the mail class form
	require_once("../includes/EventsForm.php");
	$form = new EventsForm();
	
	/**
	 * URL EVENTS:
	 *  edit
	 *  compose
	 */
    
        switch($_GET["event"])
        {
            case "edit":
                $event = "EDIT";
                
                // check if the event that is about to be edited exists from the database
                $eventQuery = mysql_query("SELECT title, year, month, day, content, status FROM argus_events WHERE event_id = '".$_GET["aevent"]."'") or die(mysql_error());
                
                // check if a queried result exist
                if(mysql_num_rows($eventQuery) > 0)
                {
                    // set the attributes which will be displayed below
                    $title = mysql_result($eventQuery,0,"title");
                    $eventDate = mysql_result($eventQuery,0,"year")."/".mysql_result($eventQuery,0,"month")."/".mysql_result($eventQuery,0,"day");
                    $content = mysql_result($eventQuery,0,"content");
                    $status = mysql_result($eventQuery,0,"status");
                }
                else
                {
                    // if the query fails, bring back the event to default
                    $event = "COMPOSE";
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
	 * BUTTON TRIGGER EVENTS:
	 *  update
	 *  create
	 */
     
        // UPDATE button
        if(isset($_POST["update"]))
        {
            // get the inputs from the user
            $title = $_POST["title"];
            $eventDate = $_POST["theDate"];
            $content = $_POST["content"];
            
            // add the event
            $result = $form -> updateEvents($_GET["aevent"], $title, $eventDate, $content);
            
            // check the result
            if($result == true)
            {
                // set the success message which will be displayed below
                $successMessage = "Saved";
            }
            else
            {
                // if validation failed, get the errors that was committed which will be displayed below
                $errors = $form -> getErrors();
            }
        }
    
        // CREATE button
        if(isset($_POST["create"]))
        {
            // get the inputs from the user
            $title = $_POST["title"];
            $eventDate = $_POST["theDate"];
            $content = $_POST["content"];
            
            // add the event
            $result = $form -> updateEvents(null, $title, $eventDate, $content);
            
            // check the result
            if($result == true)
            {
                // set the success message which will be displayed below
                $successMessage = "Saved";
            }
            else
            {
                // if validation failed, get the errors that was committed which will be displayed below
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
        
        // set the title of the page if it's going to be in edit mode or compose mode
        if($event == "EDIT")
        {
            // set the title for EDIT mode
            echo "<h3><a href='events.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo; ".$title."</h3>";
            echo "<div class='bg1'>";
            echo "<form method='post' action='".$_SERVER["PHP_SELF"]."?event=edit&aevent=".$_GET["aevent"]."'>";
        }
        else
        {
            // set the title for compose mode
            echo "<h3>Create</h3>";
            echo "<div class='bg1'>";
            echo "<form method='post' action='".$_SERVER["PHP_SELF"]."'>";
        }

        // display the errors that was committed
        if((isset($_POST["create"]) || isset($_POST["update"])) && $result == false)
        {
            echo "<p><font color='red'>";
            
            if($errors["title"] != null)
            {
                // print errors for title
                echo $errors["title"]."<br>";
            }
            
            if($errors["eventDate"] != null)
            {
                // print the errors for the date
                echo $errors["eventDate"]."<br>";
            }
            
            echo "</font></p>";
        }
        // display success message
        else if((isset($_POST["create"]) || isset($_POST["update"])) && $result == true)
        {
            echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
            
            // clear the values of the input fields of creating a new event only
            if(isset($_POST["create"]))
            {
                $title = "";
                $content = "";
                $eventDate = "";
            }
        }
    ?>
            <p>Create events here</p>
            <p id='box'>
                <b>Title</b><br>
                <input type='text' id='textbox' name='title' value='<?php echo $title; ?>'><br>
            </p>
            <p id='box' align='left'>   
                <link type="text/css" rel="stylesheet" href="../miscs/js/date_picker/calendar.css" media="screen">
                <script type="text/javascript" src="../miscs/js/date_picker/calendar.js"></script>
                <b>Event date</b><a name='calendar'>
                <input type="text" id='textbox' style='textbox' value="<?php echo $eventDate; ?>" readonly name="theDate" onClick="displayCalendar(document.forms[1].theDate,'yyyy/mm/dd',this)"><br>
            </p>
            <p>
                <b>Content</b><br>
                <?php
                    // include the text editor class and set up the javascript code
                    include("../includes/ajax_libraries/TextEditor.php");
                    $textEditor = new TextEditor();
                    $textEditor -> setupTextEditor("ADVANCED");
                ?>
                <textarea name='content' style='width:100%; height:150px'><?php echo $content ?></textarea>
            </p>
            <p align='center'>
                <?php
                    // set the appropriate button for the page if it's going to be create or update
                    if($event == "EDIT")
                    {
                        echo "<input type='submit' id='submit2' value='Update' name='update'>";
                    }
                    else
                    {
                        echo "<input type='submit' id='submit2' value='Create' name='create'>";
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