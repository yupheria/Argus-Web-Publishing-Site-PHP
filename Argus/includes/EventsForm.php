<?php
    /**
     * Filename : EventsForm.php
     * Description : contains properties and objects for managing events
     * Date Created : December 16, 2007
     * Author : Argus Team
     */
    
    /**
     * METHODS SUMMARY:
     */
    
    class EventsForm
    {
        var $errors;
        
        /**
         * Display Banner Method: displays the options and menus for managing the calendar
         */
        function displayBanner()
        {
            echo "
            <div class='bg2'>
                <h2><em>Events Manager</em></h2>
                <p align='center'>";
            
            // menus
            echo "
            <a href='events.php'>Saved</a> . 
            <a href='events.php?event=deleted'>Deleted</a> . 
            <a href='eventscompose.php'>Create</a>";
            
            echo "
            </p>
            </div>";
            
            return;
        }
        
        /**
         * Display Events Method: displays all events depending on the parameter being passed
         * Parameter: $status
         * Return type: string
         */
        function displayEvents($status)
        {
            // query all events from the database
            $eventsQuery = mysql_query("SELECT event_id, title, month, year, day, date_added FROM argus_events WHERE status = '".$status."' ORDER BY date_added DESC") or die(mysql_error());

            // set the title of the page
            echo "
            <h3>".ucfirst(strtolower($status))."</h3>
            <div class='bg1' id='tablePanel'>";
            
            // check if there are events queried from the database
            if(mysql_num_rows($eventsQuery) == 0)
            {
                // if there are no events, then notify the user that there are no events
                echo "<p><h3 align='center'>There are no ".$status." events</h3></p>";
            }
            else
            {
                // include the ajax tool tip and set it up
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                // include the javascript file for selecting and deselecting multiple checkboxes
                echo "<script type='text/javascript' src='../miscs/js/checkbox_toggle/checkboxtoggler.js'></script>";
                
                // set the form and table where to display the events
                echo "<form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=".$_GET["event"]."'>";
                echo "<table width='100%'>";
                echo "<tr>";
                echo "<th><input type='checkbox' onClick='toggleCheckBoxes(\"eventIds\")'></th>";
                echo "<th>Title</th>";
                echo "<td>Date of Event</td>";
                echo "<th>Date Added</th>";
                echo "<th>Action</th>";
                echo "</tr>";
                
                // display all the events
                $color = true;
                
                for($i=0; $i<mysql_num_rows($eventsQuery); $i++)
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
                    $eventId = mysql_result($eventsQuery,$i,"event_id");
                    $title = mysql_result($eventsQuery,$i,"title");
                    $dateAdded = date("m/d/y", mysql_result($eventsQuery,$i,"date_added"));
                    $eventDate = mysql_result($eventsQuery,$i,"year")."/".mysql_result($eventsQuery,$i,"month")."/".mysql_result($eventsQuery,$i,"day");
                    
                    // display the attributes
                    echo "<td class='fix'><input type='checkbox' name='eventIds[]' value='".$eventId."'></td>";
                    echo "<td><a href='events.php?event=view&aevent=".$eventId."'>".$title."</a></td>";
                    echo "<td>".$eventDate."</td>";
                    echo "<td>".$dateAdded."</td>";
                    echo "<td class='action'>";
                    echo "<a href='eventscompose.php?event=edit&aevent=".$eventId."' title='Edit'><img src='../miscs/images/Default/article_edit.png'></a> ";
                    
                    // set the actions
                    if($status == "SAVED")
                    {
                        // set the actions for SAVED events
                        echo "<a href='events.php?event=".$_GET["event"]."&action=remove&aevent=".$eventId."' title='Remove'><img src='../miscs/images/Default/article_trash.png'></a>";
                    }
                    else
                    {
                        // set the actions for DELETED events
                        echo "<a href='events.php?event=".$_GET["event"]."&action=restore&aevent=".$eventId."' title='Restore'><img src='../miscs/images/Default/b.gif'></a> ";   
                        echo "<a href='events.php?event=".$_GET["event"]."&action=delete&aevent=".$eventId."' title='Delete'><img src='../miscs/images/Default/article_delete.png'></a>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
                
                // set the buttons for managing events
                echo "<table widh='100%'>";
                echo "<tr>";
                echo "<td>";
                
                if($status == "SAVED")
                {
                    // buttons for saved events
                    echo "<input type='submit' id='submit1' value='Remove' name='remove'>";
                }
                else
                {
                    // buttons for deleted events
                    echo "<input type='submit' id='submit1' value='Restore' name='restore'> ";
                    echo "<input type='submit' id='submit1' value='Delete' name='delete'> ";
                    echo "<input type='submit' id='submit1' value='Delete all' name='deleteAll'>";
                }
                
                echo "</td>";
                echo "</tr>";
                echo "</table>";
                echo "</form>";
            }
            
            echo "</div>";
            
            return;
        }
        
        /**
         * Update Events Method: updates the event, add or edit events
         * Parameter: $eventId, $title, $eventDate, $content
         * Return Type: boolean
         */
        function updateEvents($eventId, $title, $eventDate, $content)
        {
            // escape the characters that are needed to be escped to avoid sql injection
            $title = mysql_escape_string($title);
            $content = mysql_escape_string($content);
            
            // validate the title
            $titleError = $this -> validateTitle($title);
            $eventDateError = $this -> validateEventDate($eventDate);
            
            // check the validation
            if($titleError == null && $eventDateError == null)
            {
                // break the event date by YEAR, MONTH, and DAY into an array
                // eventDate[0] = YEAR
                // eventDate[1] = MONTH
                // eventDate[2] = DAY
                $eventDate = explode("/",$eventDate);
                $year = $eventDate[0];
                $month = $eventDate[1];
                $day = $eventDate[2];
                
                // check if the event ID is null or not
                if($eventId == null)
                {
                    // if the event id is null, which means that the user is adding a new event
                    // add the new event to the database
                    mysql_query("INSERT INTO argus_events(title, content, month, day, year,date_added, status)
                                 VALUES('".$title."','".$content."','".$month."','".$day."','".$year."','".time()."','SAVED')") or die(mysql_error());
                }
                else
                {
                    // if the event id is not null, then that means that the user is updating or editing an event
                    // update the event
                    mysql_query("UPDATE argus_events SET title='".$title."', content='".$content."', month='".$month."', day='".$day."', year='".$year."' WHERE event_id = '".$eventId."'") or die(mysql_error());
                }
                
                // return successful update of event
                return true;
            }
            else
            {
                // set the errors which will be passed and displayed to the user
                $this -> errors = array("title" => $titleError, "eventDate" => $eventDateError);
                
                // return unsuccessful update of events
                return false;
            }
        }
        
        /**
         * Validate event date method: validates the date if it is empty or not
         * Parameter: $eventDate
         * Return Type: string
         */
        function validateEventDate($eventDate)
        {
            // check if the date is empty
            if(empty($eventDate))
            {
                // return an error message that the date is empty
                return "Please provide a date for the event";
            }
            
            return;
        }
        
        /**
         * Validate Title method: validates the title
         * Parameter: $title
         * Return Type: string
         */
        function validateTitle($title)
        {
            // include the title validate class and validate the title that accepts minimum of 5 characters and max of 100 characters
            include("class_libraries/TitleValidator.php");
            $titleValidator = new TitleValidator(5, 500);
            
            // validate the title
            $result = $titleValidator -> validateTitle($title);
            
            // check the result
            if($result == false)
            {
                // if false, get the error that was committed and return the error
                return $titleValidator -> getErrors();
            }
            
            return;
        }
        
        /**
         * Get Errors method: returns the errors that was committed during the updating of events
         * REturn type: string
         */
        function getErrors()
        {
            // return the errors
            return $this -> errors;   
        }
        
        /**
         * Remove Event method: removes the event and transfers it to the deleted section
         * Parameter: $eventId
         */
        function removeEvent($eventId)
        {
            // remove the Event
            mysql_query("UPDATE argus_events SET status = 'DELETED' WHERE event_id = '".$eventId."'") or die(mysql_error());

            return;
        }
        
        /**
         * Restore Event method: restores the event and transfers it to the saved section
         * Parameter: $eventId
         */
        function restoreEvent($eventId)
        {
            // restore the event
            mysql_query("UPDATE argus_events SET status = 'SAVED' where event_id = '".$eventId."' AND status = 'DELETED'") or die(mysql_error());
            
            return;
        }
    
        /**
         * Delete Event method: deletes an event permanently
         * Parameter: $eventId
         */
        function deleteEvent($eventId)
        {
            // delete the event
            mysql_query("DELETE FROM argus_events WHERE event_id = '".$eventId."' AND status = 'DELETED'") or die(mysql_error());
        
            return;
        }
        
        /**
         * Delete All Event method: deletes all removed events
         */
        function deleteAllEvents()
        {
            // delete all removed events
            mysql_query("DELETE FROM argus_events WHERE status = 'DELETED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * View Event method: displays the content event
         * Parameter: $eventId
         * Return type: string
         */
        function viewEvent($eventId)
        {
            // query the event from the database
            $eventQuery = mysql_query("SELECT title, content, date_added, month, day, year, status FROM argus_events WHERE event_id = '".$eventId."'") or die(mysql_error());
            
            // check if the event exists from the database
            if(mysql_num_rows($eventQuery) > 0)
            {
                // set the attributes
                $title = mysql_result($eventQuery,0,"title");
                $content = mysql_result($eventQuery,0,"content");
                $dateAdded = date("F d, Y", mysql_result($eventQuery,0,"date_added"));
                $eventDate = mysql_result($eventQuery,0,"year")."/".mysql_result($eventQuery,0,"month")."/".mysql_result($eventQuery,0,"day");
                $status = mysql_result($eventQuery,0,"STATUS");
                
                // set the title of the page
                echo "<h3><a href='events.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo ".$title."</h3>";
                echo "<div class='bg1'>";
                
                // display the attributes
                echo "<p>Event Information</p>";
                echo "<p id='box'>";
                echo "Title : ".$title."<br>";
                echo "Event Date : ".$eventDate."<br>";
                echo "Date Added : ".$dateAdded."<br>";
                echo "</p>";
                
                echo "<p align='right'>";
                echo "<a href='eventscompose.php?event=edit&aevent=".$eventId."'><input type='button' id='submit1' value='Edit'></a> ";
                
                // display the buttons for managing the event
                if($status == "SAVED")
                {
                    // buttons for SAVED events
                    echo "<a href='events.php?event=".strtolower($status)."&action=remove&aevent=".$eventId."'><input type='button' id='submit1' value='Remove'></a>";
                }
                else
                {
                    // buttons for DELETED events
                    echo "<a href='events.php?event=".strtolower($status)."&action=restore&aevent=".$eventId."'><input type='button' id='submit1' value='Restore'></a> ";
                    echo "<a href='events.php?event=".strtolower($status)."&action=delete&aevent=".$eventId."'><input type='button' id='submit1' value='Delete'></a>";
                }
                
                echo "<p>";
                echo $content;
                echo "</p>";                
                echo "</p>";
                echo "</div>";
            }
            
            return;
        }
    }
?>