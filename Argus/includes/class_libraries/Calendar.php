<?php
    /**
     * Filename : Calendar.php
     * Description : class file that generates a calendar
     * Date Added : December 16,2007
     * Author : Keith Devens
     * Modified by : Argus Team
     */

    /**
     * METHODS SUMMARY:
     *  string generateCalendar()
     *  boolean searchDay($day)
     */
    
    class Calendar
    {
        var $eventDays;
        
        /**
         * Search Day Method: searches if the day is an event or not
         * Parameter: $day
         * Return Type: boolean
         */
        function searchDay($day)
        {
            // search the array of days if the given parameter day exists in the array
            for($i=0; $i<count($this -> eventDays); $i++)
            {
                if($day == $this -> eventDays[$i])
                {
                    // if the day has a match then that means that the day is an event
                    // return successful search
                    return true;
                }
            }
            
            // return false if the event does not exist
            return false;
        }
        
        /**
         * generate calendar method: generates a calendar
         */
        function generateCalendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array())
        {
            $this -> eventDays = array();
            
            $eventsQuery = mysql_query("SELECT day FROM argus_events WHERE month='".$month."' AND year = '".$year."' AND status = 'SAVED'") or die(mysql_error());
            
            // store the days in an array
            for($i=0; $i<mysql_num_rows($eventsQuery); $i++)
            {
                array_push($this -> eventDays, mysql_result($eventsQuery, $i, "day"));
            }
            
            $first_of_month = gmmktime(0,0,0,$month,1,$year);
            #remember that mktime will automatically correct if invalid dates are entered
            # for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
            # this provides a built in "rounding" feature to generate_calendar()

            $day_names = array(); #generate all the day names according to the current locale
            for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
                $day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name

            list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
            $weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
            $title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;  #note that some locales don't capitalize month and day names

            #Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
            @list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
            if($p) $p = '<span class="calendar-prev">'.($pl ? '<a href="'.htmlspecialchars($pl).'">'.$p.'</a>' : $p).'</span>&nbsp;';
            if($n) $n = '&nbsp;<span class="calendar-next">'.($nl ? '<a href="'.htmlspecialchars($nl).'">'.$n.'</a>' : $n).'</span>';
            $calendar = '<table class="calendar">'."\n".
                '<caption class="calendar-month">'.$p.($month_href ? '<a href="'.htmlspecialchars($month_href).'">'.$title.'</a>' : $title).$n;
            
            // check if who is using this calendar class
            if(isset($_COOKIE["argus"]))
            {
                // image path for administrators, members, and contributors
                $nextPath = "../miscs/images/Default/next.png";
                $previousPath = "../miscs/images/Default/previous.png";
            }
            else
            {
                $nextPath = "miscs/images/Default/next.png";
                $previousPath = "miscs/images/Default/previous.png";
            }
            
            // check the year and month for the next and previous link
            if($month == 12)
            {
                // if month is december, the next year would be january
                $nextYear = $year+1;
                $nextMonth = 1;
                
                $previousYear = $year;
                $previousMonth = $month - 1;
            }
            else if($month == 1)
            {
                // if month is january, the previous year would be december
                $previousYear = $year-1;
                $previousMonth = 12;
                
                $nextYear = $year;
                $nextMonth = $month + 1;
            }
            else
            {
                $nextMonth = $month + 1;
                $nextYear = $year;
                
                $previousMonth = $month - 1;
                $previousYear = $year;
            }
            
            $calendar .= "<br /><a href='index.php?event=calendar&year=".$previousYear."&month=".$previousMonth."'><img src='".$previousPath."' align='top'></a>&nbsp;&nbsp ";
            
            if(mysql_num_rows($eventsQuery) > 0)
            {
                // if there is an event, display the summary of events link where it will show
                // the summary of all events within a particular month
                $calendar .= "<a href='index.php?event=eventsummary&month=".$month."&year=".$year."'>Summary of Events</a>";
            }
            else
            {
                $calendar .= "Summary of Events";
            }
            
            $calendar .= "&nbsp;&nbsp<a href='index.php?event=calendar&year=".$nextYear."&month=".$nextMonth."'><img src='".$nextPath."' align='top'></a>";
            
            $calendar .= "</caption><tr>";

            if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
                #if day_name_length is >3, the full name of the day will be printed
                foreach($day_names as $d)
                    $calendar .= '<th abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
                $calendar .= "</tr>\n<tr>";
            }

            if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
            for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
                if($weekday == 7){
                    $weekday   = 0; #start a new week
                    $calendar .= "</tr>\n<tr>";
                }
                
                // search the database if the day is an event or not
                $result = $this -> searchDay($day);
                
                // check the result
                if($result == true)
                {
                    $calendar .= "<td><a href='index.php?event=events&day=".$day."&month=".$month."&year=".$year."'>".$day."</a></td>";
                }
                else 
                {
                    $calendar .= "<td>$day</td>";
                }
            }
            if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days

            $calendar.="</tr></table>";
            
            return $calendar;
        }
    }
?>
