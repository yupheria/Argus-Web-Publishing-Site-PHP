<?php
	/**
	 * Filename : ArchivesForm.php
	 * Description : contains functions and page properties for managing archives
	 * Date Created : December 28,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
     *  string displayBanner()
     *  string displayArchivesByYear($status)
     *  string displayArchivesByIssue($status, $year)
     *  string displayArchives($status, $year, $issue)
     *  void disableArchive($archiveId)
     *  void enableArchive($archiveId)
     *  void disableArchivesByIssue($issue, $year)
     *  void enableArchivesByIssue($issue, $year)
     *  void disableArchivesByYear($year)
     *  void enableArchivesByYear($year)
     *  void removeArchive($archiveId)
     *  void removeArchivesByIssue($issue, $year)
     *  void removeArchivesByYear($year)
     *  void limitTitle($title)
	 */
	 
	class ArchivesForm
	{
		
		/**
		 * Display Banner method: displays the menu and options for mananging archives
         * Return Type: String
		 */
		function displayBanner()
		{
			echo "
            <div class='bg2'>
			<h2><em>Archives manager</em></h2>
			<p align='center'>";
			
			// menus
			echo "
            <a href='archives.php'>Enabled</a> . 
			<a href='archives.php?event=disabled'>Disabled</a>";
			
			echo "
            </p>
			</div>";
			
			return;
		}
        
        /**
         * Display Archives Method: displays the archives
         * Parameter: $status
         * Return Type: String
         */
        function displayArchivesByYear($status)
        {
            // query the archives from the database by year
            $archivesQuery = mysql_query("SELECT year, COUNT(archive_id) FROM argus_archives WHERE status='".$status."' GROUP BY year") or die(mysql_error());
            
            // set the title of the page
            echo "<h3>".ucfirst(strtolower($status))."</h3>";
            echo "<div class='bg1' id='tablePanel'>";
            
            // check if the archives is empty or not
            if(mysql_num_rows($archivesQuery) == 0)
            {
                // notify the user that there are no archives
                echo "<p><h3 align='center'>There are no ".$status." archives</h3>";
            }
            else
            {
                // include the ajax tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
            
                // include the javascript of checking all and unchecking of checkboxes
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
            
                // display the archives by year in a table
                echo "<form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=".strtolower($status)."'>";
                echo "<table width='100%'>";
                echo "<tr>";
                echo "<th><input type='checkbox' onclick='toggleCheckBoxes(\"years\")'></th>";
                echo "<th>Year</th>";
                echo "<th>Number of Articles</th>";
                echo "<th>Action</th>";
                echo "</tr>";
            
                // display the years
                $color = true;
                
                while($row = mysql_fetch_array($archivesQuery))
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
                    $year = $row["year"];
                    $articlesCount = $row["COUNT(archive_id)"];
                    
                    // display the attributes
                    echo "<td><input type='checkbox' name='years[]' value='".$year."'></td>";
                    echo "<td><a href='archives.php?event=".strtolower($status)."&action=view&year=".$year."'>".$year."</a></td>";
                    echo "<td>".$articlesCount."</td>";
                    echo "<td>";
                    
                    // set the actions
                    if($status == "ENABLED")
                    {
                        // actions for ENABLED archives
                        echo "<a href='archives.php?event=".$_GET["event"]."&action=disableyear&year=".$year."' title='Disable'><img src='../miscs/images/Default/user_lock.png'></a>";
                    }
                    else
                    {
                        // actions for DISABLED archives
                        echo "<a href='archives.php?event=".$_GET["event"]."&action=enableyear&year=".$year."' title='Enable'><img src='../miscs/images/Default/archive_restore.png'></a> ";
                        echo "<a href='archives.php?event=".$_GET["event"]."&action=removeyear&year=".$year."' title='Remove'><img src='../miscs/images/Default/article_remove.png'></a>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
            
                echo "</table>";
                
                // display the buttons for managing the archives by year
                if($status == "ENABLED")
                {
                    // buttons for ENABLED archives
                    echo "<input type='submit' id='submit1' value='Disable' name='disableArchivesByYear'>";
                }
                else
                {
                    // buttons for DISABLED archives
                    echo "<input type='submit' id='submit1' value='Enable' name='enableArchivesByYear'> ";
                    echo "<input type='submit' id='submit1' value='Remove' name='removeArchivesByYear'>";
                }
                
                echo "</form>";
            }
            
            echo "</div>";
                
            return;
        }
        
        /**
         * Display Archives By Issue Method: displays all issue in a particular year
         * Parameter: $status, $year
         * Return Type: String
         */
        function displayArchivesByIssue($status, $year)
        {
            // query all issues on that year and the number of articles in each issue
            $archivesQuery = mysql_query("SELECT issue, COUNT(archive_id) FROM argus_archives WHERE status='".$status."' AND year='".$year."' GROUP BY issue") or die(mysql_error());
            
            // set the title of the page
            echo "<h3><a href='archives.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo; ".$year."</h3>";
            echo "<div class='bg1' id='tablePanel'>";
            
            // check for queried results
            if(mysql_num_rows($archivesQuery) == 0)
            {
                // notify the user that there are no archives for the year
                echo "<p><h3 align='center'>There are no archives for the year ".$year."</h3></p>";
            }
            else
            {
                // include the ajax tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
            
                // include the javascript of checking all and unchecking of checkboxes
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
                // display the archives by year in a table
                echo "<form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=".$_GET["event"]."&year=".$_GET["year"]."'>";
                echo "<table width='100%'>";
                echo "<tr>";
                echo "<th><input type='checkbox' onclick='toggleCheckBoxes(\"issues\")'></th>";
                echo "<th>Issue</th>";
                echo "<th>Number of Articles</th>";
                echo "<th>Action</th>";
                echo "</tr>";
            
                // display the years
                $color = true;
                
                while($row = mysql_fetch_array($archivesQuery))
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
                    $issue = $row["issue"];
                    $articleCount = $row["COUNT(archive_id)"];
                    
                    // display the attributes
                    echo "<td><input type='checkbox' name='issues[]' value='".$issue."'></td>";
                    echo "<td><a href='archives.php?event=".strtolower($status)."&year=".$year."&issue=".$issue."'>".$issue."</a></td>";
                    echo "<td>".$articleCount."</td>";
                    echo "<td>";
                    
                    // set the actions
                    if($status == "ENABLED")
                    {
                        // actions for ENABLED archives
                        echo "<a href='archives.php?event=".$_GET["event"]."&year=".$_GET["year"]."&action=disableissue&issue=".$issue."' title='Disable'><img src='../miscs/images/Default/user_lock.png'></a>";
                    }
                    else
                    {
                        // actions for DISABLED archives
                        echo "<a href='archives.php?event=".$_GET["event"]."&year=".$_GET["year"]."&action=enableissue&issue=".$issue."' title='Enable'><img src='../miscs/images/Default/archive_restore.png'></a> ";
                        echo "<a href='archives.php?event=".$_GET["event"]."&year=".$_GET["year"]."&action=removeissue&issue=".$issue."' title='Remove'><img src='../miscs/images/Default/article_remove.png'></a>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
            
                echo "</table>";
                
                // display the buttons for managing the archives by issue
                if($status == "ENABLED")
                {
                    // buttons for ENABLED archives
                    echo "<input type='submit' id='submit1' value='Disable' name='disableArchivesByIssue'>";
                }
                else
                {
                    // buttons for DISABLED archives
                    echo "<input type='submit' id='submit1' value='Enable' name='enableArchivesByIssue'> ";
                    echo "<input type='submit' id='submit1' value='Remove' name='removeArchivesByIssue'>";
                }
            
                echo "</form>";
            }
            
            echo "</div>";
            
            return;
        }
    
        /**
         * Display Archives: displays all archived articles
         * Parameter: $status, $year, $issue
         * Return Type: string
         */
        function displayArchives($status, $year, $issue)
        {
            // query all articles in the given issue and year and status
            $archivesQuery = mysql_query("SELECT archive_id, title, path, date_archived FROM argus_archives WHERE status = '".$status."' AND year = '".$year."' AND issue='".$issue."'") or die(mysql_error());
            
            // set the title of the page
            echo "<h3><a href='archives.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo; <a href='".$_SERVER["PHP_SELF"]."?event=".strtolower($status)."&year=".$year."'>".$year."</a> &raquo; ".$issue."</h3>";
            echo "<div class='bg1' id='tablePanel'>";
            
            // check if there are archives
            if(mysql_num_rows($archivesQuery) == 0)
            {
                // notify the user that there are no archives on that issue
                echo "<p><h3 align='center'>There are no archives on ".$issue." issue</h3></p>";
            }
            else
            {
                // include the ajax tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
            
                // include the javascript of checking all and unchecking of checkboxes
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
                // display the archives by year in a table
                echo "<form id='form_id' method='post' action='archives.php?event=".$_GET["event"]."&year=".$_GET["year"]."&issue=".$_GET["issue"]."'>";
                echo "<table width='100%'>";
                echo "<tr>";
                echo "<th><input type='checkbox' onclick='toggleCheckBoxes(\"archiveIds\")'></th>";
                echo "<th>Title</th>";
                echo "<th>Date Archived</th>";
                echo "<th>Action</th>";
                echo "</tr>";
            
                // display all archives
                $color = true;
                
                for($i=0; $i<mysql_num_rows($archivesQuery); $i++)
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
                    $archiveId = mysql_result($archivesQuery,$i,"archive_id");
                    $title = $this -> limitTitle(stripslashes(mysql_result($archivesQuery,$i,"title")));
                    $dateArchived = date("m/d/y", mysql_result($archivesQuery,$i,"date_archived"));
                    $path = mysql_result($archivesQuery,$i,"path");
                    
                    // display the attributes
                    echo "<td><input type='checkbox' name='archiveIds[]' value='".$archiveId."'></td>";
                    echo "<td><a href='".$path."' target='_blank'>".$title."</a></td>";
                    echo "<td>".$dateArchived."</td>";
                    echo "<td>";
                    
                    // set the actions
                    if($status == "ENABLED")
                    {
                        // actions for ENABLED archives
                        echo "<a href='archives.php?event=".$_GET["event"]."&year=".$_GET["year"]."&issue=".$_GET["issue"]."&action=disable&archive=".$archiveId."' title='Disable'><img src='../miscs/images/Default/user_lock.png'></a> ";
                    }
                    else
                    {
                        // actions for DISABLED archives
                        echo "<a href='archives.php?event=".$_GET["event"]."&year=".$_GET["year"]."&issue=".$_GET["issue"]."&action=enable&archive=".$archiveId."' title='Enable'><img src='../miscs/images/Default/archive_restore.png'></a> ";
                        echo "<a href='archives.php?event=".$_GET["event"]."&year=".$_GET["year"]."&issue=".$_GET["issue"]."&action=remove&archive=".$archiveId."' title='Remove'><img src='../miscs/images/Default/article_remove.png'></a>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
            
                echo "</table>";
                
                // set the buttons for managing the archives
                echo "<table width='100%'>";
                echo "<tr>"; 
                echo "<td>";
                
                if($status == "ENABLED")
                {
                    // buttons for enabled archives
                    echo "<input type='submit' id='submit1' value='Disable' name='disableArchive'>";
                }
                else
                {
                    // buttons for disabled archives
                    echo "<input type='submit' id='submit1' value='Enable' name='enableArchive'> ";
                    echo "<input type='submit' id='submit1' value='Remove' name='removeArchive'>";
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
         * Disable Archives method: disable an archive
         * Parameter: $archiveId
         */
        function disableArchive($archiveId)
        {
            // disable the selected archive Id
            mysql_query("UPDATE argus_archives SET status='DISABLED' WHERE archive_id = '".$archiveId."' AND status='ENABLED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Enable Archive Method: enable an archive
         * Parameter: $archiveId
         */
        function enableArchive($archiveId)
        {
            // enable the selected archive Id
            mysql_query("UPDATE argus_archives SET status='ENABLED' WHERE archive_id = '".$archiveId."' AND status='DISABLED'") or die(mysql_error());
            
            return;
        }
    
        /**
         * Disable Archives By Issue: disables archived articles in a selected issue
         * Parameter: $issue, year
         */
        function disableArchivesByIssue($issue, $year)
        {
            // disable all archives that issue in that year
            mysql_query("UPDATE argus_archives SET status='DISABLED' WHERE issue='".$issue."' AND year='".$year."' AND status='ENABLED'") or die(mysql_error());
            
            return;
        }
    
        /**
         * Enable Archives By Issue: enables archived articles in a selected issue
         * Parameter: $issue, $year
         */
        function enableArchivesByIssue($issue, $year)
        {
            // enable all archives in that issuue in that year
            mysql_query("UPDATE argus_archives SET status='ENABLED' WHERE issue='".$issue."' AND year='".$year."' AND status='DISABLED'") or die(mysql_error());
               
            return;
        }
        
        /**
         * Disable Archives By Year method: disabled all archives on that year
         * Parameter: $year
         */
        function disableArchivesByYear($year)
        {
            // disable all archives on that year
            mysql_query("UPDATE argus_archives SET status='DISABLED' WHERE year='".$year."' AND status='ENABLED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Enable Archives By Year method: enables all archives on that year
         * Parameter: $year
         */
        function enableArchivesByYear($year)
        {
            // enable all archives on that year
            mysql_query("UPDATE argus_archives SET status='ENABLED' WHERE year='".$year."' AND status='DISABLED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Remove Archive Method: removes archives permanently
         * parameter: $archiveId
         */
        function removeArchive($archiveId)
        {
            // query the information of the archive
            $archiveQuery = mysql_query("SELECT year,issue,path FROM argus_archives WHERE archive_id = '".$archiveId."'") or die(mysql_error());
            $path = mysql_result($archiveQuery,0,"path");
            $issue = mysql_result($archiveQuery,0,"issue");
            $year = mysql_result($archiveQuery,0,"year");
            
            // read the file contents
            $handle = fopen($path, "r");
            $content = fread($handle, filesize($path));
            
            // search the content if it has an image using the pregmatch
            preg_match_all('/<img.*?src\s*=\s*["\'](.+?)["\']/im', $content, $imagePaths);
            
            // delete the images from the archives folder
            for($i=0; $i<count($imagePaths[1]); $i++)
            {
                // the image path syntax that is expected is : images/123.jpg
                // to avoid confusion when deleting images, get the 123.jpg
                $explodedImagePath = explode("/", $imagePaths[1][$i]);
                
                // the exploded image path is an array
                // expected output: [images][123.jpg]
                // the "123.jpg" is found at the last portion of the array, get 123.jpg outside from the array
                $imageName = $explodedImagePath[count($explodedImagePath)-1];
                
                // set the REAL PATH of the image
                $modifiedImagePath = "../archives/".$year."/".$issue."/images/".$imageName;
                
                // check if the image exist
                if(file_exists($modifiedImagePath))
                {
                    // delete the image
                    unlink($modifiedImagePath);
                }
            }
            
            fclose($handle);
            
            // after deleting the images, delete the HTML file from the archives
            unlink($path);
            
            // after deleting the HTML file, delete the archive from the database
            mysql_query("DELETE FROM argus_archives WHERE archive_id = '".$archiveId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Remove Archives By Issue: removes archived articles in a selected issue
         * paramter: $issue, $year
         */
        function removeArchivesByIssue($issue,$year)
        {
            // query all articles that is in that issue and year
            $archivesQuery = mysql_query("SELECT archive_id FROM argus_archives WHERE issue='".$issue."' AND year='".$year."' AND status='DISABLED'") or die(mysql_error());
        
            // delete each archive
            for($i=0; $i<mysql_num_rows($archivesQuery); $i++)
            {
                $archiveId = mysql_result($archivesQuery,$i,"archive_id");
                $this -> removeArchive($archiveId);
            }
        
            return;
        }
        
        /**
         * Remove Archives By Year method: removes archived articles in a selected year
         * Parameter: $year
         */
        function removeArchivesByYear($year)
        {
            // query all archives from the database with the selected year
            $archivesQuery = mysql_query("SELECT archive_id FROM argus_archives WHERE year='".$year."' AND status='DISABLED'") or die(mysql_error());
        
            // delete each archive
            for($i=0; $i<mysql_num_rows($archivesQuery); $i++)
            {
                $archiveId = mysql_result($archivesQuery,$i,"archive_id");
                $this -> removeArchive($archiveId);
            }
            
            return;
        }
        
        /**
         * Limit Title Method: limits the title of the article
         * Parameter: $title
         * return type: string
         */
        function limitTitle($title)
        {
            // include the text limiter and limit the title to 5 words only
            require_once("class_libraries/TextLimiter.php");
            $textLimiter = new TextLimiter();
            
            // limit the title
            $title = $textLimiter -> limitText($title, 5);
            
            // return the truncated title
            return $title;
        }
	}
?>