<?php
	/**
	 * Filename: accountconfiguration.php
	 * Description: page for configuring the account/admin
	 * Date Created: January 17,2007
	 * Author: Argus Team
	 */
     
    // when loaded, check if the database connector has already been set
    if(file_exists("../includes/class_libraries/DatabaseConnector.php"))
    {
        // if the file exist, create a connection to the database
        include("../includes/class_libraries/DatabaseConnector.php");
        $databaseConnector = new DatabaseConnector();
        
        // set the default variables
        $publicationName = "Argus";
        $subtitle = "College of Information and Computing Science";
    }
    else
    {
        // if the file does not exist, redirect the user back to the index page to create the tables and script for the database
        header("Location: index.php");
    }
    
	/**
	 * Validate Publication Name method: validates the name of the publication if it has the correct length
	 * Parameter: $publicationName
	 * Return type: string
	 */
    function validatePublicationName($publicationName)
    {
        // include the title validator class to validate the name minimum of 5 characters and mx of 15
        include("../includes/class_libraries/TitleValidator.php");
        $titleValidator = new TitleValidator(5,15);
        
        // validate the publication name
        $result = $titleValidator -> validateTitle($publicationName);
        
        // check the result
        if($result == false)
        {
            // if false, get the error committed and return it
            return $titleValidator -> getErrors();
        }
        
        return;
    }
    
	/**
	 * Validate Username Method: validates the username for correct syntax and length
	 * Parameter: $username
	 * Return Type: string
	 */
    function validateUsername($username)
    {
        // include the username validator class to validate the username minimum of 5 and max of 15 characters
        include("../includes/class_libraries/UsernameValidator.php");
        $usernameValidator = new UsernameValidator(5,15);
        
        // validate the username
        $result = $usernameValidator -> validateUsername($username);
        
        // check the result of there is an error committed
        if($result == false)
        {
            // get the errors committed and return the error
            return $usernameValidator -> getErrors();
        }
        
        return;
    }
    
	/**
	 * Validate Id Number Method: validates the id number of the student if it exists in the database
	 * Parameter: $idNumber, $name
	 * Return Type: string
	 */
    function validateIdNumber($idNumber, $name)
    {
        //include the id number validator class to validate the id number and the name of the student if it matches
        include("../includes/class_libraries/IdNumberValidator.php");
        $idNumberValidator = new IdNumberValidator();
        
        // validate the id number
        $result = $idNumberValidator -> validateIdNumber($idNumber, $name);
        
        // check the result if there is an error committed
        if($result == false)
        {
            // get the error and and return the error
            return $idNumberValidator -> getErrors();
        }
        
        return;
    }
    
	/**
	 * Validate Password Method: validates the password and retyped password
	 * Parameter: $password, $retypedPassword
	 * Return Type: string
	 */
    function validatePassword($password, $retypedPassword)
    {
        // include the password validator class and validate the password and retyped password minimum of 5 and max of 15 characters
        include("../includes/class_libraries/PasswordValidator.php");
        $passwordValidator = new PasswordValidator(5,15);
        
        // validate the password
        $result = $passwordValidator -> validatePassword($password, $retypedPassword);
        
        // check the result
        if($result == false)
        {
            // get the error and return it
            return $passwordValidator -> getErrors();
        }
        
        return;
    }
    
	/**
	 * Validate Email Method: validates the email of the user for correct syntax
	 * Parameter: $email
	 * Return Type: string
	 */
    function validateEmail($email)
    {
        // include the email validator class for correct email syntax
        include("../includes/class_libraries/EmailValidator.php");
        $emailValidator = new EmailValidator();
        
        // validate the email
        $result = $emailValidator -> validateEmail($email);
        
        // check the result
        if($result == false)
        {
            // get the error and return the error that was committed
            return $emailValidator -> getErrors();
        }
        
        return;
    }
     
	/**
	 * BUTTON EVENTS
	 *  create
	 */
     
        // CREATE button
        if(isset($_POST["create"]))
        {
            // get the inputs from the user
            $publicationName = $_POST["publicationName"];
            $subtitle = $_POST["subtitle"];
            $idNumber = $_POST["idNumber"];
            $username = $_POST["username"];
            $firstName = $_POST["firstName"];
            $lastName = $_POST["lastName"];
            $middleInitial = $_POST["middleInitial"];
            $password = $_POST["password"];
            $retypedPassword = $_POST["retypedPassword"];
            $email = $_POST["email"];
            
            // arrange the name in First Last MI
            $name = $firstName." ".$lastName." ".$middleInitial;
            
            // validate the publication  name
            $publicationNameError = validatePublicationName($publicationName);
            
            // check if the id number is blank or not..
            // if the id number is empty then that means that the administrator is not a student
            // if the administrator is not a student, then there's no need to validate the name and id number of the student
            $idNumber = trim($idNumber);
            
            if(!empty($idNumber))
            {
                // validate the id number
                $idNumberError = validateIdNumber($idNumber, $name);
            }
            
            // validate the username
            $usernameError = validateUsername($username);
            
            // validate the password
            $passwordError = validatePassword($password, $retypedPassword);
            
            // validate the email
            $emailError = validateEmail($email);
            
            // check if all validation has passed
            if($publicationNameError == null && $idNumberError == null && $usernameError == null && $passwordError == null && $emailError == null)
            {
                // set the time which will be used
                $time = time();
                
                // check if the id number is empty or not
                if(!empty($idNumber))
                {
                    // update the table of students turning the student as a REGISTERED user
                    mysql_query("UPDATE argus_slu_students SET status = 'REGISTERED' WHERE id_number = '".$idNumber."'") or die(mysql_error());
                }
                
                // create the title and subtitle for the website
                // arrange the publication name
                $publicationName = $publicationName.";".$subtitle;
                
                mysql_query("UPDATE argus_infos SET content='".$publicationName."' WHERE name='publication_name'") or die(mysql_error());
                
                // create the account of the administrator
                mysql_query("INSERT INTO argus_accounts(id_number,username,password,name,position,email,date_registered,status)
                            VALUES('".$idNumber."','".$username."','".$password."','".$name."','ADMINISTRATOR','".$email."','".$time."','ENABLED')") or die(mysql_error());
                
                // after the administrator is created, redirect the user to the index page
                header("Location: ../index.php");
            }
            else
            {
                // arrange all the errors which will be displayed below
                $errors = array("publicationName" => $publicationNameError, "idNumber" => $idNumberError ,"username" => $usernameError, "password" => $passwordError, "email" => $emailError);
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
                <p>
					Welcome to Argus Online Publication initial setup. This step by step setup provides the tools needed for the publication to function. The Editor-in-Chief must provide the necessary information below to complete the setup(with or without the assistance of the Technical Support). 
                </p>
            </div>
            
            <h3>Account Configuration</h3>
            <div class='bg1'>
                <form method='post' action='<?php echo $_SERVER["PHP_SELF"] ?>'>
                    <?php
                        // display errors here that was committed during the creation of account
                        if(isset($_POST["create"]) && $errors != null)
                        {
                            echo "<p><font color='red'>";
                            
                            // error for publication name
                            if($errors["publicationName"] != null)
                            {
                                echo $errors["publicationName"]."<br />";
                            }
                            
                            // error for slu id
                            if($errors["idNumber"] != null)
                            {
                                echo $errors["idNumber"]."<br />";
                            }
                            
                            // error for username
                            if($errors["username"] != null)
                            {
                                echo $errors["username"]."<br />";
                            }
                            
                            // error for password
                            if($errors["password"] != null)
                            {
                                echo $errors["password"]."<br />";
                            }
                            
                            // error for email
                            if($errors["email"] != null)
                            {
                                echo $errors["email"]."<br />";
                            }
                            
                            echo "</font></p>";
                        }
                    ?>
                    <p>
                        Account Information
                    </p>
                    <!-- publication name -->
                    <p id='box'>
                        <b>Publication Name</b><br />
                        <input type='text' id='textbox' name='publicationName' value='<?php echo $publicationName ?>'><br />
                        <i>Publication Title should be 5 - 15 characters long.</i><br />
                        <b>Publication Subtitle</b><br />
                        <input type='text' id='textbox' name='subtitle' value='<?php echo $subtitle ?>'>
                    </p>
                    <!-- Id Number -->
                    <p id='box'>
                        <b>Saint Louis University Id Number</b><br />
                        <input type='text' id='textbox' name='idNumber' value='<?php echo $idNumber ?>'><br />
                        <i>Leave blank if none</i>
                    </p>
                    <!-- Username -->
                    <p id='box'>
                        <b>Username</b><br />
                        <input type='text' id='textbox' name='username' value='<?php echo $username ?>'>
                    </p>
                    <!-- name -->
                    <p id='box'>
                        <b>First Name</b><br />
                        <input type='text' id='textbox' name='firstName' value='<?php echo $firstName ?>'><br />
                        <b>Middle Initial</b><br />
                        <input type='text' id='textbox' name='middleInitial' maxlength='1' value='<?php echo $middleInitial ?>'><br />
                        <b>Last Name</b><br />
                        <input type='text' id='textbox' name='lastName' value='<?php echo $lastName ?>'><br />
                    </p>
                    <!-- password -->
                    <p id='box'>
                        <b>Password</b><br />
                        <input type='password' id='textbox' name='password' value='<?php echo $password ?>'><br />
                        <i>Password should be 5 - 15 characters long.</i><br />
                        <b>Retype Password</b><br />
                        <input type='password' id='textbox' name='retypedPassword' value='<?php echo $retypedPassword ?>'>
                    </p>
                    <!-- current email -->
                    <p id='box'>
                        <b>Current Email</b><br />
                        <input type='text' id='textbox' name='email' value='<?php echo $email ?>'><br />
                        <i>Please ensure that the spam filter of this email address will allow email from www.argus.com.</i>
                    </p>
                    <p align='center'>
                        <input type='submit' id='submit2' value='create' name='create'>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <div id='footer'>
        <p>powered by argus</p>
    </div>
</html>";
