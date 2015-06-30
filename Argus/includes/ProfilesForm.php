<?php
	/**
	 * Filename : ProfilesForm.php
	 * Description : Contains functions for editing profile
	 * Date Created : December 27,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
     *  void ProfilesForm($accountId)
     *  string displayBanner()
     *  boolean updateProfile($username, $password, $retypedPassword, $email)
     *  string validateUsername($username)
     *  string validatePassword($password)
     *  string validateEmail($email)
     *  string getErrors()
     *  string displayAccountInformation()
     *  string validatePhoto($photo)
     *  boolean uploadPhoto($photoName, $photoTmpName, $accountId)
	 */
	
	class ProfilesForm
	{
        var $accountId;
        var $errors;
        
        /**
         * Constructor method
         * Parameter: $accountId
         */
        function ProfilesForm($accountId)
        {
            // set the attributes which will be used further
            $this -> accountId = $accountId;
            
            return;
        }
        
        /**
         * Display Banner Method: displays the menus and options of profiles
         * Return Type: String
         */
        function displayBanner()
        {
            echo "
            <div class='bg2'>
            <h2><em>Profile</em></h2>
            <p align='center'>";
            
            // menus
            // display only the account information for contributors and administrators
            // query the position of the account from the database
            $positionQuery = mysql_query("SELECT position FROM argus_accounts WHERE account_id = '".$this -> accountId."'") or die(mysql_error());
            $position = mysql_result($positionQuery,0,"position");
            
            // check the position
            if($position != "MEMBER")
            {
                echo "<a href='profiles.php?event=info'>Account Information</a> . ";
            }
            
            echo "<a href='profilesedit.php'>Edit</a>";
            echo "
            </p>
            </div>";
            
            return;
        }
        
        /**
         * Update Profile method: updates the profile of the user
         * Parameter: $username, $password, $retypedPassword, $email
         * Return Type: Boolean
         */
        function updateProfile($username, $password, $retypedPassword, $email)
        {
            // escape the characters that needs escaping to avoid sql injection
            $username = mysql_escape_string($username);
            $password = mysql_escape_string($password);
            $retypedPassword = mysql_escape_string($retypedPassword);
            $email = mysql_escape_string($email);
            
            // validate the username
            $usernameError = $this -> validateUsername($username);
            
            // validate the password and the retyped password
            $passwordError = $this -> validatePassword($password, $retypedPassword);
            
            // validate the email
            $emailError = $this -> validateEmail($email);
            
            // check the results
            if($usernameError == null && $passwordError == null && $emailError == null && $photoError == null)
            {
                // update the database
                mysql_query("UPDATE argus_accounts SET username='".$username."', password='".$password."', email='".$email."' WHERE account_id = '".$this -> accountId."'") or die(mysql_error());
                
                // return successful update of profile
                return true;
            }
            else
            {
                // set the errors
                $this -> errors = array("username" => $usernameError, "password" => $passwordError, "email" => $emailError);
                
                // return unsuccessful update of profile
                return false;
            }
            
            return;
        }
        
        /**
         * Get Errors method: returns the errors that was committed during the update of account profile
         * Return Type: string
         */
        function getErrors()
        {
            // return the errors
            return $this -> errors;
        }
        
        /**
         * Validate username Method: validates the username if it is available or not
         * Parameter: $username
         * Return Type: string
         */
        function validateUsername($username)
        {
            // include the username validator class and validate the username minimum of 5 characters and maximum of 15 characters
            include("class_libraries/UsernameValidator.php");
            $usernameValidator = new UsernameValidator(5,15);
            
            // validate the username
            $result = $usernameValidator -> validateUsername($username);
            
            // check the result
            if($result == false)
            {
                // if failed, get the errors that was committed which will be returned
                return $usernameValidator -> getErrors();
            }
            else
            {
                // do extra validation.. check if the username is unique or not
                $usernameQuery = mysql_query("SELECT username FROM argus_accounts WHERE username='".$username."' AND account_id != '".$this -> accountId."'") or die(mysql_error());
            
                // check the queried result
                if(mysql_num_rows($usernameQuery) > 0)
                {
                    // return a message that the username has already been taked
                    return "The username '".$username."' has already been registered to someone else";
                }
            }
            
            return;
        }
        
        /**
         * Validate password method: validates the password of the user
         * Parameter: $password, $retypedPassword
         * Return Type: string
         */
        function validatePassword($password, $retypedPassword)
        {
            // include the password validator class and validate the password minimum of 5 characters and max of 15
            include("class_libraries/PasswordValidator.php");
            $passwordValidator = new PasswordValidator(5,15);
            
            // validate the password
            $result = $passwordValidator -> validatePassword($password, $retypedPassword);
            
            // check the result
            if($result == false)
            {
                // get the errors that was committed during validation and return it to the user
                return $passwordValidator -> getErrors();
            }
            
            return;
        }
        
        /**
         * Validate Email method: validates the email of the user if it is available or not
         * Parameter: $email
         * $return type: string
         */
        function validateEmail($email)
        {
            // include the email validator class and validate the email
            include("class_libraries/EmailValidator.php");
            $emailValidator = new EmailValidator();
            
            // validate the email
            $result = $emailValidator -> validateEmail($email);
            
            // check the result
            if($result == false)
            {
                // get the errors that was committed during the validation and return it to the user
                return $emailValidator -> getErrors();
            }
            else
            {
                // query an email address from the database if it has the same email
                $emailQuery = mysql_query("SELECT email FROM argus_accounts WHERE email='".$email."' AND account_id != '".$this -> accountId."'") or die(mysql_error());
                
                // check the queried result
                if(mysql_num_rows($emailQuery) > 0)
                {
                    // return a message to the user that the email address has been registered to someone else
                    return "The email address '".$email."' has already been registered to someone else";
                }
            }
            
            return;
        }
    
        /**
         * Display Account Information method: displays the information of an account
         * Return Type: string
         */
        function displayAccountInformation()
        {
            // query the account information of the user
            $accountQuery = mysql_query("SELECT id_number, username, name, position, email, last_login_date, date_registered FROM argus_accounts WHERE account_id = '".$this -> accountId."'") or die(mysql_error());
            
            // set the attributes
            $idNumber = mysql_result($accountQuery,0,"id_number");
            $username = mysql_result($accountQuery,0,"username");
            $position = mysql_result($accountQuery,0,"position");
            $email = mysql_result($accountQuery,0,"email");
            $lastLoginDate = date("F d, Y", mysql_result($accountQuery,0,"last_login_date"));
            $dateRegistered = date("F d, Y", mysql_result($accountQuery,0,"date_registered"));
            
            // display the attributes
            echo "<h3>Account Information</h3>";
            echo "<div class='bg1'>";
            echo "<p>Account Information</p>";
            echo "<p id='box'>";
            echo "ID Number : ".$idNumber."<br>";
            echo "Username : ".$username."<br>";
            echo "Position : ".$position."<br>";
            echo "Email : ".$email."<br>";
            echo "Last login : ".$lastLoginDate."<br>";
            echo "Date Registered : ".$dateRegistered."<br>";
            echo "</p>";
            
            // display additional information for administrator and contributor accounts
            if($position == "ADMINISTRATOR" || $position == "CONTRIBUTOR")
            {
                // include the account information class which will display the addition information
                include("class_libraries/AccountInformation.php");
                $accountInformation = new AccountInformation($this -> accountId);
                
                // display the mail information
                $accountInformation -> displayMailInformation();
                
                // display the article information
                $accountInformation -> displayArticleInformation();
                
                // display the image information
                $accountInformation -> displayImageInformation();
            }
            
            echo "</div>";
            
            return;
        }
    
        /**
         * Validate Photo Method: validates the photo if the file is really an image
         * Parameter: $photo
         * Return type: string
         */
        function validatePhoto($photoName)
        {
            // include the image validate class and validate the image
            include("class_libraries/ImageNameValidator.php");
            $imageValidator = new ImageNameValidator();
            
            // validate the image
            $result = $imageValidator -> validateImage($photoName);
            
            // check the result
            if($result == false)
            {
                // if failed, get the error that was committed then return the error
                return $imageValidator -> getErrors();
            }
            else
            
            return;
        }
        
        /**
         * Upload Photo method: uploads a photo
         * Parameter: $photoName, $photoTmpName, $accountId
         * Return Type: boolean
         */
        function uploadPhoto($photoName, $photoTmpName, $accountId)
        {            
            // validate the photo name
            $photoError = $this -> validatePhoto($photoName);
            
            // check the results
            if($photoError == null)
            {
                // if no error encountered, upload the photo to the server renaming the image to there account id
                 // get the extension of the the image
                $extension = end(explode(".", $photoName));
                
                // set the path where to store the images then attach the image name with the extension
                $path = "../images/accounts/".$accountId.".".$extension;
                
                // upload the image to the server renaming the PHYSICAL image name to it's SAVED IMAGE ID
                move_uploaded_file($photoTmpName, $path);
                
                // after uploading the picture, resize the picture
                // include the photo resizer class to resize the photos
                include("class_libraries/ImageResizer.php");
                $imageResizer = new ImageResizer($path);
                
                // resize the image in a 300 by 300 pixel
                $imageResizer -> resizeImage(300,300);
                
                // after uploading and resizing the photo, update the database
                mysql_query("UPDATE argus_accounts SET photo_path = '".$path."' WHERE account_id = '".$accountId."'") or die(mysql_error());
                
                // return successful upload
                return true;
            }
            else
            {
                // set the error
                $this -> errors = array("photo" => $photoError);
                
                // return unsuccessful upload
                return false;
            }
            
            return;
        }
	}
?>