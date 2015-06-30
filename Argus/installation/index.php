<?php
	/**
	 * Filename: databaseconfiguration.php
	 * Description: page for configuring the database
	 * Date Created: January 13,2007
	 * Author: Argus Team
	 */
	 
	/**
	 * METHODS SUMMARY
	 *	string validateName($name)
	 *	void createDatabase($host, $name, $username, $password)
     *  void insertDatabaseValues()
	 */
     
	/**
	 * Validate Name method: validates the name of the database for correct syntax
	 * Parameter: $name
	 * Return type: string
	 */
    function validateName($name)
    {
        // check if the name is empty or not
        $name = trim($name);
         
        // check if empty
        if(empty($name))
		{
			// if empty, return an error that the name is empty
			return "Please provide a database name";
		}
		// check the length of the database name
		else if(strlen($name) > 15 || strlen($name) < 5)
		{
			// return a message that the length is not valid
			return "Database name should be 5-15 characters long";
		}
        // check the syntax
        else if(eregi("[^a-zA-Z0-9\_\-]", $name))
        {
           // if it has a wrong syntx, return an error that the name is invalid
           return "Please provide a valid database name";              
        }
         
        return;
    }
	 
	 /**
	  * Create Database Method: creates a database
	  * Parameter: $host, $name, $username, $password
	  */
	function createDatabase($host, $name, $username, $password)
    {
	 	// create a connection to the mysql database
		mysql_connect($host, $username, $password) or die(mysql_error());
		
		// after creating a connection, create the database
		$databaseQuery = "CREATE DATABASE `".$name."` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;";
		
		// execute the database query
		// check for error
		// the only possible error that is expected is that the database already exist
		if(!mysql_query($databaseQuery))
		{
			// drop the current existing database and then create again the database
			mysql_query("DROP DATABASE `".$name."`;") or die(mysql_error());
			
			// try again to create
			mysql_query($databaseQuery) or die(mysql_error());
		}
		
		// after creating the database, create a connection to that database
		mysql_select_db($name) or die(mysql_error());
		
		// after creating a connection to that database, create the necessary tables needed
        // include the SQL table class which contains the scripts for creating the necessary tables
        include("../includes/class_libraries/SqlTables.php");
        $sqlTables = new SqlTables();
        
        // get the scripts and divide them
        $tablesScripts = explode(";", $sqlTables -> getCreateTablesScripts());
        
        // create each tables
        for($i=0; $i<count($tablesScripts); $i++)
        {
            if($tablesScripts[$i] != "")
            {
                // execute the script
                mysql_query($tablesScripts[$i]);
            }
        }
        
        // after creating the database, create the database script which will be named DatabaseConnector.php
        // create the comment of the script
		$databaseScript = "
        <?php
        /**
         * Filename : DatabaseConnector.php
         * Description :  Connects to the mysql database with the appropriate HOST, USERNAME, PASSWORD, and DATABASE
         * Date Created : ".date("M d,Y", time())."
         * Author : Argus Team
         */";
         
        // create the method summary of the script
        $databaseScript .= "
        /**
         * METHODS SUMMARY:
         *  DatabaseConnector()
         */";
            
         // create the content of the script
        $databaseScript .= "
        class DatabaseConnector
        {
            /**
             * Constuctor Summary: connects to the database with the HOST, USERNAME, PASSWORD, and DATABASE
             */
            function DatabaseConnector()
            {
                mysql_connect('".$host."', '".$username."', '".$password."') or die(mysql_error());
                mysql_select_db('".$name."') or die(mysql_error());
                
                return;
            }
        }
        ?>";
        
        // create the database connector.php
        $fileHandle = fopen("../includes/class_libraries/DatabaseConnector.php", w);
        fwrite($fileHandle, $databaseScript);
        fclose($fileHandle);
        
		return;
	}
    
    /**
     * Insert Database Values: inserts the default values of the database if freshly installed
     * Parameters: $studentInformationFileName
     */
    function insertDatabaseValues($studentInformationFileName)
    {
        // set the time which will be used
        $time = time();
        
        // check if there are values in the student information student file name
        if(!empty($studentInformationFileName))
        {
            // open the file and read the file
            $fileHandle = fopen($studentInformationFileName, "r");
            $sqlQuery = fread($fileHandle, filesize($studentInformationFileName));
            fclose($fileHandle);
            
            // try executing the query
            mysql_query($sqlQuery);
        }
        
        // set the default value for themes table
        $tablesQuery = "INSERT INTO `argus_themes` (`theme_id`, `name`, `path`, `status`) VALUES 
                        (1, 'Default', 'miscs/css/default.css', 'ENABLED'),
                        (2, 'Argus', 'miscs/css/argus.css', 'DISABLED')";
                        
        // execute the QUERY
        mysql_query($tablesQuery) or die(mysql_error());
        
        // set the default value for argus infos panel
        $tablesQuery = "INSERT INTO `argus_infos` (`name`,`date_modified`,`content`) VALUES
                        ('contact_us','".$time."',''),
                        ('terms_and_policies','".$time."','<p><strong>Terms and Policies of Membership</strong><br />1. To become a bona fide member of the publication community, you must be a currently enrolled student of Saint Louis University, College of Information and Computing Sciences.<br />2. Being a member, you will be given certain privileges to rate and comment on articles.<br />3. Comments on articles are moderated based on its contents. The following are content descriptions that are not allowed:<br /> a. Comments unrelated to the article.<br /> b. Comments with explicit contents.<br />4. Any violations of the above descriptions are subject to termination of membership.</p>'),
                        ('welcome_banner','".$time."','<p><strong>Congratulations!</strong> Welcome to Argus Online Publication. Once logged in, visit the <u>accounts manager</u> to create staff accounts, the <u>sections manager</u> to create publication sections, the <u>issues manager</u> to create issues. You can also change the look, style and contents of the website through the <u>web settings</u>. For more information, please refer to the <a href=\'#\'>help</a> section for the administrator tools.</p>'),
                        ('publication_name','".$time."','Argus;College of Information and Computing Science'),
                        ('interface_panel','".$time."','false'),
                        ('contributor_login','".$time."','true'),
                        ('member_login','".$time."','true'),
                        ('send_mail','".$time."','true'),
                        ('site_online','".$time."','Unavailable;The website is undergoing maintenance, sorry of the inconvenience;true'),
                        ('submit_article','".$time."','true'),
                        ('submit_limit','".$time."','2'),
                        ('admin_remote_login','".$time."','false'),
                        ('last_backup_date','".$time."',''),
                        ('editor_theme','".$time."','none')";
        
        // execute the QUERY
        mysql_query($tablesQuery) or die(mysql_error());
        
        // set the default value for issues
        $tablesQuery = "INSERT INTO `argus_issues` (`issue_id`, `name`, `description`, `date_created`, `date_publish`, `status`) VALUES 
                        (1, 'January', 'January Issue', '".$time."', '', 'ENABLED'),
                        (2, 'February', 'February Issue', '".$time."', '', 'ENABLED'),
                        (3, 'March', 'March Issue', '".$time."', '', 'ENABLED'),
                        (4, 'April', 'April Issue', '".$time."', '', 'ENABLED'),
                        (5, 'May', 'May Issue', '".$time."', '', 'ENABLED'),
                        (6, 'June', 'June Issue', '".$time."', '', 'ENABLED'),
                        (7, 'July', 'July Issue', '".$time."', '', 'ENABLED'),
                        (8, 'August', 'August Issue', '".$time."', '', 'ENABLED'),
                        (9, 'September', 'September Issue', '".$time."', '', 'ENABLED'),
                        (10, 'October', 'October Issue', '".$time."', '', 'ENABLED'),
                        (11, 'November', 'November Issue', '".$time."', '', 'ENABLED'),
                        (12, 'December', 'December Issue', '".$time."', '', 'ENABLED'),
						(13, 'Argus Opens', 'Maiden Issue', '".$time."', '', 'PUBLISHED')";
        
        // execute the QUERY
        mysql_query($tablesQuery) or die(mysql_error());
        
        // set default value for the categories
        $tablesQuery = "INSERT INTO `argus_categories` (`category_id`, `name`, `position`, `date_created`, `status`) VALUES
                        (1, 'Uncategorized', '1', '".$time."', 'DISABLED')";
        
        mysql_query($tablesQuery) or die(mysql_error());
        
        return;
    }
    
    /**
     * Upload Student Information Method: uploads the student information into the database
     * Parameter: $studentInformationName, $studentInformationTmpName
     * Return Type: String
     */
    function uploadStudentInformation($studentInformationName, $studentInformationTmpName)
    {
        // try uploading the file
        if(!move_uploaded_file($studentInformationTmpName, $studentInformationName))
        {
            // if the file has not been successfully uploaded, return an error message
            return "There was an error uploading the student information";
        }
        
        return;
    }
     
     /**
      * BUTTON EVENTS
      * createdatabase
      */
     
        // CREATE DATABASE BUTTON
         if(isset($_POST["create"]))
         {
             // get the inputs from the user
			 $host = $_POST["host"];
             $name = $_POST["name"];
             $username = $_POST["username"];
             $password = $_POST["password"];
             $studentInformationFileName = $_FILES["studentInformation"]["name"];
             $studentInformationTmpName = $_FILES["studentInformation"]["tmp_name"];
             
             // try a pre-connection to the server database with the given values
             if(!mysql_connect($host, $username, $password))
             {
                 $errors["connection"] = "The host, username, and or password is/are invalid";
             }
             
			 // validate the name of the database that is going to be created
			 $nameError = validateName($name);
             
             // upload the student file information if it is valid or not
             $studentInformationError = uploadStudentInformation($studentInformationFileName, $studentInformationTmpName);
			 
			 // check for errors
			 if($nameError == null)
			 {
			 	// after validating the name, create the tables using query
				createDatabase($host, $name, $username, $password, $studentInformationFileName);
                
                // check if there is an error on uploading the student information
                if($studentInformationError == null)
                {
                    // insert the default values of the tables
                    insertDatabaseValues($studentInformationFileName);
                }
                else
                {
                    // insert the default values of the table without the student information file name
                    insertDatabaseValues(null);
                }
                
                header("Location: accountconfiguration.php");
			 }
			 else
			 {
			 	// set the error which will be displayed below
				$errors["name"] = $nameError;
			 }
         }
     
     /**
      * END OF BUTTON TRIGGER EVENTS
      */
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<title>Argus Installation</title>
<link href='../miscs/css/default.css' rel='stylesheet' type='text/css'>
</head>
    <div id='header'>
        <h1>Argus Installation</h1>
        <h2>College of Information and Computing Sciences</h2>
    </div>
    <div id='content'>
        <div id='colTwo' style='width:892px'>
			<div class='bg2'>
                <h2><em>Welcome</em></h2>
                <p>Welcome to Argus Online Publication initial setup. This step by step setup provides the tools needed for the publication to function. The 

