<?php
	/**
	 * Filename : IssuesForm.php
	 * Description : class file for managing issues of articles
	 * Date Created : December 2, 2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	string displayBanner()
	 *	string displayIssues($status)
	 *	boolean addIssue($name, $description, $status)
	 *	string validateName($issueId, $name)
	 *	string getErrors()
	 *	void disableIssue($issueId)
	 *	void enableIssue($issueId)
	 *	void deleteIssue($issueId)
	 *	boolean updateIssue($issueId, $name, $description, $status)
	 *	string displayIssueStatistics($issueId)
	 */
	
	class IssuesForm
	{
		var $errors;
		
		/**
		 * Display Banner Method: displays the menus for managing issues
		 * Return type: string
		 */
		function displayBanner()
		{
			echo "
            <div class='bg2'>
			<h2><em>Issues Manager</em></h2>
			<p align='center'>";
			
			// menus
			echo "
            <a href='issues.php'>Available</a> . 
			<a href='issues.php?event=disabled'>Disabled</a> . 
			<a href='issuescompose.php'>Create</a>";
			
			echo "
            </p>
			</div>";
			
			return;
		}
		
		/**
		 * Display Issues method: displays the enabled or disabled issues
		 * Parameter: $status
		 * return type: string
		 */
		function displayIssues($status)
		{
			// query the issues
            if($status == "ENABLED") 
            {
                // when status is enabled, query also the currently published issue
			    $issuesQuery = mysql_query("SELECT issue_id, name, date_created,status FROM argus_issues WHERE status = '".$status."' OR status ='PUBLISHED'") or die(mysql_error());
            }
            else
            {
                $issuesQuery = mysql_query("SELECT issue_id, name, date_created,status FROM argus_issues WHERE status = '".$status."'") or die(mysql_error());
            }
			
			// set the title for the form
            echo "
            <h3>".ucfirst(strtolower($status))."</h3>
			<div class='bg1' id='tablePanel'>";
			
			// check if there are issues queried from the database
			if(mysql_num_rows($issuesQuery) == 0)
			{
				// notify the user that there are no issues
				echo "<p><h3 align='center'>There are no ".$status." issues</h3></p>";
			}
			else
			{
                // inlcude the TOOL TIP ajax and create a tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                // include the checkbox funtions where check box are allowed to be selected/unselected all
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
				// create the form and table
				echo "
                <form id='form_id' method='post' action='".$_SERVER['PHP_SELF']."?event=".strtolower($status)."'>
				<table width='100%'>
				<tr>
				<th><input type='checkbox' onClick='toggleCheckBoxes(\"issueIds\")'></th>
				<th>Name</th>
				<th>Date Created</th>
				<th>Action</th>
				</tr>";
				
				// display the issues
				$color = true;
				
				for($i=0; $i<mysql_num_rows($issuesQuery); $i++)
				{
					// set the table rows in an alternate color manner
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
					
					// set the properties of the issue that is to be displayed
					$issueId = mysql_result($issuesQuery,$i,"issue_id");
					$name = mysql_result($issuesQuery,$i,"name");
					$dateCreated = mysql_result($issuesQuery,$i,"date_created");
					$queriedStatus = mysql_result($issuesQuery,$i,"status");
					
                    // display the queried issue
                    // check for queried status
                    if($queriedStatus == "PUBLISHED")
                    {
                        // if the current status of the issue is published, then the user is not allowed to manipulate
                        // the published issue
                        echo "<td><input type='checkbox' disabled='disabled' value = '".$issueId."'></td>";
                    }
                    else
                    {
                        echo "<td><input type='checkbox' name='issueIds[]' value = '".$issueId."'></td>";
                    }
                    
                    echo "<td><a href='issues.php?event=statistics&issue=".$issueId."'>".$name."</a></td>";
					echo "<td>".date("m/d/y", $dateCreated)."</td>";
					echo "<td>";
					
					// set the actions for ENABLED issues
					if($status == "ENABLED")
					{
						echo "<a href='issuescompose.php?event=edit&issue=".$issueId."' title='Edit'><img src='../miscs/images/Default/category_edit.png' alt='edit'></a> ";
                        
                        // check for the queried status
                        if($queriedStatus == "PUBLISHED")
                        {
                            // if the current status of the issue is published, then the user is not allowed to disable the issue
                            echo "<img src='../miscs/images/Default/user_lock.png'>";
                        }
                        else
                        {
                            echo "<a href='issues.php?event=".strtolower($status)."&action=disable&issue=".$issueId."' title='Disable'><img src='../miscs/images/Default/user_lock.png' alt='disable'></a>";
                        }
					}
					// set the actions for DISABLED issues
					else
					{
						echo "<a href='issuescompose.php?event=edit&issue=".$issueId."' title='Edit'><img src='../miscs/images/Default/category_edit.png' alt='edit'></a> ";
						echo "<a href='issues.php?event=".strtolower($status)."&action=enable&issue=".$issueId."' title='Enable'><img src='../miscs/images/Default/issue_enable.png' alt='enable'></a> ";
						echo "<a href='issues.php?event=".strtolower($status)."&action=delete&issue=".$issueId."' title='Delete'><img src='../miscs/images/Default/category_delete.png' alt='delete'></a>";
					}
					
					echo "</td>";
					echo "</tr>";
				}
				
				echo "</table>";
				
				// set the buttons
				echo "<table width='100%'>";
				echo "<tr><td>";
				
				// set the buttons for ENABLED issues
				if($status == "ENABLED")
				{
					echo "<input type='submit' id='submit1' value='Disable' name='disable'>";
				}
				// set the buttons for DISABLED issues
				else
				{
					echo "<input type='submit' id='submit1' value='Enable' name='enable'> ";
					echo "<input type='submit' id='submit1' value='Delete' name='delete'>";
				}
				
				echo "</td></tr>";
				echo "</table>";
				
				echo "</form>";
			}
			
			echo "</div>";
			
			return;
		}
		
		/**
		 * Add Issue method: add a new issue to the database
		 * Parameters: $name, $description, $status
		 * Return type: boolean
		 */
		function addIssue($name, $description, $status)
		{
            // escape the characters that are needed to be escaped to avoid sql injection
            $name = mysql_escape_string($name);
            $description = mysql_escape_string($description);
            
			// validate the name
			$nameError = $this -> validateName(null, $name);
			
			// validate the description
			$descriptionError = $this -> validateDescription($description);
			
			// check if the validation has passed
			if($nameError == null && $descriptionError == null)
			{
				// add the new category to the database
				mysql_query("INSERT INTO argus_issues(name, description, date_created, status) VALUES ('".$name."', '".$description."', '".time()."', '".$status."')") or die(mysql_error());
				
				// return a successful add
				return true;
			}
			else
			{
				// set the error
				$this -> errors = array("name" => $nameError, "description" => $descriptionError);
				
				// return an unsuccessful add
				return false;
			}
			
			return;
		}
		
		/**
		 * Validate Description Method: validates the description if it has the correct length
		 * Parameters: $description
		 * Return type: string
		 */
		function validateDescription($description)
		{
			// include the description validator class and create a validator than accepts only a max char of 255
			include("class_libraries/DescriptionValidator.php");
			$descriptionValidator = new DescriptionValidator(255);
			
			// validate the description
			$result = $descriptionValidator -> validateDescription($description);
			
			// check the result
			if($result == false)
			{
				// if validation failed, get the errors committed and return it
				return $result -> getErrors();
			}
			
			return;
		}
		
		/**
		 * Validate Name method: validates the name of the issue if it is valid
		 * Parameters: $name
		 * Return type: string
		 */
		function validateName($issueId, $name)
		{
			// check if empty
			if(empty($name))
			{
				// return a message that the name is empty
				return "Please provide a category name";
			}
			// check if the name has the correct character length
			else if(strlen($name) < 3 || strlen($name) > 15)
			{
				// return a message that the name should be 5 - 15 characters long
				return "Category name should be 5-15 characters long";
			}
            // check if the issue name is unique
            else
            {
                // query the issues from the database where the issue name is as the same as the issue that is about to be inserted
                $issueNameQuery = mysql_query("SELECT name FROM argus_issues WHERE name = '".$name."' AND issue_id != '".$issueId."'") or die(mysql_error());
                
                // check the result
                if(mysql_num_rows($issueNameQuery) > 0)
                {
                    // return a message that the issue name has already been used
                    return "The issue name '".$name."' had already been registered";
                }
            }
			
			return;
		}
		
		/**
		 * Get errors method: returns the errors committed during the adding/editing of categories
		 * Return type: string
		 */
		function getErrors()
		{
			// return the errors
			return $this -> errors;
		}
	
		/**
		 * Disable issue method: disables an issue
		 * Parameter: $issueId
		 */
		function disableIssue($issueId)
		{
            // all articles that is in that issue will be removed setting there issues back to 0 which means no issue
            mysql_query("UPDATE argus_articles SET issue_id = '0' WHERE issue_id = '".$issueId."'") or die(mysql_error());
            
			// disable the issue
			mysql_query("UPDATE argus_issues SET status = 'DISABLED' WHERE issue_id = '".$issueId."' AND status = 'ENABLED'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Enable issue method: enables an issue
		 * Parameter: $issueId
		 */
		function enableIssue($issueId)
		{
			// enable the issue
			mysql_query("UPDATE argus_issues SET status = 'ENABLED' WHERE issue_id = '".$issueId."' AND status = 'DISABLED'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Delete issue method: deletes an issue
		 * Parameter: $issueId
		 */
		function deleteIssue($issueId)
		{
			// update all articles that is in that issue for approved articles only
			mysql_query("UPDATE argus_articles SET issue_id = '0' WHERE issue_id = '".$issueId."' AND status = 'APPROVED'") or die(mysql_error());
			
			// delete the issue
			mysql_query("DELETE FROM argus_issues WHERE issue_id = '".$issueId."'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Update issue method: updates the issue
		 * Parameters: $issueId, $name, $status
		 * return type: boolean
		 */
		function updateIssue($issueId, $name, $description, $status)
		{
            // escape the characters that are needed to be escaped to avoid sql injection
            $name = mysql_escape_string($name);
            $description = mysql_escape_string($description);
            
			// validate the name
			$nameError = $this -> validateName($issueId, $name);
            
            // validate the description
            $descriptionError = $this -> validateDescription($description);
			
			// check if validation is successful
			if($nameError == null && $descriptionErrpr == null)
			{
				// query the current status of the issue
				$statusQuery = mysql_query("SELECT status FROM argus_issues WHERE issue_id = '".$issueId."'") or die(mysql_error());
				
				// check what current status the issue is
				if(mysql_result($statusQuery,0,"status") == "ENABLED")
				{
					// if current status is ENABLED and changing to DISABLED, remove all those articles that is in that issue
					if($status == "DISABLED")
					{
						// remove articles in that issue
						mysql_query("UPDATE argus_articles SET issue_id = '' WHERE issue_id = '".$issueId."'") or die(mysql_error());
					}
				}
				
				// update the issue
				mysql_query("UPDATE argus_issues SET name = '".$name."', description = '".$description."', status = '".$status."' WHERE issue_id = '".$issueId."'") or die(mysql_error());
				
				// return successful update
				return true;
			}
			else
			{
				// if validation is not successful, set the error
				$this -> errors = array("name" => $nameError, "description" => $descriptionError);
				
				// return unsuccessful update
				return false;
			}
			
			return;
		}
		
		/**
		 * Display Issue Statistics method: displays the statistics of an issue
		 * Parameter: $issueId
		 * return type: string
		 */
		function displayIssueStatistics($issueId)
		{
			// query the statistics of the issue from the database
			$issueQuery = mysql_query("SELECT name, description, date_created, status FROM argus_issues WHERE issue_id = '".$issueId."'") or die(mysql_error());
		
			// check if the issue exists from the database
			if(mysql_num_rows($issueQuery) > 0)
			{
				// set the queried attributes
				$name = mysql_result($issueQuery,0,"name");
				$description = mysql_result($issueQuery,0,"description");
				$dateCreated = date("F d, Y", mysql_result($issueQuery,0,"date_created"));
				$status = mysql_result($issueQuery,0,"status");

				// set the title of the form
				echo "<h3><a href='issues.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo; ".$name."</h3>";

				
				echo "<div class='bg1'>";
				
				// diplay the attributes
				echo "<p>Issue Information</p>";
				echo "<p id='box'>";
				echo "Name : ".$name."<br />";
				echo "Description : ".$description."<br />";
				echo "Date Created : ".$dateCreated."<br />";
				echo "Status : ".$status."<br />";
				echo "</p>";
				
				// display the buttons for managing the issue
				echo "<p align='right'>";
				echo "<a href='issuescompose.php?event=edit&issue=".$issueId."'><input type='button' id='submit1' value='edit'></a> ";
				
				if($status == "ENABLED")
				{
					// button for ENABLED issue
					echo "<a href='issues.php?event=".strtolower($status)."&action=disable&issue=".$issueId."'><input type='button' id='submit1' value='disable'></a>";
				}
				else if($status == "DISABLED")
				{
					// button for DISABLED issue
					echo "<a href='issues.php?event=".strtolower($status)."&action=enable&issue=".$issueId."'><input type='button' id='submit1' value='enable'></a> ";
					echo "<a href='issues.php?event=".strtolower($status)."&action=delete&issue=".$issueId."'><input type='button' id='submit1' value='delete'></a>";
				}
				
				echo "<p>";
				
				// query the number of articles that is in that issue
				$articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE issue_id = '".$issueId."'") or die(mysql_error());
				$articlesCount = mysql_num_rows($articlesQuery);
				
				// display the article information
				echo "<p id='box'>";
				echo "Number of Articles : ".$articlesCount."<br />";
				echo "</p>";
				
				echo "</div>";
			}
			
			return;
		}
	}
?>