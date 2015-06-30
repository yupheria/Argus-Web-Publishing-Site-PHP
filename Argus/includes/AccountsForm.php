<?php
	/**
	 * Filename : AccountsForm.php
	 * Description : contains functions and page properties of managing accounts
	 * Date Created : November 28,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	string displayBanner()
	 *	string displayAccounts($position, $status, $page)
     *  string displayAccountArticles($accountId, $status)
	 *	string displayAccountStatistics($accountId)
	 *	void deleteMails($accountId, $status)
     *  void deleteImages($accountId, $status)
	 *	void disableAccount($accountId)
	 *	void enableAccount($accountId)
	 *	boolean addAccount($idNumber, $username, $name, $password, $retypedPassword, $email, $position, $status)
	 *	boolean updateAccount($accountId, $username, $password, $retypedPassword, $email, $status)
	 *	string getErrors()
	 *	string validateEmail($accountId, $email)
	 *	string validatePassword($password, $retypedPassword)
	 *	string validateUsername($accountId, $username)
	 *	string validateIdNumber($idNumber, $name)
     *  string getCategoryName($categoryId)
     *  void refreshArticleSubmit($accountId, $article)
     *  void deleteTrashArticle($accountId)
     *  void removeArticle($accountId, $article)
     *  void restoreArticle($accountId, $article)
     *  void deleteArticle($accountId, $article)
     *  string searchAccount($accountKeyword, $accountSearchType)
     *  string getAccountInfo($idNumber);
	 */
	 
	class AccountsForm
	{
		var $errors;
		
		/**
		 * Display Banner method: displays the menu and options for mananging accounts
		 */
		function displayBanner()
		{
			echo "
            <div class='bg2'>
			<h2><em>Accounts Manager</em></h2>
			<p align='center'>";
			
			// menus
			echo "
            <a href='accounts.php'>Members</a> . 
			<a href='accounts.php?event=contributor'>Contributors</a> . 
			<a href='accounts.php?event=disabled'>Disabled</a> . 
			<a href='accountscompose.php'>Create</a> . 
            <a href='search.php?event=accounts'>Search</a>";
			
			echo "
            </p>
			</div>";
			
			return;
		}
		
		/**
		 * Display Accounts method: displays all account
		 * Parameter: $position, $status, $page
		 * Return Type: string
		 */
		function displayAccounts($position, $status, $page)
		{
			if($status == "DISABLED")
			{
                // query the total number of ammount of disabled contributors and members
                $accountsCountQuery = mysql_query("SELECT account_id FROM argus_accounts WHERE status = '".$status."'") or die(mysql_error());
			}
			else
			{
                // query the total number of ammount of disabled contributors and members
                $accountsCountQuery = mysql_query("SELECT account_id FROM argus_accounts WHERE status = '".$status."' AND position = '".$position."'") or die(mysql_error());
			}
            
            // get the count
            $accountsCount = mysql_num_rows($accountsCountQuery);
            
            // set the number of accounts to be showed per page
            $limit = 15;
            
            // compute the number of page for the accounts
            $numberOfPages = ceil($accountsCount/$limit);
            
            // check the status of the page
            if(empty($page) && !ctype_digit($page))
            {
                // set the default page which is 1
                $page = 1;
            }
			
            // compute the limit value
            $limitValue = $page * $limit - ($limit);
            
            if($status == "DISABLED")
            {
                // query all DISABLED CONTRIBUTORS and MEMBERS and display the appropriate form title
                $accountsQuery = mysql_query("SELECT account_id, id_number, username, name, position, email, last_login_date FROM argus_accounts WHERE status = '".$status."' ORDER BY last_login_date DESC LIMIT ".$limitValue.",".$limit."") or die(mysql_error());
                
                // title for disabled accounts
                echo "<h3>Disabled</h3>";
            }
            else
            {
                // query all ENABLED accounts depending on the position being asked and display the appropriate form title
                $accountsQuery = mysql_query("SELECT account_id, id_number, username, name, email, last_login_date FROM argus_accounts WHERE status = '".$status."' AND position = '".$position."' ORDER BY last_login_date DESC LIMIT ".$limitValue.",".$limit."") or die(mysql_error());
                
                // title for MEMBERS and CONTRIBUTORS that are enabled
                echo "<h3>".ucfirst(strtolower($position))."s</h3>";
            }
            
			echo "<div class='bg1' id='tablePanel'>";
			
			// check if there is a result from the database
			if(mysql_num_rows($accountsQuery) == 0)
			{
				echo "<p><h3 align='center'>";
				
				// message for DISABLED accounts
				if($status == "DISABLED")
				{
					// print a message that there are no results on the request of the user
					echo "There are no ".$status." accounts";
				}
				// message for ENABLED accounts
				else
				{
					echo "There are no ".$position." accounts";
				}
				
				echo "</h3></p>";
			}
			else
			{
                // include the TOOL TIP ajax and create a tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                // include the checkbox funtions where check box are allowed to be selected/unselected all
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
				if($status == "ENABLED")
				{
					// set the form for ENABLED accounts
					echo "<form id='form_id' method='post' action='".$_SERVER['PHP_SELF']."?event=".strtolower($position)."'>";
				}
				else
				{
					// set the form for DISABLED accounts
					echo "<form id='form_id' method='post' action='".$_SERVER['PHP_SELF']."?event=".strtolower($status)."'>";
				}
				
                // if there is a result, create a table where to display all queried accounts
				echo "
                <table width='100%'>
				<tr>
				<th class='fix'><input type='checkbox' onClick='toggleCheckBoxes(\"accountIds\")'></th>
				<th>ID Number</th>
				<th>Username</th>
				<th>Name</th>
				<th>Email</th>";
				
				// display only the POSITION attribute if being accessed are DISABLED accounts
				if($status == "DISABLED")
				{
					echo "<th>Position</th>";
				}
				
				echo "
                <th>Last Login Date</th>
				<th class='action'>Action</th>
				</tr>";
				
				// display all accounts
				$color = true;
				
				for($i=0; $i<mysql_num_rows($accountsQuery); $i++)
				{
					// display the row in an alternate color manner
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
					
					// set the queried values for ENABLED status
					$accountId = mysql_result($accountsQuery,$i,"account_id");
					$idNumber = mysql_result($accountsQuery,$i,"id_number");
					$username = mysql_result($accountsQuery,$i,"username");
					$name = mysql_result($accountsQuery,$i,"name");
					$email = mysql_result($accountsQuery,$i,"email");
					$lastLoginDate = date("m/d/y", mysql_result($accountsQuery,$i,"last_login_date"));
					
					echo "
                    <td><input type='checkbox' name='accountIds[]' value='".$accountId."'></td>
					<td>".$idNumber."</td>
					<td><a href='accounts.php?event=statistics&account=".$accountId."'>".$username."</a></td>
					<td>".$name."</td>
					<td><a href='mailto:".$email."'>".$email."</a></td>";
					
					// display the POSITION attribute if status being accessed are DISABLED accounts
					if($status == "DISABLED")
					{
						$position = mysql_result($accountsQuery,$i,"position");
						echo "<td>".$position."</td>";
					}
					
					echo "
                    <td>".$lastLoginDate."</td>
					<td>
                    <a href='accountscompose.php?event=edit&account=".$accountId."' title='Edit'><img src='../miscs/images/Default/user_edit.png' alt='edit'></a> ";
					
					// display the ACTIONS for ENABLED accounts
					if($status == "ENABLED")
					{
						echo "
						<a href='accounts.php?event=&action=disable&account=".$accountId."' title='Disable'><img src='../miscs/images/Default/user_lock.png' alt='disable'></a>";
					}
					// display the ACTIONS for DISABLED accounts
					else
					{
						echo "
						<a href='accounts.php?event=".strtolower($status)."&action=enable&account=".$accountId."' title='Enable'><img src='../miscs/images/Default/user_restore.png' alt='enable'></a> 
						<a href='accounts.php?event=".strtolower($status)."&action=delete&account=".$accountId."' title='Delete'><img src='../miscs/images/Default/user_delete.png' alt='delete'></a>";
					}
					
					echo "
                    </td>
					</tr>";
				}
				
				echo "</table>";
				
				// display the batch processing buttons
				echo "
                <table width='100%'>
				<tr><td>";
				
				// display BUTTONS for  ENABLED accounts
				if($status == "ENABLED")
				{
					echo "<input type='submit' id='submit1' value='Disable' name='disable'> ";
				}
				// display BUTTONS for DISABLED accounts
				else
				{
					echo "
                    <input type='submit' id='submit1' value='Enable' name='enable'> 
					<input type='submit' id='submit1' value='Delete' name='delete'>";
				}
				
				echo "
                </td>
                <td align='right'>";
                
                // display the previous page link
                if($page > 1)
                {
                    echo "<a href='accounts.php?event=".$_GET["event"]."&page=".($page - 1)."'><img src='../miscs/images/Default/previous.png'></a> ";
                }
                
                // display the next page link
                if($page < $numberOfPages)
                {
                    echo "<a href='accounts.php?event=".$_GET["event"]."&page=".($page + 1)."'><img src='../miscs/images/Default/next.png'></a>";
                }
                
                echo "
                </td>
                </tr>
				</table>";
			}
			
			echo "</div>";
			
			return;
		}
        
        /**
         * Display Account Articles: displays the articles of an account
         * Parameter: $accountId, $status
         * Return Type: string
         */
        function displayAccountArticles($accountId, $status)
        {
            // query the account name from the database
            $accountQuery = mysql_query("SELECT name FROM argus_accounts WHERE account_id = '".$accountId."'") or die(mysql_error());
            
            // check if the account exists
            if(mysql_num_rows($accountQuery) > 0)
            {
                // set the name attribute of the account
                $name = mysql_result($accountQuery,0,"name");
                
                // validate the status cause it's not safe cause it came from a URL
                // there are only two possible values of saved articles, saved or deleted
                if($status != "saved" && $status != "deleted")
                {
                    $status = "saved";
                }
                
                // query the articles of the user depending on the provided status
                $articlesQuery = mysql_query("SELECT saved_article_id, title, category_id, date_created, date_modified, times_submitted FROM argus_saved_articles WHERE status='".strtoupper($status)."' AND account_id = '".$accountId."'") or die(mysql_error());
                
                // set the title of the page
                echo "<h3><a href='accounts.php?event=contributor'>Contributors</a> &raquo; <a href='accounts.php?event=statistics&account=".$accountId."'>".$name."</a> &raquo; ".ucfirst($status)." Articles</h3>";
                echo "<div class='bg1' id='tablePanel'>";
                
                // check if there are articles for the account
                if(mysql_num_rows($articlesQuery) == 0)
                {
                    // notify the user that there are no articles
                    echo "<p><h3 align='center'>Tnere are no ".strtoupper($status)." Articles</h3></p>";
                }
                else
                {
                    // inlcude the TOOL TIP ajax and create a tool tip
                    include("ajax_libraries/ToolTip.php");
                    $toolTip = new ToolTip();
                    $toolTip -> setupForm();
                    
                    // include the checkbox funtions where check box are allowed to be selected/unselected all
                    echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                    
                    // display the articles in a table
                    echo "<form method='post' id='form_id' action='".$_SERVER["POST"]."?event=viewarticles&account=".$accountId."&status=".$status."'>";
                    echo "<table width='100%'>";
                    echo "<tr>";
                    echo "<th><input type='checkbox' onClick='toggleCheckBoxes(\"articleIds\")'></th>";
                    echo "<th>Title</th>";
                    echo "<th>Category</th>";
                    echo "<th>Date Created</th>";
                    echo "<th>Date Modified</th>";
                    echo "<th>Submits</th>";
                    echo "<th>Action</th>";
                    echo "</tr>";
                    
                    $color = true;
                    
                    for($i=0; $i<mysql_num_rows($articlesQuery); $i++)
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
                        $articleId = mysql_result($articlesQuery,$i,"saved_article_id");
                        $title = $this -> limitTitle(stripslashes(mysql_result($articlesQuery,$i,"title")));
                        $category = $this -> getCategoryName(mysql_result($articlesQuery,$i,"category_id"));
                        $dateCreated = date("m/d/y", mysql_result($articlesQuery,$i,"date_created"));
                        $dateModified = date("m/d/y", mysql_result($articlesQuery,$i,"date_modified"));
                        $timesSubmitted = mysql_result($articlesQuery,$i,"times_submitted");
                        
                        // display the attributes
                        echo "<td><input type='checkbox' name='articleIds[]' value='".$articleId."'></td>";
                        echo "<td><a href='#'>".$title."</a></td>";
                        echo "<td>".$category."</td>";
                        echo "<td>".$dateCreated."</td>";
                        echo "<td>".$dateModified."</td>";
                        echo "<td>".$timesSubmitted."</td>";
                        echo "<td>";
                        
                        // set the actions
                        if($status == "saved")
                        {
                            // actions for saved articles
                            echo "<a href='accounts.php?event=".$_GET["event"]."&account=".$_GET["account"]."&status=".$_GET["status"]."&action=remove&article=".$articleId."' title='Remove'><img src='../miscs/images/Default/article_trash.png'></a> ";
                        }
                        else
                        {
                            // actions for deleted articles
                            echo "
                            <a href='accounts.php?event=".$_GET["event"]."&account=".$_GET["account"]."&status=".$_GET["status"]."&action=restore&article=".$articleId."' title='Restore'><img src='../miscs/images/Default/article_restore.png'></a> 
                            <a href='accounts.php?event=".$_GET["event"]."&account=".$_GET["account"]."&status=".$_GET["status"]."&action=delete&article=".$articleId."' title='Delete'><img src='../miscs/images/Default/article_delete.png'></a> ";
                        }
                        
                        echo "<a href='accounts.php?event=".$_GET["event"]."&account=".$_GET["account"]."&status=".$_GET["status"]."&action=refresh&article=".$articleId."' title='Refresh Submits'><img src='../miscs/images/Default/article_refresh.png'></a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                    
                    // display the buttons for managing the articles
                    echo "<table width='100%'>";
                    echo "<tr>";
                    
                    // button for saved articles
                    if($status == "saved")
                    {
                        echo "<input type='submit' id='submit1' name='removeArticle' value='Remove'> ";
                    }
                    else
                    {
                        // buttons for DELETED articles
                        echo "<input type='submit' id='submit1' value='Restore' name='restoreArticle'> ";
                        echo "<input type='submit' id='submit1' value='Delete' name='deleteArticle'> ";
                        echo "<input type='submit' id='submit1' value='Delete all' name='deleteAllArticle'> ";
                    }
                    
                    echo "<input type='submit' id='submit1' value='Refresh Submits' name='refreshSubmit'>";
                    echo "</tr>";
                    echo "</table>";
                    echo "</form>";
                }
                
                echo "</div>";
            }
            
            return;
        }
		
		/**
		 * Display Account Statistics method: displays the status of the account and logs
		 * Paramaters: $accountId
		 * Return type: String
		 */
		function displayAccountStatistics($accountId) 
		{
			// query all the information of the account
			$accountQuery = mysql_query("SELECT id_number, username, name, position, email, last_login_date, date_registered, status, photo_path FROM argus_accounts WHERE account_id = '".$accountId."'") or die(mysql_error());
			
			// display only the account statistics if the account id exists in the database
			if(mysql_num_rows($accountQuery) > 0)
			{
				// set the account information
				$idNumber = mysql_result($accountQuery,0,"id_number");
				$username = mysql_result($accountQuery,0,"username");
				$name = mysql_result($accountQuery,0,"name");
				$position = mysql_result($accountQuery,0,"position");
				$email = mysql_result($accountQuery,0,"email");
				$lastLoginDate = mysql_result($accountQuery,0,"last_login_date");
				$dateRegistered = mysql_result($accountQuery,0,"date_registered");
				$status = mysql_result($accountQuery,0,"status");
                $photoPath = mysql_result($accountQuery,0,"photo_path");
				
				// set the title of the form
				if($status == "ENABLED")
				{
                    // set the title for enabled MEMEBRS and CONTRIBUTORS
                    echo "
                    <h3><a href='accounts.php?event=".strtolower($position)."'>".ucfirst(strtolower($position))."s</a> &raquo; ".$name."</h3>";
				}
				else
				{
					// title for Disabled accounts
					echo "<h3><a href='accounts.php?event=".strtolower($status)."'>Disabled</a> &raquo; ".$name."</h3>";
				}
				
				echo "<div class='bg1'>";
			
				// display the account information
				echo "
                <p>Account Information</p>
                <p align='center'>";
                
                // check if the user has a photo
                if(!empty($photoPath))
                {
                    // display the photo
                    echo "<img src='".$photoPath."'>";
                }
                else
                {
                    // display the no photo picture
                    echo "<img src='../images/accounts/default.png'>";
                }
                
                echo "
				<p id='box'>
				    ID Number : ".$idNumber."<br />
				    Username : ".$username."<br />
				    Name : ".$name."<br />
				    Position : ".$position."<br />
				    Email : ".$email."<br />
				    Last login date : ".date("F d, Y", $lastLoginDate)."<br />
				    Date registered : ".date("F d, Y", $dateRegistered)."<br />
				    Status : ".$status."<br />
				</p>";
				
				// display the buttons for managing the account information
				echo "<p align='right'>";
				
				if($status == "ENABLED")
				{
					// ACTIONS for ENABLED accounts
					echo "
                    <a href='accountscompose.php?event=edit&account=".$accountId."'><input type='submit' id='submit1' value='edit'></a> 
					<a href='accounts.php?event=".strtolower($position)."&action=disable&account=".$accountId."'><input type='submit' id='submit1' value='disable'></a>";
				}
				else
				{
					// ACTIONS for DISABLED accounts
					echo "
                    <a href='accountscompose.php?event=edit&account=".$accountId."'><input type='submit' id='submit1' value='edit'></a> 
					<a href='accounts.php?event=".strtolower($status)."&action=enable&account=".$accountId."'><input type='submit' id='submit1' value='enable'></a> 
					<a href='accounts.php?event=".strtolower($status)."&action=delete&account=".$accountId."'><input type='submit' id='submit1' value='delete'></a>";
				}
				
				echo "</p>";
			
				// check if the position is a contributor for additional display of information
				if($position == "CONTRIBUTOR")
				{
                    // include the account information class which will display the statistics of an account
                    include("class_libraries/AccountInformation.php");
                    $accountInformation = new AccountInformation($accountId);
                    
                    // display the mail information
                    $accountInformation -> displayMailInformation();
                    
					// display BUTTONS for managing mails
					echo "
                    <p align='right'>
					    <a href='accounts.php?event=".$_GET["event"]."&action=delsavedmails&account=".$accountId."'><input type='button' id='submit1' value='delete all saved'></a> 
					    <a href='accounts.php?event=".$_GET["event"]."&action=deltrashmails&account=".$accountId."'><input type='button' id='submit1' value='delete all trash'></a> 
					    <a href='accounts.php?event=".$_GET["event"]."&action=delallmails&account=".$accountId."'><input type='button' id='submit1' value='delete all mails'></a>
					</p>";
					
                    // display the article information
                    $accountInformation -> displayArticleInformation();
                    
					// display BUTTONS for managing articles
					echo "
                    <p align='right'>
                        <a href='accounts.php?event=viewarticles&account=".$accountId."&status=saved'><input type='button' id='submit1' value='view saved articles'></a> 
                        <a href='accounts.php?event=viewarticles&account=".$accountId."&status=deleted'><input type='button' id='submit1' value='view deleted articles'></a>
					</p>";
                    
                    // display the image information
                    $accountInformation -> displayImageInformation();
                    
                    // display the buttons for managing the images
                    echo "
                    <p align='right'>
                    <a href='accounts.php?event=".$_GET["event"]."&action=delsavedimages&account=".$accountId."'><input type='button' id='submit1' value='delete all saved'></a> 
                    <a href='accounts.php?event=".$_GET["event"]."&action=deltrashimages&account=".$accountId."'><input type='button' id='submit1' value='delete all trash'></a> 
                    <a href='accounts.php?event=".$_GET["event"]."&action=delallimages&account=".$accountId."'><input type='button' id='submit1' value='delete all images'></a>
                    </p>";
				}
				
				echo "</div>";
			}
			
			return;
		}
		
		/**
		 * Delete Mails method: deletes a mail of accounts
		 * Parameters: $accountId, $status
		 */
		function deleteMails($accountId, $status)
		{
			// delete the specified mails
			if($status == null)
			{
				// if deleteType is null which means that the user wants to delete all articles
				mysql_query("DELETE FROM argus_mails WHERE account_id = '".$accountId."'") or die(mysql_error());
			}
			else
			{
				// if not null, then delete only the specified type
				mysql_query("DELETE FROM argus_mails WHERE account_id = '".$accountId."' AND status = '".$status."'") or die(mysql_error());
			}
			
			return;
		}
		
        /**
         * Delete Images method: deletes images of accounts
         * Parameter: $accountId, $status
         */
        function deleteImages($accountId, $status)
        {
            if($status == null)
            {
                // query all the images of the user
                $imagesQuery = mysql_query("SELECT path FROM argus_images WHERE account_id = '".$accountId."'") or die(mysql_error());
                
                // delete the PHYSICAL file from the file server
                for($i=0; $i<mysql_num_rows($imagesQuery); $i++)
                {
                    $path = mysql_result($imagesQuery,$i,"path");
                    unlink($path);
                }
                
                // after deleting the physical file, delete data from the database
                mysql_query("DELETE FROM argus_images WHERE account_id = '".$accountId."'") or die(mysql_error());
            }
            else
            {
                // query the images of the user depending on the status
                $imagesQuery = mysql_query("SELECT path FROM argus_images WHERE account_id = '".$accountId."' AND status='".$status."'") or die(mysql_error());

                // delete the Physical file from the file server
                for($i=0; $i<mysql_num_rows($imagesQuery); $i++)
                {
                    $path = mysql_result($imagesQuery,$i,"path");
                    unlink($path);
                }
                
                // after deleting the physical file, delete data from the database
                mysql_query("DELETE FROM argus_images WHERE account_id = '".$accountId."' AND status='".$status."'") or die(mysql_error());
            }
            
            return;
        }
		
		/**
		 * Disable Account method: disables an account
		 * Paramter: $accountId
		 * Return type: void
		 */
		function disableAccount($accountId)
		{
			mysql_query("UPDATE argus_accounts SET status = 'DISABLED' WHERE account_id = '".$accountId."' AND status = 'ENABLED'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Enable Account method: enables a disabled account
		 * Parameter: $accountId
		 * Return type: void
		 */
		function enableAccount($accountId)
		{
			mysql_query("UPDATE argus_accounts SET status = 'ENABLED' WHERE account_id = '".$accountId."' AND status = 'DISABLED'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Delete Account method: deletes an account
		 * Paramter: $accountId
		 * return type: void
		 */
		function deleteAccount($accountId)
		{
			// get the id number and the position of the account
			$accountQuery = mysql_query("SELECT id_number, position, photo_path FROM argus_accounts WHERE account_id = '".$accountId."'") or die(mysql_error());
			$idNumber = mysql_result($accountQuery,0,"id_number");
			$position = mysql_result($accountQuery,0,"position");
            $photoPath = mysql_result($accountQuery,0,"photo_path");
			
			// delete all traces in the database for CONTRIBUTORS
			if($position == "CONTRIBUTOR")
			{	
				// delete all mails of the account
				mysql_query("DELETE FROM argus_mails WHERE account_id = '".$accountId."'") or die(mysql_error());
                
                // delete all saved articles of the account
                mysql_query("DELETE FROM argus_saved_articles WHERE account_id = '".$accountId."'") or die(mysql_error());
                
                // delete all the images of the account
                // select query all the contributor's articles
                $imagesQuery = mysql_query("SELECT path FROM argus_images WHERE account_id = '".$accountId."'") or die(mysql_error());
                
                // delete all the images of the user
                for($i=0; $i<mysql_num_rows($imagesQuery); $i++)
                {
                    // set the attribute
                    $path = mysql_result($imagesQuery,$i,"path");
                    
                    // delete the image
                    unlink($path);
                }
                
                // after deleting the physical trace of image, delete the image from the database
                mysql_query("DELETE FROM argus_images WHERE account_id = '".$accountId."'") or die(mysql_error());
			}
            
            // delete the photo of the user
			if(!empty($photoPath))
            {
                unlink($photoPath);
            }
            
            // check if the user has an id number or not..(guest has no id number
            if(!empty($idNumber))
            {            
		        // update the argus slu students database from REGISTERED to UNREGISTERED
		        mysql_query("UPDATE argus_slu_students SET status = 'UNREGISTERED' WHERE id_number = '".$idNumber."' and status = 'REGISTERED'") or die(mysql_error());
            }
			
			// delete the account from the database
			mysql_query("DELETE FROM argus_accounts WHERE account_id = '".$accountId."'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Add Account method: adds a new account
		 * Parameter: $idNumber, $username, $name, $password, $position, $status
		 * Return type: boolean
		 */
		function addAccount($idNumber, $username, $name, $password, $retypedPassword, $email, $position, $status)
		{
            // escape the characters that are needed to be escaped to avoid sql injection
            $idNumber = mysql_escape_string($idNumber);
            $username = mysql_escape_string($username);
            $name = mysql_escape_string($name);
            $password = mysql_escape_string($password);
            $retypedPassword = mysql_escape_string($retypedPassword);
            $email = mysql_escape_string($email);
			
			// check if the id number is blank or not, if the id number is blank, then that means that the administrator is adding
			// a special guest account. Special guest account has different validation.
            // trim the id number, removing the spaces found
			$idNumber = trim($idNumber);
			
			// check if the id number is empty or not
			if(empty($idNumber))
			{	
				// if the id number is empty, insert "GUEST" as an id number to identify that the account is a guest account
				$idNumber = "GUEST";
			}
			else
			{
				// validate the id number if it has an ID number
				$idError = $this -> validateIdNumber($idNumber, $name);
			}
			
			// validate the username
			$usernameError = $this -> validateUsername(null, $username);
			
			// validate the password
			$passwordError = $this -> validatePassword($password, $retypedPassword);
			
			// validate the email
			$emailError = $this -> validateEmail(null, $email);
			
			if($idError == null && $usernameError == null && $passwordError == null && $emailError == null)
			{
				// update the slu students database to REGISTERED from UNREGISTERED
				mysql_query("UPDATE argus_slu_students SET status = 'REGISTERED' WHERE id_number = '".$idNumber."'") or die(mysql_error());
				
				// insert the new account to the database if validation has passed
				mysql_query("INSERT INTO argus_accounts(id_number, username, password, name , position, email, last_login_date, date_registered, status)
						 	VALUES('".$idNumber."', '".$username."', '".$password."' ,'".$name."', '".$position."', '".$email."', '".time()."', '".time()."', '".$status."')") or die(mysql_error());
				
				return true;
			}
			else
			{
				// set the errors and return un successful validation
				$this -> errors = array("id" => $idError, "username" => $usernameError, "password" => $passwordError, "email" => $emailError);
				
				return false;
			}
			
			return;
		}
		
		/**
		 * Update Account method: updates an account
		 * Parameter: $accountId, $username, $password, $retypedPassword, $email, $status
		 * Return type: boolean
		 */
		function updateAccount($accountId, $username, $password, $retypedPassword, $email, $status)
		{
            // escape the characters that are needed to escaped to avoid sql injection
            $username = mysql_escape_string($username);
            $password = mysql_escape_string($password);
            $retypedPassword = mysql_escape_string($retypedPassword);
            $email = mysql_escape_string($email);
            
			// validate the username
			$usernameError = $this -> validateUsername($accountId, $username);
			
			// validate the password
			$passwordError = $this -> validatePassword($password, $retypedPassword);
			
			// validate the email
			$emailError = $this -> validateEmail($accountId, $email);
			
			// check for errors
			if($usernameError == null && $passwordError == null && $emailError == null)
			{
				// if validation has passed, update the account
				mysql_query("UPDATE argus_accounts SET username = '".$username."', password = '".$password."', email = '".$email."', status = '".$status."' WHERE account_id = '".$accountId."'") or die(mysql_error());
				
				// return successful update
				return true;
			}
			else
			{
				// if validation failed, set the errors
				$this -> errors = array("username" => $usernameError, "password" => $passwordError, "email" => $emailError);
				
				// return unsuccessful update
				return false;
			}
			
			return;
		}
		
		/**
		 * Get errors method: returns the errors that were committed during update and adding of accounts
		 * Return Type: string
		 */
		function getErrors()
		{
            // return the errors
			return $this -> errors;
		}
		
		/**
		 * Validate Email method: validates the email for correct syntax and if been already used
		 * Paramater: $accountId, $email
		 * Return Type: string
		 */
		function validateEmail($accountId, $email)
		{
			// include the email validator class and create a validator
			include("class_libraries/EmailValidator.php");
			$emailValidator = new EmailValidator();
			
			// validate the email
			$result = $emailValidator -> validateEmail($email);
			
			// check the result
			if($result == false)
			{
				// get the errors and return it
				return $emailValidator -> getErrors();
			}
			else
			{
				if($accountId == null)
				{
					// if the account id is null which means that the administrator is creating a new a account with a new email
					// this validates if the email entered has already been used by other accounts
					$emailQuery = mysql_query("SELECT email FROM argus_accounts WHERE email = '".$email."'") or die(mysql_error());
				}
				else
				{
					// if the account is not null which means that the administrator is updatin the email of the account
					// this validates if the email enetered has already been used by other account except the users own email address
					$emailQuery = mysql_query("SELECT email FROM argus_accounts WHERE email = '".$email."' AND account_id != '".$accountId."'") or die(mysql_error());
				}
				
				// check if the email has already been registered to someone else
				if(mysql_num_rows($emailQuery) > 0)
				{
					// return a message that the email has been registered to someone else
					return "The email address you have provided has already been registered by another member";
				}
			}
			
			return;
		}
		
		/**
		 * Validate Password method: validates the password if it matches the retyped password
		 * Paramater: $password, $retypedPassword
		 * Return Type: string
		 */
		function validatePassword($password, $retypedPassword)
		{
			// include the Password Validator Class and create a validator with 5-15 characters long
			include("class_libraries/PasswordValidator.php");
			$passwordValidator = new PasswordValidator(5, 15);
			
			// validate the password
			$result = $passwordValidator -> validatePassword($password, $retypedPassword);
			
			// check the result
			if($result == false)
			{
				// get the error and return the result
				return $passwordValidator -> getErrors();
			}
			
			return;
		}
		
		/**
		 * Validate Username method: validates the username if it has the correct format
		 * Paramter: $accountId, $username
		 * Return type: string
		 */
		function validateUsername($accountId, $username)
		{
			// include the username validator class and create a validator with 5-15 characters long
			include("class_libraries/UsernameValidator.php");
			$usernameValidator = new UsernameValidator(5, 15);
			
			// validate the username
			$result = $usernameValidator -> validateUsername($username);
			
			// check the result
			if($result == false)
			{
				// get the error and return it
				return $usernameValidator -> getErrors();
			}
			// do extra validation
			else
			{
				if($accountId == null)
				{
					// if account id is null, which means that a new account is being added.
					// perform a check if the username has already been taken by someone else
					$usernameQuery= mysql_query("SELECT username FROM argus_accounts WHERE username = '".$username."'") or die(mysql_error());
				}
				else
				{
					// if the account id is not null, which means that an update of account username is happening
					// perform a check if the username has already been taken avoiding the same username being used by the user
					$usernameQuery = mysql_query("SELECT username FROM argus_accounts WHERE username = '".$username."' AND account_id != '".$accountId."'") or die(mysql_error());
				}
				
				// check if the username has already been taken
				if(mysql_num_rows($usernameQuery) > 0)
				{
					// return a message that the username has already been registered to someone else
					return "The username you have provided has already been registered by another account";
				}
			}
			
			return;
		}
		
		/**
		 * Validate ID Number method: validates the id number
		 * Parameter: $idNumber, $name
		 * Return type: string
		 */
		function validateIdNumber($idNumber, $name)
		{
			// include the Id Number Validator Class and validate the id number
			include("class_libraries/IdNumberValidator.php");
			$idValidator = new IdNumberValidator();
			
			// validate the id number
			$result = $idValidator -> validateIdNumber($idNumber, $name);
			
			// check the result
			if($result == false)
			{
				// get the error and return it
				return $idValidator -> getErrors();
			}
			
			return false;
		}
    
        /**
         * Get Category name Method: retrieves the name of the category from the database
         * Parameter: $categoryId
         * Return Type: string
         */
        function getCategoryName($categoryId)
        {
            // include the name retriever class and retrieve the category name
            require_once("class_libraries/NameRetriever.php");
            $nameRetriever = new NameRetriever("category_id");
            
            // retrieve the name
            $categoryName = $nameRetriever -> getName($categoryId);
            
            return $categoryName;
        }
        
        /**
         * Limit Title method: limits the title of an article when displayed
         * Parameter: $title
         * Return type: title
         */
        function limitTitle($title)
        {
            // include the text limiter class and limit the title
            require_once("class_libraries/TextLimiter.php");
            $textLimiter = new TextLimiter();
            
            // limit the text up to 5 words only
            $title = $textLimiter -> limitText($title, 5);
            
            return $title;
        }
    
        /**
         * Refresh Article Submit: removes the article submit limit of an article
         * Parameter: $accountId, $articleId
         */
        function refreshArticleSubmit($accountId, $articleId)
        {
            // refresh the article
            mysql_query("UPDATE argus_saved_articles SET times_submitted = '0' WHERE account_id='".$accountId."' AND saved_article_id = '".$articleId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Delete Trash Articles Method: permanently deletes the trash article of an account
         * Parameter: $accountId
         */
        function deleteTrashArticles($accountId)
        {
            mysql_query("DELETE FROM argus_saved_articles WHERE account_id = '".$accountId."' AND status='DELETED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Remove Article Method: removes the saved article of the user sending it to the deleted section
         * Parameter: $accountId, $articleId
         */
        function removeArticle($accountId, $articleId)
        {
            // update the database
            mysql_query("UPDATE argus_saved_articles SET status='DELETED' WHERE account_id = '".$accountId."' AND saved_article_id = '".$articleId."'") or die(mysql_error());
            
            return;
        }
    
        /**
         * Restore Article Method: restores the deleted article back to the saved section
         * Parameter: $accountId, $articleId
         */
        function restoreArticle($accountId, $articleId)
        {
            // update the database
            mysql_query("UPDATE argus_saved_articles SET status='SAVED' WHERE account_id = '".$accountId."' AND saved_article_id = '".$articleId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Delete Article method: permanently deletes an article of an account
         * Parameter: $accountId, $articleId
         */
        function deleteArticle($accountId, $articleId)
        {
            // update the database
            mysql_query("DELETE FROM argus_saved_articles WHERE account_id = '".$accountId."' AND saved_article_id = '".$articleId."'") or die(mysql_error());
            
            return;
        }
    
        /**
         * Search Account Method: searches account from the database
         * Parameter: $accountKeyword, $accountSearchType
         * Return Type: string
         */
        function searchAccount($accountKeyword, $accountSearchType)
        {
            // check if the account keyword has a value
            if(trim($accountKeyword) == "")
            {
                // return and do not process anything
                return;
            }
            
            // check the account search type
            if($accountSearchType == "byUsername")
            {
                // query on the username area
                $accountsQuery = mysql_query("SELECT position, username, account_id, id_number, name, email, last_login_date FROM argus_accounts WHERE username LIKE '%".$accountKeyword."%'") or die(mysql_error());
            }
            else if($accountSearchType == "byName")
            {
                // query on the name area
                $accountsQuery = mysql_query("SELECT position, username, account_id, id_number, name, email, last_login_date FROM argus_accounts WHERE name LIKE '%".$accountKeyword."%'") or die(mysql_error());
            }
            else
            {
                // query on the email area
                $accountsQuery = mysql_query("SELECT position, username, account_id, id_number, name, email, last_login_date FROM argus_accounts WHERE email LIKE '%".$accountKeyword."%'") or die(mysql_error());
            }
            
            // set the title of the page
            echo "<h3></h3>";
            echo "<div class='bg1' id='tablePanel'>";
            
            // check if there are results from the query
            if(mysql_num_rows($accountsQuery) == 0)
            {
                // notify the user that there are no accounts searched
                echo "<p><h3 align='center'>There are no search results</h3></p>";
            }
            else
            {
                // display the results in a table
                echo "<table width='100%'>";
                echo "<tr>";
                echo "<th align='center'>ID Number</th>";
                echo "<th>Username</th>";
                echo "<th>Name</th>";
                echo "<th>Email</th>";
                echo "<th>Last Login Date</th>";
                echo "<th>Position</th>";
                echo "</tr>";
                
                // display the results
                $color = true;
                
                for($i=0; $i<mysql_num_rows($accountsQuery); $i++)
                {
                    // display the rows in an alternate color manner
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
                    
                    // set the attributes to be displayed
                    $accountId = mysql_result($accountsQuery,$i,"account_id");
                    $idNumber = mysql_result($accountsQuery,$i,"id_number");
                    $username = mysql_result($accountsQuery,$i,"username");
                    $name = mysql_result($accountsQuery,$i,"name");
                    $email = mysql_result($accountsQuery,$i,"email");
                    $lastLoginDate = date("m/d/y", mysql_result($accountsQuery,$i,"last_login_date"));
                    $position = mysql_result($accountsQuery,$i,"position");
                    
                    // display the results
                    echo "<td align='center'>".$idNumber."</td>";
                    echo "<td><a href='accounts.php?event=statistics&account=".$accountId."'>".$username."</a></td>";
                    echo "<td>".$name."</td>";
                    echo "<td>".$email."</td>";
                    echo "<td>".$lastLoginDate."</td>";
                    echo "<td>".$position."</td>";
                    
                    echo "</tr>";
                }
                
                echo "</table>";
            }
            
            echo "</div>";

            return;
        }
        
        /**
         * Get Account info: retrieves the infos of the account for generating values when creating contributors
         * Parameter: $idNumber
         * Return Type: string
         */
        function getAccountInfo($idNumber)
        {
            // query the info of the account using the id number and works only for unregistered students
            $accountQuery = mysql_query("SELECT first_name, last_name, middle_initial FROM argus_slu_students WHERE id_number = '".$idNumber."' AND status='UNREGISTERED'") or die(mysql_error());
            
            // check if the query exists
            if(mysql_num_rows($accountQuery) > 0)
            {
                // set the attributes
                $userInfo["firstName"] = mysql_result($accountQuery,0,"first_name");
                $userInfo["lastName"] = mysql_result($accountQuery,0,"last_name");
                $userInfo["middleInitial"] = mysql_result($accountQuery,0,"middle_initial");
                
                // return the user info
                return $userInfo;
            }
            
            return;
        }
	}
?>