Editor-in-Chief must provide the necessary information below to complete the setup (with or without the assistance of the Technical Support).
                </p>
            </div>
            
            <h3>1/2 Database Configuration</h3>
            <div class='bg1'>
                <form method='post' action='<?php echo $_SERVER["PHP_SELF"] ?>' enctype="multipart/form-data">
					<p>
						<?php
							// display errors here
							if(isset($_POST["create"]) && $errors != null)
							{
								echo "<p><font color='red'>";
                                
                                // error on connection
                                if($errors["connection"] != null)
                                {
                                    echo $errors["connection"]."<br />";
                                }
                                
                                // error on name
                                if($errors["name"] != null)
                                {
                                    echo $errors["name"]."<br />";
                                }
                                
                                echo "</font></p>";
							}
						?>
					</p>
                    <p>
                        Database Information
                    </p>
					<!-- Database host -->
					<p id='box'>
						<b>Database Host</b><br>
						<input type='text' id='textbox' name='host' value='<?php echo $host ?>'><br>
						<i>This is usually localhost or a host name provided by the hoster.</i>
					</p>
                    <!-- Database name -->
                    <p id='box'>
                        <b>Database Name</b><br />
                        <input type='text' id='textbox' name='name' value='<?php echo $name ?>'><br />
                        <i>Name for the database that is going to be created.</i>
                    </p>
                    <!-- Database username -->
                    <p id='box'>
                        <b>Database Username</b><br />
                        <input type='text' id='textbox' name='username' value='<?php echo $username ?>'><br />
                        <i>This can be the default MySQL username "root" or a username provided by the hoster, or one that you have created while setting up 

your database server.</i>
                    </p>
                    <!-- Database password -->
                    <p id='box'>
                        <b>Database Password</b><br />
                        <input type='text' id='textbox' name='password' value='<?php echo $password ?>'><br />
                        <i>For site security using a password for the MySQL account is mandatory. This is the same password used to access your database. 

This may again be preset by your hoster.</i>
                    </p>
                    <!-- Student Information -->
                    <p id='box'>
                        <b>Students</b><br />
                        <input type='file' name='studentInformation'><br />
                        <i>Student information file containing the list of currently enrolled students of the college that is to be imported into the 

database for registration referencing purposes.</i>
                    </p>
                    <p align='center'>
                        <input type='submit' id='submit2' name='create' value='create database'>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <div id='footer'>
        <p>powered by argus</p>
    </div>
</html>
