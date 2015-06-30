<?php
	/**
	 * Filename : MailsForm.php
	 * Description : contains functions and page properties of managing mails
	 * Date Created : December 12,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
     *  MailsForm($accountId)
     *  string displayMails($status, $page)
     *  string getSenderName($accountId)
     *  string displayContacts()
     *  boolean sendMails($contactIds, $subject, $content)
     *  string validateContacts($contactid)
     *  string getErrors()
     *  string validateSubject($subject)
     *  void removeMail($mailId)
     *  void deleteMail($mailId)
     *  void restoreMail($mailId)
     *  void deleteAllMails()
     *  string viewMail($mailId)
	 */
	 
	class MailsForm
	{
        var $accountId;
        var $errors;
        
        /**
         * Constructor method
         * Parameter: $accountId
         */
        function MailsForm($accountId)
        {
            // set the account id to know who's MAILS are going to be managed
            $this -> accountId = $accountId;
            
            return;
        }
         
		/**
		 * Display Banner method: displays the menu and options for managing accounts
		 */
		function displayBanner()
		{
			echo "
            <div class='bg2'>
			<h2><em>Mail Manager</em></h2>
			<p align='center'>";
			
			// menus
			echo "
            <a href='mailbox.php'>Saved</a> . 
			<a href='mailbox.php?event=deleted'>Deleted</a> . 
			<a href='mailscompose.php'>Compose</a> . 
			<a href='mailbox.php?event=contacts'>Contacts</a>";
			
			echo "
            </p>
			</div>";
			
			return;
		}
        
        /**
         * Display mails method: displays the mails of the user depending on the parameter
         * Parameter: $status, $page
         * Return Type: String
         */
        function displayMails($status, $page)
        {
            // query on how many total mails does the user have
            $totalMailsQuery = mysql_query("SELECT mail_id FROM argus_mails WHERE status = '".$status."' AND account_id = '".$this -> accountId."'") or die(mysql_error());
            $totalMailsCount = mysql_num_rows($totalMailsQuery);
            
            // set that the number of mails to be displayed per page is 15
            $limit = 15;
            
            // compute the number of total number of pages
            $numberOfPages = ceil($totalMailsCount / $limit);
            
            // check if the page is empty or not
            if(empty($page) && !ctype_digit($page))
            {
                // set the default page which is equal to 1
                $page = 1;
            }
            
            // compute the limit value
            // so if we have 30 mails, and we are in page one.. mails to be displayed would be mails 1 - 15
            // and if we are in page two.. mails to be displayed would be 15 - 30;
            $limitValue = $page * $limit - ($limit);
                        
            // query the mails from the database ordering them by date received descending order setting the limit
            $mailsQuery = mysql_query("SELECT mail_id, sender_account_id, subject, date_received, type FROM argus_mails WHERE status = '".$status."' AND account_id = '".$this -> accountId."' ORDER BY date_received DESC LIMIT ".$limitValue.",".$limit."") or die(mysql_error());
            
            // set the title of the page
            echo "
            <h3>".ucfirst(strtolower($status))."</h3>
            <div class='bg1' id='tablePanel'>";
            
            // check the queried results
            if(mysql_num_rows($mailsQuery) == 0)
            {
                // notify the user that there are no mails queried from the database
                echo "<p><h3 align='center'>There are no ".$status." mails</h3></p>";
            }
            else
            {
                // inlcude the TOOL TIP ajax and create a tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                 // include the checkbox funtions where check box are allowed to be selected/unselected all
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
                // create a form and table where to display the queried mail results
                echo "
                <form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=".strtolower($status)."'>
                <table width='100%'>
                <tr>
                <th><input type='checkbox' onClick='toggleCheckBoxes(\"mailIds\")'></th>
                <th>Subject</th>
                <th>Sender</th>
                <th>Date Received</th>
                <th>Action</th>
                </tr>";
                
                // display the mails
                $color = true;
                
                for($i=0; $i<mysql_num_rows($mailsQuery); $i++)
                {
                    // display the rows in an alternate color
                    if($color == true)
                    {
                        echo "<tr class='bg1'>";
                        $color = false;
                    }
                    else
                    {
                        echo "<tr>";
                        $color = true;
                    }
                    
                    // set the attributes
                    $mailId = mysql_result($mailsQuery,$i,"mail_id");
                    $subject = stripslashes(mysql_result($mailsQuery,$i,"subject"));
                    $sender = $this -> getSenderName(mysql_result($mailsQuery,$i,"sender_account_id"));
                    $dateReceived = date("m/d/y", mysql_result($mailsQuery,$i,"date_received"));
                    $type = mysql_result($mailsQuery,$i,"type");
                    
                    // display the attributes
                    echo "
                    <td><input type='checkbox' name='mailIds[]' value='".$mailId."'></td>
                    <td><a href='mailbox.php?event=viewmail&mail=".$mailId."'>".$subject."</a></td>
                    <td>".$sender."</td>
                    <td>".$dateReceived."</td>
                    <td>
                    <a href='mailscompose.php?event=reply&mail=".$mailId."' title='Reply'><img src='../miscs/images/Default/mail_reply.png'></a> 
                    ";
                    
                    // set the actions
                    if($status == "SAVED")
                    {
                        // actions for saved mails
                        echo "
                        <a href='mailbox.php?event=".strtolower($status)."&action=remove&mail=".$mailId."' title='Remove'><img src='../miscs/images/Default/article_trash.png'></a>";
                    }
                    else
                    {
                        // actions for deleted mails
                        echo "
                        <a href='mailbox.php?event=".strtolower($status)."&action=restore&mail=".$mailId."' title='Restore'><img src='../miscs/images/Default/mail_restore.png'></a> 
                        <a href='mailbox.php?event=".strtolower($status)."&action=delete&mail=".$mailId."' title='Delete'><img src='../miscs/images/Default/mail_delete.png'></a>";
                    }
                    
                    echo "
                    </td>
                    </tr>";
                }
                
                echo "</table>";
                
                // set the buttons for managing mails
                echo"
                <table width='100%'>
                <tr>
                <td>";
                
                if($status == "SAVED")
                {
                    // buttons for saved mails
                    echo "<input type='submit' id='submit1' value='Remove' name='remove'>";
                }
                else
                {
                    // buttons for deleted mails
                    echo "
                    <input type='submit' id='submit1' value='Restore' name='restore'> 
                    <input type='submit' id='submit1' value='Delete' name='delete'> 
                    <input type='submit' id='submit1' value='Delete all' name='deleteAll'>";
                }
                
                echo "
                </td>
                <td align='right'>";
                
                // display the previous page link
                if($page > 1)
                {
                    echo "<a href='mailbox.php?event=".$_GET["event"]."&page=".($page-1)."'><img src='../miscs/images/Default/previous.png' title='Previous'></a> ";
                }
                
                // display the next page link
                if($page < $numberOfPages)
                {
                    echo "<a href='mailbox.php?event=".$_GET["event"]."&page=".($page+1)."'><img src='../miscs/images/Default/next.png' title='Next'></a>";
                }
                
                echo "
                </td>
                </tr>
                </table>
                </form>";
            }
            
            echo "</div>";
            
            return;
        }
        
        /**
         * Get Sender Name method: returns the name of the sender
         * Parameter: $accountId
         * Return Type: string
         */
        function getSenderName($accountId)
        {
            // include the name retreiver class and create a name retreiver to get the name of the sender
            require_once("class_libraries/NameRetriever.php");
            $nameRetriever = new NameRetriever("account_id");
            
            // retrieve the name then returns back the name
            return $nameRetriever -> getName($accountId);
        }
        
        /**
         * Display Contacts method: displays all contact list
         * Return type: string
         */
        function displayContacts()
        {
            // query all the contacts from the database
            // the contacts are already predefined where contributors and administrators are the
            // only person who can use the mailbox facility
            $contactsQuery = mysql_query("SELECT account_id, name FROM argus_accounts WHERE (position='ADMINISTRATOR' OR position='CONTRIBUTOR') AND account_id != '".$this -> accountId."'") or die(mysql_error());
            
            // set the title of the page
            echo "
            <h3>Contacts</h3>
            <div class='bg1' id='tablePanel'>";
            
            // check if there are contacts queried from the database
            if(mysql_num_rows($contactsQuery) == 0)
            {
                // notify the user that there are no contacts available
                echo "<p><h3 align='center'>There are no available contacts</h3></p>";
            }
            else
            {
                // inlcude the TOOL TIP ajax and create a tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                // include the checkbox funtions where check box are allowed to be selected/unselected all
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
                // create a form and table where to display the contacts list
                echo "
                <form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=contacts'>
                <table width='100%'>
                <tr>
                <th><input type='checkbox' onClick='toggleCheckBoxes(\"contactIds\")'></th>
                <th>Name</th>
                <th>Action</th>
                </tr>";
                
                // display the contacts
                $color = true;
                
                for($i=0; $i < mysql_num_rows($contactsQuery); $i++)
                {
                    // display the rows in an alternate color
                    if($color == true)
                    {
                        echo "<tr class='bg1'>";
                        $color = false;
                    }
                    else
                    {
                        echo "<tr>";
                        $color = true;
                    }
                    
                    // set the attributes
                    $accountId = mysql_result($contactsQuery,$i,"account_id");
                    $name = mysql_result($contactsQuery,$i,"name");
                    
                    // display the attributes
                    echo "
                    <td><input type='checkbox' name='contactIds[]' value='".$accountId."'></td>
                    <td>".$name."</td>
                    <td>";
                    
                    // display the actions
                    echo "<a href='mailscompose.php?event=compose&contacts=".$accountId."' title='insert'><img src='../miscs/images/Default/contact_insert.png'></a>";
                    
                    echo "
                    </td>
                    </tr>";
                }
                
                echo "
                </table>";
                
                // display the buttons for managing the contacts
                echo "
                <table width='100%'>
                <tr>
                <td>
                <input type='submit' id='submit1' value='Insert' name='insert'>
                </td>
                </tr>
                </table>";
                
                echo "</form>";
            }
            
            echo "</div>";
            
            return;
        }
    
        /**
         * Send Mail Method: sends the mails
         * Parameters: $contactIds, $subject, $content
         * Return type: boolean
         */
        function sendMail($contactIds, $subject, $content)
        {
            // escape the characters that are needed to be escaped to avoid sql injection
            $subject = mysql_escape_string($subject);
            $content = mysql_escape_string($content);
            
            // check if the mail utility is available
            $mailUtilityQuery = mysql_query("SELECT content FROM argus_infos WHERE name='send_mail'") or die(mysql_error());
            $mailUtility = mysql_result($mailUtilityQuery,0,"content");
            
            if($mailUtility == "false")
            {
                // set an error that the mail utility is disabled
                $contactsError = "The Mail utility is currently disabled";
            }
            else
            {            
                // validate the contactIds if it is empty or not
                $contactsError = $this -> validateContacts($contactIds);
                
                // validate the subject
                $subjectError = $this -> validateSubject($subject);
            }
            
            // check the validated results
            if($contactsError == null && $subjectError == null)
            {
                // conver the contactIds into an ARRAY separating them using the "," character
                $contactIds = explode(",", $contactIds);
                
                // send the mail to each contact ids
                for($i=0; $i<count($contactIds); $i++)
                {
                    // send the mail and mark the mail UNREAD and SAVED
                    mysql_query("INSERT INTO argus_mails(account_id, sender_account_id, subject, content, date_received, type, status)
                                 VALUES('".$contactIds[$i]."','".$this -> accountId."','".$subject."','".$content."','".time()."','UNREAD','SAVED')") or die(mysql_error());
                }
                
                // return successful send
                return true;
            }
            else
            {
                // set the error
                $this -> errors = array("contacts" => $contactsError, "subject" => $subjectError);
                
                // return unsuccessful send
                return false;
            }
            
            return;
        }
        
        /**
         * Validate Contacts Method: validates if the contacts is empty or not
         * Parameter: $contactIds
         * Return Type: string
         */
        function validateContacts($contactIds)
        {
            // check if the contact ids is empty or not
            if(empty($contactIds))
            {
                // return an error message that the contacts is empty
                return "Please insert a contact";
            }
            
            return;
        }
        
        /**
         * Get Errors Method: returns the errors that was committed during the sending of mails
         * Return Type: String
         */
        function getErrors()
        {
            // return the errors
            return $this -> errors;
        }
        
        /**
         * Validate Subject Method: validates the subject if it's empty or not and validates the length of characters
         * Parameter: $subject
         * Return type: String
         */
        function validateSubject($subject)
        {
            // include the title validate class and validate the subject with a minimum character of 1 and maximum of 50 characters
            require_once("class_libraries/TitleValidator.php");
            $titleValidator = new TitleValidator(1,50);
            
            // validate the title
            $result = $titleValidator -> validateTitle($subject);
            
            // check the result
            if($result == false)
            {
                // if the result is false, get the error that was committed and return that error
                return $titleValidator -> getErrors();
            }
            
            return;
        }
        
        /**
         * Remove Mail method: transfers the mail to the deleted section
         * Parameter: $mailId
         */
        function removeMail($mailId)
        {
            // remove the mail
            mysql_query("UPDATE argus_mails SET status='DELETED' WHERE mail_id = '".$mailId."' AND status = 'SAVED' and account_id = '".$this -> accountId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Delete Mail Method: permanently deletes a mail
         * Parameter: $mailId
         */
        function deleteMail($mailId)
        {
            // delete the mail
            mysql_query("DELETE FROM argus_mails WHERE mail_id = '".$mailId."' AND status = 'DELETED' AND account_id = '".$this -> accountId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Restore Mail method: restores a mail back to the saved section
         * Parameter: $mailId
         */
        function restoreMail($mailId)
        {
            // restore the mail
            mysql_query("UPDATE argus_mails SET status='SAVED' WHERE mail_id = '".$mailId."' AND status = 'DELETED' AND account_id = '".$this -> accountId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * DELETE all Mails method: delets all trash mails of the user
         */
        function deleteAllMails()
        {
            // delete all the mails
            mysql_query("DELETE FROM argus_mails WHERE account_id = '".$this -> accountId."' AND status='DELETED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * VIEW MAIL method: shows the contents of the mail
         * Parameter: $mailId
         * Return Type: String
         */
        function viewMail($mailId)
        {
            // query the mail from the database using the mail id parameter
            $mailQuery = mysql_query("SELECT sender_account_id, subject, content, date_received, status FROM argus_mails WHERE mail_id = '".$mailId."' AND account_id = '".$this -> accountId."'") or die(mysql_error());
            
            // check if there are results from the query
            if(mysql_num_rows($mailQuery) > 0)
            {
                // once the mail has been viewed, update the TYPE of mail from UNREAD to READ
                mysql_query("UPDATE argus_mails SET type='READ' WHERE mail_id = '".$mailId."' AND account_id = '".$this -> accountId."' AND type='UNREAD'") or die(mysql_error());
                
                // set the attributes
                $sender = $this -> getSenderName(mysql_result($mailQuery,0,"sender_account_id"));
                $subject = stripslashes(mysql_result($mailQuery,0,"subject"));
                $content = stripslashes(mysql_result($mailQuery,0,"content"));
                $dateReceived = date("F d, Y", mysql_result($mailQuery, 0, "date_received"));
                $status = mysql_result($mailQuery,0,"status");
                
                // set the title of the page
                echo "
                <h3><a href='mailbox.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo ".$subject."</h3> 
                <div class='bg1'>";
                
                // display the mail contents
                echo "
                <p>Mail Information</p>
                <p id='box'>
                Sender : ".$sender."<br>
                Subject : ".$subject."<br>
                Date Received : ".$dateReceived."<br>
                Status : ".$status."<br>
                </p>";
                
                // set the buttons for managing the contents
                echo "
                <p align='right'>
                <a href='mailscompose.php?event=reply&mail=".$mailId."'><input type='button' id='submit1' value='reply'></a>";
                
                if($status == "SAVED")
                {
                    // actions for saved mails
                    echo " 
                    <a href='mailbox.php?event=".strtolower($status)."&action=remove&mail=".$mailId."'><input type='button' id='submit1' value='remove'></a>";
                }
                else
                {
                    // actions for deleted mails
                    echo "
                    <a href='mailbox.php?event=".strtolower($status)."&action=restore&mail=".$mailId."'><input type='button' id='submit1' value='restore'></a>
                    <a href='mailbox.php?event=".strtolower($status)."&action=delete&mail=".$mailId."'><input type='button' id='submit1' value='delete'></a>";
                }
                
                echo "</p>";
                
                echo "Message : <br>";
                echo $content;
                
                echo "</div>";
            }
            
            return;
        }
	}
?>