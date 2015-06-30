<?php
	/**
	 * Filename	: LoginForm.php
	 * Description	: contains functions for validating login credentials
	 * Date Created	: November 30, 2007
	 * Author	: Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	boolean loginAccount($username, $password)
	 *	array getErrors()
	 *	string validateLogin($username, $password)
	 */
	
	class LoginForm
	{
		var $accountId;
		var $errors;
	
		/**
		 * Login Account method: logs in the account to their respective pages depending on what position they are in
		 * Paramaters: $username, $password
		 * Return Type: boolean
		 */
		function loginAccount($username, $password)
		{
            // escape the characters that needs to be escaped to avoid sql injection
            $username = mysql_escape_string($username);
            
			// validate the username and password
			$loginError = $this -> validateLogin($username, $password);
			
			// check for errors
			if($loginError == null)
			{	
				// if there are no errors committed, update the accounts last login date so as to monitor the logins
				mysql_query("UPDATE argus_accounts SET last_login_date = '".time()."' WHERE username = '".$username."'") or die(mysql_error());
				
				// create a session cookie for the account. The session cookie contains the account id of the user which
				// will be used for accessing restricted pages depending on the position of the account
				setcookie("argus", $this -> accountId, time()+3600);
				
				return true;
			}
			else
			{
				// set the errors that was committed
				$this -> errors = array("login" => $loginError);
				
				return false;
			}
			
			return;
		}
		
		/**
		 * Get Errors method: returns the errors that was committed durint the login process
		 * Return Type: array
		 */
		function getErrors()
		{
			return $this -> errors;
		}
		
		/**
		 * Validate Login method: validates if the username and password is correct
		 * Parameters: $username, $password
		 * Return Type: string
		 */
		function validateLogin($username, $password)
		{
			// get the information from the database for the given username
			$accountQuery = mysql_query("SELECT account_id, password, status, position FROM argus_accounts WHERE username = '".$username."'") or die(mysql_error());
			
			// check the information
			if(mysql_num_rows($accountQuery) == 0)
			{
				// return a message that the account does not exist if no information was queried from the database
				return "The account username you have provided does not exist";
			}
			else
			{
				// check the status of the user if it is ENABLED. only ENABLED accounts are allowed to login
				$status = mysql_result($accountQuery,0,"status");
				
				if($status == "DISABLED")
				{
					// return a message that the account is disabled and not allowed to login
					return "The account is currently DISABLED";
				}
				else
				{
                    // check the position of the user
                    
                    $position = mysql_result($accountQuery,0,"position");
                    
                    if($position == "MEMBER")
                    {
                        // check if  members are allowed to login
                        $memberLoginQuery = mysql_query("SELECT content FROM argus_infos WHERE name='member_login'") or die(mysql_error());
                        $memberLogin = mysql_result($memberLoginQuery,0,"content");
                        
                        if($memberLogin == "false")
                        {
                            // return a message that members are not allowed to login
                            return "Members are currently not allowed to login";
                        }
                    }
                    else if($position == "CONTRIBUTOR")
                    {
                        // check if contributors are allowed to login
                        $contributorLoginQuery = mysql_query("SELECT content FROM argus_infos WHERE name='contributor_login'") or die(mysql_error());
                        $contributorLogin = mysql_result($contributorLoginQuery,0,"content");
                        
                        if($contributorLogin == "false")
                        {
                            // return a message that contributors are not allowed to login
                            return "Contributors are currently not allowed to login";
                        }
                    }
                    else if($position == "ADMINISTRATOR" && $_SERVER["REMOTE_ADDR"] != "127.0.0.1")
                    {
                        // check if the administrator remote login is disabled or not
                        $adminRemoteLoginQuery = mysql_query("SELECT content FROM argus_infos WHERE name='admin_remote_login'") or die(mysql_error());
                        $adminRemoteLogin = mysql_result($adminRemoteLoginQuery,0,"content");
                        
                        if($adminRemoteLogin == "false")
                        {
                            // return a message that admin remote login is disabled
                            return "Administrator remote login is currently disabled";
                        }
                    }
                    
					// check if the entered password matches with the password that is stored from the database
					$queriedPassword = mysql_result($accountQuery,0,"password");
					
					if($password != $queriedPassword)
					{
						// return a message that the password is invalid
						return "Invalid Password";
					}
					else
					{
						// set the global variable account id which will be used for creating a cookie session
						$accountId = mysql_result($accountQuery,0,"account_id");
						$this -> accountId = $accountId;
					}
				}
			}
			
			return;
		}
	}
?>