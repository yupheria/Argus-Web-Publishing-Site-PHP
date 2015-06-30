<?php
	/**
	 * Filename : SettingsForm.php
	 * Description : class file for managing web settings
	 * Date Created : December 23, 2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	string displayBanner()
     *  string displayInfos($intoType, $content)
     *  string displayThemes()
     *  void loadTheme($themeId)
     *  void updateInterfacePanel($result)
     *  string getErrors()
     *  string validateSubmitLimit($submitLimit)
     *  void backUpDatabase($archiveType)
     *  boolean updateStudentInformation($studentFileName, $studentTmpName)
	 */
	
	class SettingsForm
	{
		var $errors;
		
		/**
		 * Display Banner Method: displays the menus for managing the website
		 * Return type: string
		 */
		function displayBanner()
		{
			echo "
            <div class='bg2'>
			<h2><em>Web Settings</em></h2>
			<p align='center'>";
			
			// menus
			echo "
            <a href='settings.php?event=welcomebanner'>Welcome Banner</a> . 
            <a href='settings.php?event=termsandpolicies'>Terms and Policies</a> . 
			<a href='settings.php?event=contactus'>Contact us</a> . 
			<a href='settings.php?event=themes'>Web Theme</a> . 
            <a href='configuration.php'>Configuration</a>";
            			
			echo "
            </p>
			</div>";
			
			return;
		}
        
        /**
         * Display Terms and Policies: displays the terms and policies page to be viewed by the administrator
         * Paramter: $infoType
         * Return Type: String
         */
        function displayInfo($infoType)
        {
            // determine the info type
            if($infoType == "TERMS AND POLICIES")
            {
                // query the terms and policies from the database
                $infoQuery = mysql_query("SELECT date_modified, content FROM argus_infos WHERE name = 'terms_and_policies'") or die(mysql_error());
                
                // title for terms and policies
                echo "<h3>Terms and Policies</h3>";
                echo "<div class='bg1'>";
                echo "<p>Terms and Policies Information</p>";
            }
            else if($infoType == "CONTACT US")
            {
                // query the contact us from the database
                $infoQuery = mysql_query("SELECT date_modified, content FROM argus_infos WHERE name='contact_us'") or die(mysql_error());
                
                // title for contacts us
                echo "<h3>Contact Us</h3>";
                echo "<div class='bg1'>";
                echo "<p>Contact Us Information</p>";
            }
            else
            {
                // query the welcome banner from the database
                $infoQuery = mysql_query("SELECT date_modified, content FROM argus_infos WHERE name='welcome_banner'") or die(mysql_error());
                
                // query also the publication name from the database
                $publicationNameQuery = mysql_query("SELECT date_modified, content FROM argus_infos WHERE name='publication_name'") or die(mysql_error());
                
                // title for the conatct banner
                echo "<h3>Welcome Banner</h3>";
                echo "<div class='bg1'>";
                echo "<p>Welcome Banner Information</p>";
            }
            
            // set the attributes
            $dateModified = date("F d, Y", mysql_result($infoQuery,0,"date_modified"));
            $content = mysql_result($infoQuery,0,"content");
            
            echo "<p id='box'>";
            
            // display the title and subtitle of the publication name if being accessed is welcome banner
            if($infoType == "WELCOME BANNER")
            {
                // arrange the title and subtitle separating them into title and subtitle
                $titleAndSubtitle = mysql_result($publicationNameQuery,0,"content");
                $titleAndSubtitle = explode(";", $titleAndSubtitle);
                
                $title = $titleAndSubtitle[0];
                $subtitle = $titleAndSubtitle[1];
                
                echo "Title: ".$title."<br />";
                echo "Subtitle: ".$subtitle."<br />";
            }
            
            echo "Date Modified: ".$dateModified."<br>";
            echo "</p>";
            
            // set the buttons
            echo "<p align='right'>";                         
            
            if($infoType == "TERMS AND POLICIES")
            {
                // display the buttons for managing the terms and policies
                echo "<a href='infosedit.php?info=termsandpolicies'><input type='button' id='submit1' value='edit'></a>";
            }
            else if($infoType == "CONTACT US")
            {
                // display the buttons for managing the contact us
                echo "<a href='infosedit.php?info=contactus'><input type='button' id='submit1' value='edit'></a>";
            }
            else
            {
                // display the buttons for managing the welcome banner
                echo "<a href='infosedit.php?info=welcomebanner'><input type='button' id='submit1' value='edit'></a>";
            }
            
            echo "</p>";
            echo "<p>".$content."</p>";
            echo "</div>";
            
            return;
        }
        
        /**
         * Update Infos method: updates the infos (terms and policies/contact us)
         * Parameter: $intoType, $content
         */
        function updateInfos($infoType, $content)
        {
            // determine the info type
            if($infoType == "termsandpolicies")
            {
                // update the terms and policies
                mysql_query("UPDATE argus_infos SET content='".$content."', date_modified='".time()."' WHERE name='terms_and_policies'") or die(mysql_error());
            }
            else if($infoType == "contactus")
            {
                // update the contacts us page
                mysql_query("UPDATE argus_infos SET content='".$content."', date_modified='".time()."' WHERE name='contact_us'") or die(mysql_error());
            }
            else if($infoType == "welcomebanner")
            {
                // update the welcome banner page
                mysql_query("UPDATE argus_infos SET content='".$content."', date_modified='".time()."' WHERE name='welcome_banner'") or die(mysql_error());
            }
            else
            {
                // update the publication name
                mysql_query("UPDATE argus_infos SET content='".$content."', date_modified='".time()."' WHERE name='publication_name'") or die(mysql_error());
            }
            
            return;
        }
        
        /**
         * Display Themes method: displays the available color theme options
         * Return Type: string
         */
        function displayThemes()
        {
            // query all themes from the database
            $themesQuery = mysql_query("SELECT theme_id, name, status FROM argus_themes") or die(mysql_error());
            
            echo "<h3>Web Theme</h3>";
            echo "<div class='bg1' id='tablePanel'>";
            echo "<table width='100%'>";
            echo "<tr>";
            echo "<th align='center'>Name</th>";
            echo "<th align='center'>Status</th>";
            echo "</tr>";
            
            // display all the themes
            $color = true;
            
            for($i=0; $i<mysql_num_rows($themesQuery); $i++)
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
                $themeId = mysql_result($themesQuery,$i,"theme_id");
                $name = mysql_result($themesQuery,$i,"name");
                $status = mysql_result($themesQuery,$i,"status");
                
                // display the attributes
                echo "<td align='center'><a href='settings.php?event=".$_GET["event"]."&action=load&theme=".$themeId."'>".$name."</a></td>";
                echo "<td align='center'>".$status."</td>";
                
                
                
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</div>";
            
            return;
        }
        
        /**
         * Load Theme Method: loads the theme
         * Parameter: $themeId
         */
        function loadTheme($themeId)
        {
            // validate if the themeId exists from the database
            $themeQuery = mysql_query("SELECT theme_id FROM argus_themes WHERE theme_id = '".$themeId."'") or die(mysql_error());
            
            // check the result
            if(mysql_num_rows($themeQuery) > 0)
            {
                // set the enable theme to disabled
                mysql_query("UPDATE argus_themes SET status='DISABLED' WHERE status='ENABLED'") or die(mysql_error());
                
                // set the theme selected as enabled
                mysql_query("UPDATE argus_themes SET status='ENABLED' WHERE theme_id = '".$themeId."'") or die(mysql_error());
            }
        }
    
        /**
         * Update Interface Panel method: updates the interface
         * Parameter: $interfacePanel, $memberLogin, $contributorLogin, $mailUtility, $submitArticle, $siteOnline, $submitLimit, $editorTheme
         */
        function updateSettings($interfacePanel, $memberLogin, $contributorLogin, $mailUtility, $submitArticle, $siteOnline, $submitLimit, $adminRemoteLogin, $editorTheme)
        {
            // validate the submitLimit
            $submitLimitError = $this -> validateSubmitLimit($submitLimit);
            
            if($submitLimitError == null)
            {            
                // update the interface panel
                mysql_query("UPDATE argus_infos SET content='".$interfacePanel."' WHERE name='interface_panel'") or die(mysql_error());
                
                // update the member login
                mysql_query("UPDATE argus_infos SET content='".$memberLogin."' WHERE  name='member_login'") or die(mysql_error());
                
                // update the contributor login
                mysql_query("UPDATE argus_infos SET content='".$contributorLogin."' WHERE name='contributor_login'") or die(mysql_error());
                
                // update the mail utility
                mysql_query("UPDATE argus_infos SET content='".$mailUtility."' WHERE name='send_mail'") or die(mysql_error());
                
                // update the submit article
                mysql_query("UPDATE argus_infos SET content='".$submitArticle."' WHERE name='submit_article'") or die(mysql_error());
                
                // update the site online
                mysql_query("UPDATE argus_infos SET content='".$siteOnline."' WHERE name='site_online'") or die(mysql_error());
                
                // update the submit limit
                mysql_query("UPDATE argus_infos SET content='".$submitLimit."' WHERE name='submit_limit'") or die(mysql_error());
                
                // update the administrator remote login
                mysql_query("UPDATE argus_infos SET content='".$adminRemoteLogin."' WHERE name='admin_remote_login'") or die(mysql_error());
                
                // update the editor theme
                mysql_query("UPDATE argus_infos SET content='".$editorTheme."' WHERE name='editor_theme'") or die(mysql_error());
                
                // return successful update
                return true;
            }
            else
            {
                // set the error that was committed
                $this -> errors = array("submitLimit" => $submitLimitError);
                
                // return unsuccessful update
                return false;
            }
            
            return;
        }
        
        /**
         * Get Errors Method: returns the error that was committed
         * Return Type: string
         */
        function getErrors()
        {
            // return the errors
            return $this -> errors;
        }
        
        /**
         * Validate Submit limit method: validates the submit limit value if it is correct
         * Parameter: $submitLimit
         * Return Type: string
         */
        function validateSubmitLimit($submitLimit)
        {
            // check if the submit limit has empty or not
            $submitLimit = trim($submitLimit);
            
            if(empty($submitLimit))
            {
                // return an error that the user has to provide a value for the submit limit
                return "Please provide an Article submit limit";
            }
            // check the value if it is a digit or not
            else if(!ctype_digit($submitLimit))
            {
                // return an error that the value of the submit limit should be an integer
                return "Article submit limit should be an integer";
            }
            // check the value for minimum and maximum
            else if($submitLimit < 1 || $submitLimit > 999)
            {
                // return an error that the value should be be minimum of 1 and max of 999
                return "Article submit limit value should be in the range of 1 - 999";
            }
            
            return;
        }
        
        /**
         * Back Up Database: backs up the database
         */
        function backupDatabase()
        {
            // get the current name of the database
            $databaseNameQuery = mysql_query("select DATABASE()") or die(mysql_error());
            $databaseName = mysql_result($databaseNameQuery,0);
            
            // create the sql statement for the database name
            $sql = "CREATE DATABASE `".$databaseName."` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci; USE `".$databaseName."`;\n";
            
            // get the script for creating database which is found in Sql Tables Class
            include("class_libraries/SqlTables.php");
            $sqlTables = new SqlTables();
            
            // retrieve the sql tables and append them to the script
            $sql .= $sqlTables -> getCreateTablesScripts();
            
            // get the name of the tables in my mysql
            $tablesQuery = mysql_query("SHOW TABLES") or die(mysql_error());
            
            // for each table, query the values inside
            for($i=0; $i<mysql_num_rows($tablesQuery); $i++)
            {
                // set the table
                $table = mysql_result($tablesQuery,$i);
                
                // get all the values inside the table
                $tableValuesQuery = mysql_query("SELECT * FROM ".$table) or die(mysql_error());
                
                if(mysql_num_rows($tableValuesQuery) > 0)
                {
                    // set the sql statement for the table
                    $sql .= "INSERT INTO `".$table."` (";
                    
                    for($a=0; $a<mysql_num_fields($tableValuesQuery); $a++)
                    {
                        // get the field name
                        $fieldName = mysql_field_name($tableValuesQuery,$a);
                        
                        // append the field name to the sql statement
                        if($a==0)
                        {
                            $sql .= "`".$fieldName."`";
                        }
                        else
                        {
                            $sql .= ",`".$fieldName."`";
                        }
                    }
                
                    // close the fieldnames
                    $sql .= ") VALUES ";
                }
                
                
                // insert the values of the table
                for($b=0; $b<mysql_num_rows($tableValuesQuery); $b++)
                {
                    // opening parenthisis
                    if($b==0)
                    {
                        $sql .= "(";
                    }
                    else
                    {
                        $sql .= ",(";
                    }
                    
                    for($j=0; $j<mysql_num_fields($tableValuesQuery); $j++)
                    {
                        $fieldName = mysql_field_name($tableValuesQuery,$j);
                        
                        // get that table value in that field
                        $tableValue = mysql_escape_string(mysql_result($tableValuesQuery,$b,$fieldName));
                        
                        // append the value in the sql statement
                        if($j==0)
                        {
                            $sql .= "'".$tableValue."'";
                        }
                        else
                        {
                            $sql .= ",'".$tableValue."'";
                        }
                    }
                    
                    // closing the parenthesis
                    if($b == mysql_num_rows($tableValuesQuery)-1)
                    {
                        $sql .= ");\n";
                    }
                    else
                    {
                        $sql .= ")";
                    }
                }
            }
            
            // write the sql statement into a text file
            $fileHandle = fopen("../DatabaseBackup.dmp", "w");
            fwrite($fileHandle, $sql);
            fclose($fileHandle);
            
            return;
        }
        
        /**
         * Back Up Website: backs up the entire website files
         * Parameter: $archiveType
         */
        function backupWebsite($archiveType)
        {
            // back up the database
            $this -> backupDatabase();
            
            // include the archive class and archive the website in a zip file
            include("class_libraries/Archive.php");
            
            // create a directory for the back up.  The name of the directory should be in time
            $directoryName = date("m.d.y g.i.A", time());
            
            // determine the archive type
            // create an archive type in zip form
            if($archiveType == "ZIP")
            {
                // create a new zip file passing the destination of the zip file
                $zip = new zip_file($directoryName.".ZIP");
                
                // create the options of the zip file
                // Create archive in memory (* if archive memory is set to 0, the file is not downloadable
                // Recurse through subdirectories
                // Store file paths in archive
                $zip -> set_options(array("inmemory"=>1, "recurse"=>1, "storepaths"=>1));
                
                // add the files that is to be zipped
                $zip -> add_files("../");
                
                // create the archive in memory
                $zip -> create_archive();
                
                // let the user download the file
                $zip -> download_file();
            }
            else
            {
                // create a tar file passing the destination of the tar file
                $tar = new gzip_file("../backup/".$directoryName.".tgz");
                
                // Assume the following script is executing in /var/www/htdocs/test
                // Set basedir to "../..", which translates to /var/www
                // Overwrite /var/www/htdocs/test/test.tgz if it already exists
                // Set compression level to 1 (lowest)
                $tar->set_options(array('basedir' => ".", 'overwrite' => 1, 'level' => 1));
                
                // Add entire htdocs directory and all subdirectories
                // Add all php files in htsdocs and its subdirectories
                $tar->add_files(array("../"));
                
                // create the archive
                $tar->create_archive();
            }
            
            // delete the temporarily created sql dump file
            if(file_exists("../DatabaseBackup.dmp"))
            {
                unlink("../DatabaseBackup.dmp");
            }
            
            // update the back up date
            mysql_query("UPDATE argus_infos SET date_modified = '".time()."' WHERE name='last_backup_date'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Update Student Information method: updates the old students from the database with the new information
         * Parameter: $fileName, $tmpName
         * Return Type: boolean
         */
        function updateStudentInformation($fileName, $tmpName)
        {
            // try copying the file from the client right into the server
            if(move_uploaded_file($tmpName, $fileName))
            {
                // read the file contents of the new students
                $fileHandle = fopen($fileName, "r");
                $sqlStatement = fread($fileHandle, filesize($fileName));
                fclose($fileHandle);
                
                // create a temporary table for students where to insert all the new students
                mysql_query("CREATE TABLE `argus_temp_slu_students` (
                        `id_number` varchar(10) NOT NULL,
                        `first_name` varchar(255) NOT NULL,
                        `last_name` varchar(255) NOT NULL,
                        `middle_initial` varchar(1) NOT NULL,
                        `status` varchar(15) NOT NULL,
                        PRIMARY KEY  (`id_number`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;") or die(mysql_error());
                
                // try to execute the sql statement from the uploaded file
                if(mysql_query($sqlStatement))
                {
                    // query the id number of the administrator
                    $administratorIdNumberQuery = mysql_query("SELECT id_number FROM argus_accounts WHERE position = 'ADMINISTRATOR'") or die(mysql_error());
                    $administratorIdNumber = mysql_result($administratorIdNumberQuery,0,"id_number");
                    
                    // delete the student information of the administrator in the new student tables
                    mysql_query("DELETE FROM argus_temp_slu_students WHERE id_number = '".$administratorIdNumber."'") or die(mysql_error());
                    
                    // after the successful insert of new students do comparison of the old student information with
                    // the new student information table
                    // query all the members from the database
                    $accountsQuery = mysql_query("SELECT id_number FROM argus_accounts WHERE position = 'MEMBER' AND status != 'GUEST'") or die(mysql_error());
                    
                    // validate the id number of the student if it exists in the new table
                    for($i=0; $i<mysql_num_rows($accountsQuery); $i++)
                    {
                        // set the attribute
                        $idNumber = mysql_result($accountsQuery,$i,"id_number");
                        
                        // check if the id number exists in the new table
                        $checkIdNumberQuery = mysql_query("SELECT id_number FROM argus_temp_slu_students") or die(mysql_error());
                        
                        if(mysql_num_rows($checkIdNumberQuery) == 0)
                        {
                            // if nothing was queried from the database, it is assumed that the student is not already part of the college
                            // if the student is not already part of the college, delete the member from the argus_accounts
                            mysql_query("DELETE FROM argus_accounts WHERE id_number = '".$idNumber."'") or die(mysql_error());
                            
                            // also delete the student from the slu students table
                            mysql_query("DELETE FROM argus_slu_students WHERE id_number = '".$idNumber."'") or die(mysql_error());
                        }
                        else
                        {
                            // if the id number exist, delete the id number in the new student table
                            mysql_query("DELETE FROM argus_temp_slu_students WHERE id_number = '".$idNumber."'") or die(mysql_error());
                        }
                    }
                    
                    // for the unregistered, query all the unregistered and remove those who does not exist in the new table
                    $accountsQuery = mysql_query("SELECT id_number FROM argus_slu_students WHERE status = 'UNREGISTERED'") or die(mysql_error());
                    
                    for($i=0; $i<mysql_num_rows($accountsQuery); $i++)
                    {
                        //set the attribute
                        $idNumber = mysql_result($accountsQuery,$i,"id_number");
                        
                        // check if the id number exists in the new table
                        $checkIdNumberQuery = mysql_query("SELECT id_number FROM argus_temp_slu_students WHERE id_number = '".$idNumber."'") or die(mysql_error());
                    
                        if(mysql_num_rows($checkIdNumberQuery) == 0)
                        {
                            // if nothing was queried from the database, it is assumed that the student is not already part of the college
                            // if the student is not already a part of the college, delete the student from the argus_slu_students
                            mysql_query("DELETE FROM argus_slu_students WHERE id_number = '".$idNumber."'") or die(mysql_error());
                        }
                        else
                        {
                            // if the id number exist, delete the student in the new student table
                            mysql_query("DELETE FROM argus_temp_slu_students WHERE id_number = '".$idNumber."'") or die(mysql_error());
                        }
                    }
                    
                    // after the validation has been completed, transfer the students that were left from the
                    // new slu student table into the old slu students. Merge them together
                    // query all the students in the new slu student table
                    $accountsQuery = mysql_query("SELECT id_number, first_name, last_name, middle_initial, status FROM argus_temp_slu_students") or die(mysql_error());
                
                    // include them on the old table
                    for($i=0; $i<mysql_num_rows($accountsQuery); $i++)
                    {
                        // set the attributes
                        $idNumber = mysql_result($accountsQuery,$i,"id_number");
                        $firstName = mysql_result($accountsQuery,$i,"first_name");
                        $lastName = mysql_result($accountsQuery,$i,"last_name");
                        $middleInitial = mysql_result($accountsQuery,$I,"middle_initial");
                        $status = mysql_result($accountsQuery,$i,"status");
                        
                        // insert the new student in the new table
                        mysql_query("INSERT INTO argus_slu_students(id_number, first_name, last_name, middle_initial, status) VALUES
                                    ('".$idNumber."','".$firstName."','".$lastName."','".$middleInitial."','".$status."')") or die(mysql_error());
                    }
                
                    // Delete the temporary table from the database
                    mysql_query("DROP TABLE `argus_temp_slu_students`") or die(mysql_error());
                    
                    // Delete also the file that was uploaded
                    unlink($fileName);
                    
                    // return successful update of information
                    return true;
                }
                else
                {
                    // return unsuccessful update of information
                    return false;
                }
            }
            else
            {                
                // return unsuccessful update of information
                return false;
            }
            
            return;
        }
	}
?>