<?php
	/**
	 * Filename : configuration.php
	 * Description : page for managing the global configuration of the server
	 * Date Created : January 19,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the settings form class
	require_once("../includes/SettingsForm.php");
	$form = new SettingsForm();
    
    // query all the settings that is currently stored in the database
    // interface panel settings
    $interfacePanelQuery = mysql_query("SELECT content FROM argus_infos WHERE name='interface_panel'") or die(mysql_error());
    $interfacePanel = mysql_result($interfacePanelQuery,0,"content");
    
    // member login settings
    $memberLoginQuery = mysql_query("SELECT content FROM argus_infos WHERE name='member_login'") or die(mysql_error());
    $memberLogin = mysql_result($memberLoginQuery,0,"content");
    
    // contributor login settings
    $contributorLoginQuery = mysql_query("SELECT content FROM argus_infos WHERE name='contributor_login'") or die(mysql_error());
    $contributorLogin = mysql_result($contributorLoginQuery,0,"content");
    
    // mail utility settings
    $mailUtilityQuery = mysql_query("SELECT content FROM argus_infos WHERE name='send_mail'") or die(mysql_error());
    $mailUtility = mysql_result($mailUtilityQuery,0,"content");
    
    // submit article settings
    $submitArticleQuery = mysql_query("SELECT content FROM argus_infos WHERE name='submit_article'") or die(mysql_error());
    $submitArticle = mysql_result($submitArticleQuery,0,"content");
    
    // site online and offline
    $siteOnlineQuery = mysql_query("SELECT content FROM argus_infos WHERE name='site_online'") or die(mysql_error());
    $siteOnline = explode(";",mysql_result($siteOnlineQuery,0,"content"));
    
    // submit limit
    $submitLimitQuery = mysql_query("SELECT content FROM argus_infos WHERE name='submit_limit'") or die(mysql_error());
    $submitLimit = mysql_result($submitLimitQuery,0,"content");
    
    // admin remote login settings
    $adminRemoteLoginQuery = mysql_query("SELECT content FROM argus_infos WHERE name='admin_remote_login'") or die(mysql_error());
    $adminRemoteLogin = mysql_result($adminRemoteLoginQuery,0,"content");
    
    // query the last back up date
    $lastBackUpDateQuery = mysql_query("SELECT date_modified FROM argus_infos WHERE name='last_backup_date'") or die(mysql_error());
    $lastBackUpDate = date("M d,Y", mysql_result($lastBackUpDateQuery,0,"date_modified"));
    
    // query the current editor theme
    $editorThemeQuery = mysql_query("SELECT content FROM argus_infos WHERE name='editor_theme'") or die(mysql_error());
    $editorTheme = mysql_result($editorThemeQuery,0,"content");
    
    /**
     * BUTTON TRIGGER EVENTS
     *  Save
     *  Backup
     *  Student Update
     */
        
        // STUDENT UPDATE BUTTON
        if(isset($_POST["updateStudentInformation"]))
        {
            // get the input from the user
            $studentFileName = $_FILES["studentFile"]["name"];
            $studentTmpName = $_FILES["studentFile"]["tmp_name"];
            
            // update the student information
            $result = $form -> updateStudentInformation($studentFileName, $studentTmpName);
            
            if($result == true)
            {
                // set the success message which will be displayed below
                $successMessage = "The student information has been successfully updated";
            }
            else
            {
                // set the error message which will be displayed below
                $errorMessage = "An error occured during the update of student information<br />The student information has not been updated";
            }
        }
    
        // SAVE BUTTON 
        if(isset($_POST["save"]))
        {
            // get the inputs from the user
            $interfacePanel = $_POST["interfacePanel"];
            $memberLogin = $_POST["memberLogin"];
            $contributorLogin = $_POST["contributorLogin"];
            $mailUtility = $_POST["mailUtility"];
            $submitArticle = $_POST["submitArticle"];
            $submitLimit = $_POST["submitLimit"];
            $adminRemoteLogin = $_POST["adminRemoteLogin"];
            $editorTheme = $_POST["wordEditorTheme"];
            
            // arrange the site status
            $siteOnline[0] = $_POST["title"];
            $siteOnline[1] = $_POST["content"];
            $siteOnline[2] = $_POST["siteOnline"];
            $siteStatus = $siteOnline[0].";".$siteOnline[1].";".$siteOnline[2];
            
            // update the interface panel
            $result = $form -> updateSettings($interfacePanel,$memberLogin, $contributorLogin, $mailUtility, $submitArticle, $siteStatus, $submitLimit, $adminRemoteLogin, $editorTheme);
            
            // check the result
            if($result == true)
            {
                // set the success message
                $successMessage = "Saved";
            }
            else
            {
                // get the errors that was committed
                $errors = $form -> getErrors();
            }            
        }
        
        // Backup Button
        if(isset($_POST["backup"]))
        {
            $archiveType = $_POST["backupType"];
            
            // back up the database
            $form -> backupWebsite($archiveType);
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
    ?>
        <h3>Configuration</h3>
        <div class='bg1'>
            <form method='post' action='<?php echo $_SERVER["PHP_SELF"] ?>' enctype='multipart/form-data'>
                <?php
                    // show the success message of update of students
                    if(isset($_POST["updateStudentInformation"]) && $result == true)
                    {
                        echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
                    }
                    
                    // show the error message when update of students
                    if(isset($_POST["updateStudentInformation"]) && $result == false)
                    {
                        echo "<p><font color='red'>".$errorMessage."</font></p>";
                    }
                    
                    // show "saved" if saved
                    if(isset($_POST["save"]) && $result == true)
                    {
                        echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
                    }
                    
                    // show the errors that was committed
                    if(isset($_POST["save"]) && $result == false)
                    {
                        echo "<p><font color='red'>";
                        
                        // error for the submit limit
                        if($errors["submitLimit"] != null)
                        {
                            echo $errors["submitLimit"];
                        }
                        
                        echo "</font></p>";
                    }
                    
                    if(isset($_POST["backup"]))
                    {
                        // output a message
                        echo "<p align='center'><font color='green'>The website has been successfully saved</font></p>";
                    }
                ?>
                <p>Configuration Information</p>
                <!-- Word Editor Theme -->
                <p id='box'>
                    <b>Word Editor Theme</b><br />
                    <select id='textbox' name='wordEditorTheme'>
                    <?php
                        // check the current status of the theme
                        if($editorTheme == "none")
                        {
                            echo "<option value='none' selected='selected'>None</option>";
                        }
                        else
                        {
                            echo "<option value='none'>None</option>";
                        }
                        
                        if($editorTheme == "silver")
                        {
                            echo "<option value='silver' selected='selected'>Silver</option>";
                        }
                        else
                        {
                            echo "<option value='silver'>Silver</option>";
                        }
                        
                        if($editorTheme == "word")
                        {
                            echo "<option value='word' selected='selected'>Word</option>";
                        }
                        else
                        {
                            echo "<option value='word'>Word</option>";
                        }
                    ?>
                    </select>
                </p>
                <!-- switch interface panel -->
                <p id='box'>
                    <b>Switch Interface Panel</b><br />
                    <select id='textbox' name='interfacePanel'>
                    <?php
                        // check the current status of the interface panel
                        if($interfacePanel == "true")
                        {
                            echo "<option value='true' selected='selected'>Yes</option>";
                        }
                        else
                        {
                            echo "<option value='true'>Yes</option>";
                        }
                        
                        if($interfacePanel == "false")
                        {
                            echo "<option value='false' selected='selected'>No</option>";
                        }
                        else
                        {
                            echo "<option value='false'>No</option>";
                        }
                    ?>
                    </select>
                </p>
                <!-- member login -->
                <p id='box'>
                    <b>Allow Member Login</b><br />
                    <select id='textbox' name='memberLogin'>
                    <?php
                        // check the current status of the member login
                        if($memberLogin == "true")
                        {
                            echo "<option value='true' selected='selected'>Yes</option>";
                        }
                        else
                        {
                            echo "<option value='true'>Yes</option>";
                        }
                        
                        if($memberLogin == "false")
                        {
                            echo "<option value='false' selected='selected'>No</option>";
                        }
                        else
                        {
                            echo "<option value='false'>No</option>";
                        }
                    ?>
                    </select>
                </p>
                <!-- contributor login -->
                <p id='box'>
                    <b>Allow Contributor Login</b><br />
                    <select id='textbox' name='contributorLogin'>
                    <?php
                        // check the current status of the contributor login
                        if($contributorLogin == "true")
                        {
                            echo "<option value='true' selected='selected'>Yes</option>";
                        }
                        else
                        {
                            echo "<option value='true'>Yes</option>";
                        }
                        
                        if($contributorLogin == "false")
                        {
                            echo "<option value='false' selected='selected'>No</option>";
                        }
                        else
                        {
                            echo "<option value='false'>No</option>";
                        }
                    ?>
                    </select>
                </p>
                <!-- mail function -->
                <p id='box'>
                    <b>Allow Mail Utility</b><br />
                    <select id='textbox' name='mailUtility'>
                    <?php
                        // check the current status of the send mail
                        if($mailUtility == "true")
                        {
                            echo "<option value='true' selected='selected'>Yes</option>";
                        }
                        else
                        {
                            echo "<option value='true'>Yes</option>";
                        }
                        
                        if($mailUtility == "false")
                        {
                            echo "<option value='false' selected='selected'>No</option>";
                        }
                        else
                        {
                            echo "<option value='false'>No</option>";
                        }
                    ?>
                    </select>
                </p>
                <!-- submit article -->
                <p id='box'>
                    <b>Allow Article Submission</b><br />
                    <select id='textbox' name='submitArticle'>
                    <?php
                        // check the current status of the submit mail
                        if($submitArticle == "true")
                        {
                            echo "<option value='true' selected='selected'>Yes</option>";
                        }
                        else
                        {
                            echo "<option value='true'>Yes</option>";
                        }
                        
                        if($submitArticle == "false")
                        {
                            echo "<option value='false' selected='selected'>No</option>";   
                        }
                        else
                        {
                            echo "<option value='false'>No</option>";
                        }
                    ?>
                    </select>
                </p>
                <!-- site online -->
                <p id='box'>
                    <b>Site Online</b><br />
                    <select id='textbox' name='siteOnline'>
                    <?php
                        // check the current status of the site
                        if($siteOnline[2] == "true")
                        {
                            echo "<option value='true' selected='selected'>Yes</option>";
                        }
                        else
                        {
                            echo "<option value='true'>Yes</option>";
                        }
                        
                        if($siteOnline[2] == "false")
                        {
                            echo "<option value='false' selected='selected'>No</option>";
                        }
                        else
                        {
                            echo "<option value='false'>No</option>";
                        }
                    ?>
                    </select><br />
                    <b>Title</b><br />
                    <input type='text' id='textbox' name='title' value='<?php echo $siteOnline[0] ?>'><br />
                    <b>Content</b><br />
                    <textarea id='textbox' name='content' style='font-size:11px'><?php echo $siteOnline[1] ?></textarea>
                </p>
                <p id='box'>
                    <b>Article submit limit</b><br />
                    <input type='text' id='textbox' name='submitLimit' value='<?php echo $submitLimit ?>'>
                </p>
                <p id='box'>
                    <b>Allow Administrator Remote Login</b><br />
                    <select id='textbox' name='adminRemoteLogin'>
                    <?php
                        // set the selected settings
                        if($adminRemoteLogin == "true")
                        {
                            echo "<option value='true' selected='selected'>Yes</option>";
                        }
                        else
                        {
                            echo "<option value='true'>Yes</option>";
                        }
                        
                        if($adminRemoteLogin == "false")
                        {
                            echo "<option value='false' selected='selected'>No</option>";
                        }
                        else
                        {
                            echo "<option value='false'>No</option>";
                        }
                    ?>
                    </select>
                </p>
                </p>
                <p align='center'>
                    <input type='submit' id='submit2' value='Save' name='save'>
                </p>
                <!-- back up -->
                <p id='box'>
                    Last Backup Date: <?php echo $lastBackUpDate; ?><br />
                    <b>Backup Type</b><br />
                    <select id='textbox' name='backupType'>
                        <option value='ZIP'>Zip (Windows)</option>
                        <option value='TAR'>Tar (Unix/Linux)</option>
                    </select>
                </p>
                <p align='right'>
                    <input type='submit' id='submit2' value='Backup website' name='backup'>
                </p>
                <!-- Student Update -->
                <p id='box'>
                    <b>Student Update</b><br />
                    <input type='file' name='studentFile'><br />
                    Browse the student blah blah blah
                </p>
                <p align='right'>
                    <input type='submit' id='submit2' name='updateStudentInformation' value='Update Student'>
                </p>
            </form>
        </div>
    </div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>