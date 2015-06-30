<?php
	/**
	 * Filename : mailscompose.php
	 * Description : page for composing mails
	 * Date Created : December 12,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "CONTRIBUTOR");
	
	// import the mails form class
	require_once("../includes/MailsForm.php");
	$form = new MailsForm($_COOKIE["argus"]);
	
	/**
	 * URL EVENTS:
	 *  reply
	 *  compose
	 */
    
        switch($_GET["event"])
        {
            case "reply":
                $event = "REPLY";
                
                // query the mail that is to be replied
                $mailQuery = mysql_query("SELECT sender_account_id, subject, content, status FROM argus_mails WHERE mail_id = '".$_GET["mail"]."' AND account_id = '".$_COOKIE["argus"]."'") or die(mysql_error());
                
                // check if there is a queried mail
                if(mysql_num_rows($mailQuery) == 0)
                {
                    // set the EVENT back to default if the mail has not been queried
                    $event = "COMPOSE";
                }
                else
                {
                    // set the attributes which is to be displayed below
                    $contactId = mysql_result($mailQuery,0,"sender_account_id");
                    
                    
                    // include the name retriever class nad retrieve the name of the sender
                    include("../includes/class_libraries/NameRetriever.php");
                    $nameRetriever = new NameRetriever("account_id");
                    
                    // retrieve the name
                    $name = $nameRetriever -> getName($contactId);
                    
                    // check if there is a name 
                    if($name == "UNKNOWN")
                    {
                        // if UNKNOWN, cause maybe the user was deleted then change the event back to compose
                        $event = "COMPOSE";
                    }
                    else    
                    {                
                        // set the attributes
                        $receivers = $name;
                        $contacts = $contactId;
                        $subject = "RE: ".stripslashes(mysql_result($mailQuery,0,"subject"));
                        $content = stripslashes(mysql_result($mailQuery,0,"content"));
                        $status = mysql_result($mailQuery,0,"status");
                    }
                }
            
                break;
                
            default:
                $event = "COMPOSE";
                
                // check if there are contact Ids in the URL
                if(isset($_GET["contacts"]))
                {
                    // explode the contacts which means that the contacts will be turned into an array
                    // separating each contact ids
                    $contactIds = explode(",", $_GET["contacts"]);
                    
                    // create an UN-EXPLODED contact ids
                    $contacts = $_GET["contacts"];
                    
                    // create an array where to store the names
                    $receivers = array();
                    
                    // query the name of each contacts and store them in an array
                    for($i=0; $i<count($contactIds); $i++)
                    {
                        // query the name of the contact Id
                        $nameQuery = mysql_query("SELECT name FROM argus_accounts WHERE account_id = '".$contactIds[$i]."'") or die(mysql_error());
                        
                        // check the result
                        if(mysql_num_rows($nameQuery) > 0)
                        {
                            // set the result
                            $name = mysql_result($nameQuery,0,"name");
                            
                            // store the name in the created array
                            array_push($receivers,$name);
                        }
                    }
                    
                    // after all names has been fixed.. implode the names separating each name using the character ","
                    // which will be displayed at the receivers textbox
                    $receivers = implode(",", $receivers);
                }
        }
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS
	 *  send
	 */
    
        // SEND button
        if(isset($_POST["send"]))
        {
            // get the inputs from the user
            $contactIds = $_POST["contactIds"];
            $subject = mysql_escape_string($_POST["subject"]);
            $content = $_POST["content"];
            
            // send the mail
            $result = $form -> sendMail($contactIds, $subject, $content);
            
            // check the result
            if($result == true)
            {
                // set a success message that the mails has been successfully sent which will be displayed below
                $successMessage = "Message sent";
            }
            else
            {
                // if not, then get the errors that was committed during the sending of mails which will be displayed below
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
        
        // set the form and the title of the page
        if($event == "REPLY")
        {
            echo "<h3><a href='mailbox.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo ".$subject."</h3>";
            echo "<div class='bg1'>";
            echo "<form method='post' action='".$_SERVER["PHP_SELF"]."?event=reply&mail=".$_GET["mail"]."'>";
        }
        else
        {
            echo "<h3>Compose</h3>";
            echo "<div class='bg1'>";            
            echo "<form method='post' action='".$_SERVER["PHP_SELF"]."?event=compose&contacts=".$contacts."'>";
        }
        
        // display the errors here if any
        if(isset($_POST["send"]) && $result == false)
        {
            echo "<p><font color='red'>";
            
            // display errors for contacts
            if($errors["contacts"] != null)
            {
                echo $errors["contacts"]."<br>";
            }
            
            // display errors for subjects
            if($errors["subject"] != null)
            {
                echo $errors["subject"]."<br>";
            }
            
            echo "</font></p>";
        }
        // display the successful send of mails
        else if(isset($_POST["send"]) && $result == true)
        {
            // display the message
            echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
        }        
    ?>
        <p>
            Create mails here
        </p>
        <p id='box'>
            <b>To</b><br>
            <input type='text' id='textbox' disabled='disabled' value='<?php echo $receivers ?>'><br>
            <input type='hidden' name='contactIds' value='<?php echo $contacts ?>'>
            <a href='mailbox.php?event=contacts'>Click here to insert contacts</a>
        </p>
        <p id='box'>
            <b>Subject</b><br>
            <input type='text' id='textbox' name='subject' value='<?php echo stripslashes($subject) ?>'>
        </p>
        <p>
            <?php
                // include the text editor class and set up the javascript code
                include("../includes/ajax_libraries/TextEditor.php");
                $textEditor = new TextEditor();
                $textEditor -> setupTextEditor("SIMPLE");
            ?>
            <b>Content</b>
            <textarea name='content' style='width:100%; height:300px'><?php echo stripslashes($content) ?></textarea>
        </p>
        <p align='center'>
            <input type='submit' id='submit2' value='send' name='send'>
        </p>
    </form>
    </div>
	</div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>