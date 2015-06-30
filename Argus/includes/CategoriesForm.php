<?php
	/**
	 * Filename : CategoriesForm.php
	 * Description : Contains the functions and components for managing categories/sections of the publication
	 * Date Created : December 1, 2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	string displayBanner()
	 *	string displayCategories($status)
	 *	string displayCategoryStatistics($categoryId)
	 *	void disableCategory($categoryId)
	 *	void enableCategory($categoryId)
	 *	void deleteCategory($categoryId)
	 *	boolean addCategory($status, $description, $categoryId)
	 *	boolean updateCategory($categoryId, $status, $description, $categoryId)
	 *	string getErrors()
	 *	string validateName($categoryId, $name)
     *  void moveCategory()
     *  void updatePositions()
	 *	string validateDescription($description)
	 */
	
	class CategoriesForm
	{
		var $errors;
		
		/**
		 * Display Banner method: displays the menu for managing categories
		 * Return type: String
		 */
		function displayBanner()
		{
			echo "
            <div class='bg2'>
			<h2><em>Categories Manager</em></h2>
			<p align='center'>";
			
			// menus
			echo "
            <a href='categories.php'>Available</a> . 
			<a href='categories.php?event=disabled'>Disabled</a> . 
			<a href='categoriescompose.php'>Create</a>";
			
			echo "
            </p>
			</div>";
			
			return;
		}
		
		/**
		 * Display Categories method: displays the categories depending on the paramater.
		 * Paramater: $status
		 * Return type: String
		 */
		function displayCategories($status)
		{
			// query the categories from the database and arrange them with there positions
			$categoriesQuery = mysql_query("SELECT category_id, name, status, date_created, position FROM argus_categories WHERE status = '".$status."' ORDER BY position ASC") or die(mysql_error());
			
			// set the title of the form
            echo "
            <h3>".ucfirst(strtolower($status))."</h3>
			<div class='bg1' id='tablePanel'>";
			
			// check if there is a category queried from the database
			if(mysql_num_rows($categoriesQuery) == 0)
			{
				// notify the user that the category that is being requested is not available
				echo "<p><h3 align='center'>There are no ".$status." categories</h3></p>";
			}
			else
			{
                // inlcude the TOOL TIP ajax and create a tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                // include the checkbox funtions where check box are allowed to be selected/unselected all
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
				// set the form and display the categories in a table form
				echo "
                <form id='form_id' method='post' action='".$_SERVER['PHP_SELF']."?event=".strtolower($status)."'>
				<table width='100%'>
				<tr>
				<th><input type='checkbox' onClick='toggleCheckBoxes(\"categoryIds\")'></th>
				<th>Name</th>
				<th>Date created</th>";
				
				// display only the POSITION,ORDER, and Number of Articles on ENABLED categories
				if($status == "ENABLED")
				{
					echo "
                    <th>Published Articles</th>
                    <th>Position number</th>
					<th>Order</th>";
				}
				
				echo "
                <th>Action</th>
				</tr>";
				
				// display the categories
				$color = true;
				
				for($i=0; $i<mysql_num_rows($categoriesQuery); $i++)
				{
					// display each row in an alternate color
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
					
					// set the attributes of the categories
					$categoryId = mysql_result($categoriesQuery,$i,"category_id");
					$name = mysql_result($categoriesQuery,$i,"name");
					$dateCreated = date("m/d/y", mysql_result($categoriesQuery,$i,"date_created"));
                    
                    // query if there are published articles in that category
                    // categories that has a published article should not be allowed to be disabled by the administrator
                    $articlesCountQuery = mysql_query("SELECT article_id FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED'") or die(mysql_error());
					$articlesCount = mysql_num_rows($articlesCountQuery);
                    
					// display the attributes
                    // check the name of the category
                    // if the name of the cateogry is "Uncategorized" which by default is not allowed to be disabled
                    // also check if there are published articles on that category
                    if(($status == "DISABLED" && $name == "Uncategorized") || $articlesCount > 0)
                    {
                        // if the name is "uncategorized", users are not allowed to delete the uncategorized section by default
                        echo "<td><input type = 'checkbox' disabled='disabled'></td>";
                    }
                    else
                    {
                        echo "<td><input type='checkbox' name='categoryIds[]' value='".$categoryId."'></td>";
                    }
                    
                    echo"
					<td><a href='categories.php?event=statistics&category=".$categoryId."'>".$name."</a></td>
					<td>".$dateCreated."</td>";
				
					if($status == "ENABLED")
					{                        
                        echo "<td>".$articlesCount."</td>";
                        
						$position = mysql_result($categoriesQuery,$i,"position");
	
						echo "<td><select id='position' name='positions[]'>";
						
						// set the position combo box
						for($j=0; $j<mysql_num_rows($categoriesQuery); $j++)
						{
							// select the right position of the article
							if($j+1 == $position)
							{
								echo "<option value='".$position."' selected='selected'>".($j+1)."</option>";
							}
							else
							{
								echo "<option value='".($j+1)."'>".($j+1)."</option>";
							}
						}
						
						echo "</select></td>";
						echo "<td>";
						
						// set the links to move the categories up and down
						if($i == 0)
						{
							// if this category is at the most top of all categories, then disable the move up link
							echo "<img src='../miscs/images/Default/move_up.png'> ";
						}
						else
						{
							// if this category is in between of other categories, enable the move up link
							echo "<a href='categories.php?event=".$_GET["event"]."&action=moveup&category=".$categoryId."'><img src='../miscs/images/Default/move_up.png'></a> ";
						}
						
						if($i+1 == mysql_num_rows($categoriesQuery))
						{
							// if this category is at the most bottom of all categories, then disabled the move down link
							echo "<img src='../miscs/images/Default/move_down.png'>";
						}
						else
						{
							// if this category is in between of other categories, enable the move down link
							echo "<a href='categories.php?event=".strtolower($status)."&action=movedown&category=".$categoryId."'><img src='../miscs/images/Default/move_down.png'></a>";
						}
						
						echo "</td>";
					}
					
					echo "
                    <td>
                    <a href='categoriescompose.php?event=edit&category=".$categoryId."' title='edit'><img src='../miscs/images/Default/category_edit.png' alt='edit'></a> ";

					if($status == "ENABLED")
					{
						// set the actions for ENABLED categories
                        // check if there are published articles in that category
                        if($articlesCount == 0)
                        {
						    echo "<a href='categories.php?event=".strtolower($status)."&action=disable&category=".$categoryId."' title='Disable'><img src='../miscs/images/Default/user_lock.png' alt='disable'></a>";
                        }
                        else
                        {
                            // do not allow the user to disable the category if it has a published article
                            echo "<img src='../miscs/images/Default/user_lock.png' alt='disable'>";
                        }
					}
					else
					{
						// set the actions for DISABLED categories
						echo "<a href='categories.php?event=".strtolower($status)."&action=enable&category=".$categoryId."' title='Enable'><img src='../miscs/images/Default/b.gif' alt='enable'></a> ";
                        
                        // check the name of the category
                        if($name == "Uncategorized")
                        {
                            // disable the delete action if the name of the category is "uncategorized"
                            echo "<img src='../miscs/images/Default/category_delete.png' alt='delete'>";
                        }
                        else
                        {
                            echo "<a href='categories.php?event=".strtolower($status)."&action=delete&category=".$categoryId."' title='Delete'><img src='../miscs/images/Default/category_delete.png' alt='delete'></a>";
                        }
					}
					
					echo "</td>";					
					echo "</tr>";
				}
				
				echo "</table>";
				
				// display the buttons for managing categories
				echo "<table width='100%'>";
				echo "<tr>";
				echo "<td>";
				
				if($status == "ENABLED")
				{
					// buttons for enabled categories
					echo "
                    <input type='submit' id='submit1' value='Disable' name='disable'> 
					<input type='submit' id='submit1' value='Update positions' name='update'>";
				}
				else
				{
					// buttons for disabled categories
					echo "
                    <input type='submit' id='submit1' value='Enable' name='enable'> 
					<input type='submit' id='submit1' value='Delete' name='delete'>";
				}
				
				echo "</td>
				</tr>
				</table>
				</form>";
			}
			
			echo "</div>";
			
			return;
		}
		
		/**
		 * Display Category Satistics method: displays the informations on that category
		 * Paramater: $categoryId
		 * Return type: String
		 */
		function displayCategoryStatistics($categoryId)
		{
			// query all the information about the specified category
			$categoryQuery = mysql_query("SELECT name, description, status, date_created FROM argus_categories WHERE category_id = '".$categoryId."'") or die(mysql_error());
		
			// display only the category statistics if the category exists in the database
			if(mysql_num_rows($categoryQuery) > 0)
			{
				// set the attributes
				$name = mysql_result($categoryQuery,0,"name");
				$description = mysql_result($categoryQuery,0,"description");
				$dateCreated = mysql_result($categoryQuery,0,"date_created");
				$status = mysql_result($categoryQuery,0,"status");
				
				// set the title of the form
				echo "
                <h3><a href='categories.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo; ".$name."</h3>
				<div class='bg1'>";
				
				// display the category information
				echo "
                <p>Category Information</p>
				<p id='box'>
				Name : ".$name."<br />
				Description : <span id='DESCRIPTION.".$categoryId."' class='editText'>".$description."</span><br />
				Date Created : ".date("F d, Y", $dateCreated)."<br />
				Status : ".$status."<br />
				</p>";
				
				// set the buttons for mananging the category
				echo "
                <p align = 'right'>
				<a href='categoriescompose.php?event=edit&category=".$categoryId."'><input type='button' id='submit1' value='edit'></a> ";
				
				if($status == "ENABLED")
				{
					// buttons for ENABLED status
					echo "<a href='categories.php?event=".strtolower($status)."&action=disable&category=".$categoryId."'><input type='button' id='submit1' value='disable'></a>";
				}
				else
				{
					// buttons for DISABLED status
					echo "<a href='categories.php?event=".strtolower($status)."&action=enable&category=".$categoryId."'><input type='button' id='submit1' value='enable'></a> ";
					echo "<a href='categories.php?event=".strtolower($status)."&action=delete&category=".$categoryId."'><input type='button' id='submit1' value='delete'></a>";
				}
				
				echo "</p>";
				
				// query the number of PUBLISHED articles that is in that category
				$articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED'") or die(mysql_error());
				$publishedArticlesCount = mysql_num_rows($articlesQuery);
				
				// query the number of PENDING articles in the category
				$articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PENDING'") or die(mysql_error());
				$pendingArticlesCount = mysql_num_rows($articlesQuery);
				
				// query the number of APPROVED articles in the category
				$articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'APPROVED'") or die(mysql_error());
				$approvedArticlesCount = mysql_num_rows($articlesQuery);
							
				// query the number of REJECTED articles in the category
				$articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'REJECTED'") or die(mysql_error());
				$rejectedArticlesCount = mysql_num_rows($articlesQuery);
                
                // query the number of SAVED articles in the category
                $articlesQuery = mysql_query("SELECT saved_article_id FROM argus_saved_articles WHERE category_id = '".$categoryId."' AND status = 'SAVED'") or die(mysql_error());
				$savedArticlesCount = mysql_num_rows($articlesQuery);
                
                // query the number of DELETED articles in the category
                $articlesQuery = mysql_query("SELECT saved_article_id FROM argus_saved_articles WHERE category_id = '".$categoryId."' AND status = 'DELETED'") or die(mysql_error());
                $deletedArticlesCount = mysql_num_rows($articlesQuery);
                
				// display the article information
				echo "<p>Article Information</p>";
				echo "<p id='box'>";
				echo "Published Articles : ".$publishedArticlesCount."<br />";
				echo "Pending Articles : ".$pendingArticlesCount."<br />";
				echo "Approved Articles : ".$approvedArticlesCount."<br />";
				echo "Rejected Articles : ".$rejectedArticlesCount."<br />";
                echo "Saved Articles : ".$savedArticlesCount."<br />";
                echo "Deleted Articles : ".$deletedArticlesCount."<br /><br />";
				echo "Total Articles : ".($publishedArticlesCount + $pendingArticlesCount + $approvedArticlesCount + $rejectedArticlesCount + $savedArticlesCount + $deletedArticlesCount)."<br />";
				echo "</p>";
				
				echo "</div>";
			}
			
			return;
		}
		
		/**
		 * Disable Category Method: disables category
		 * Parameter: $categoryId
		 */
		function disableCategory($categoryId)
		{
            // select all published articles in that category
            $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED'") or die(mysql_error());

            // each published article, delete the comments of that article
            for($i=0; $i < mysql_num_rows($articlesQuery); $i++)
            {
                // set the article id
                $articleId = mysql_result($articlesQuery,$i,"article_id");
                
                // delete the comments
                mysql_query("DELETE FROM argus_comments WHERE article_id = '".$articleId."'") or die(mysql_error());
            }
                        
			// remove all published articles in that given category removing the category and setting the issue to null
			mysql_query("UPDATE argus_articles SET status = 'APPROVED', issue_id = '0' WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED'") or die(mysql_error());
			
			// disable the category
			mysql_query("UPDATE argus_categories SET status = 'DISABLED' WHERE category_id = '".$categoryId."' AND status = 'ENABLED'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Enable Category method: enables categories
		 * Parameter: $categoryId
		 */
		function enableCategory($categoryId)
		{
			// get the number of categories published
			$categoriesQuery = mysql_query("SELECT category_id FROM argus_categories WHERE status = 'ENABLED'") or die(mysql_error());
			$categoriesCount = mysql_num_rows($categoriesQuery);
			
			// enable the category appending the category and updating the position
			mysql_query("UPDATE argus_categories SET status = 'ENABLED', position = '".($categoriesCount + 1)."' WHERE category_id = '".$categoryId."' AND status = 'DISABLED'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Delete Category method: deletes categories
		 * Parameter: $categoryId
		 */
		function deleteCategory($categoryId)
		{
			// delete the category
			mysql_query("DELETE FROM argus_categories WHERE category_id = '".$categoryId."' AND status = 'DISABLED'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Add Category method: adds a category
		 * Paramater: $name, $description, $status
		 * Return type: boolean
		 */
		function addCategory($name, $description, $status)
		{
            // escape the characters that are needed to be escaped to avoid sql injection
            $name = mysql_escape_string($name);
            $description = mysql_escape_string($description);
            
			// validate the name
			$nameError = $this -> validateName(null, $name);
			
			// validate the description
			$descriptionError = $this -> validateDescription($description);
			
			// check if validation has passed
			if($nameError == null && $descriptionError == null)
			{
				// check the status
				if($status == "ENABLED")
				{
					// count the number of enabled categories
					$categoriesQuery = mysql_query("SELECT category_id FROM argus_categories WHERE status = 'ENABLED'") or die(mysql_error);
					$categoriesCount = mysql_num_rows($categoriesQuery);
					
					// add the category
					mysql_query("INSERT INTO argus_categories(name, description, status, date_created, position)
								 VALUES('".$name."', '".$description."', '".$status."', '".time()."', '".($categoriesCount + 1)."')") or die(mysql_error());
				}
				else {
					// add the category in the disabled section
					mysql_query("INSERT INTO argus_categories(name, description, status, date_created)
								 VALUES('".$name."', '".$description."', '".$status."', '".time()."')") or die(mysql_error());
				}
				
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
		 * Update Category method: updates a category
		 * Paramater: $categoryId, $name, $desdription, $status
		 * Return type: boolean
		 */
		function updateCategory($categoryId, $name, $description, $status)
		{
            // escape the characters that are needed to be escaped to avoid sql injection
            $name = mysql_escape_string($name);
            $description = mysql_escape_string($description);
            
			// validate the name
			$nameError = $this -> validateName($categoryId, $name);
			
			// check if validation has passed
			if($nameError == null)
			{
				// check the status
				if($status == "ENABLED")
				{
					// query the current status of the category
					$statusQuery = mysql_query("SELECT status FROM argus_categories WHERE category_id = '".$categoryId."'") or die(mysql_error());
					
					// do not update the position if the current  status is ENABLED
					if(mysql_result($statusQuery,0,"status") == "ENABLED")
					{
						mysql_query("UPDATE argus_categories SET name = '".$name."', description = '".$description."' WHERE category_id = '".$categoryId."'") or die(mysql_error());
					}
					else
					{
						// count the number of enabled categories
						$categoriesQuery = mysql_query("SELECT category_id FROM argus_categories WHERE status = 'ENABLED'") or die(mysql_error);
						$categoriesCount = mysql_num_rows($categoriesQuery);
						
						// update the category
						mysql_query("UPDATE argus_categories SET name = '".$name."', description = '".$description."', position = '".($categoriesCount + 1)."', status = '".$status."' WHERE category_id = '".$categoryId."'") or die(mysql_error());
					}
				}
				else
				{
					// update the category
					mysql_query("UPDATE argus_categories SET name = '".$name."', description = '".$description."', status = '".$status."' WHERE category_id = '".$categoryId."'") or die(mysql_error());
				}
				
				// return a successful update
				return true;
			}
			else 
			{
				// set the error
				$this -> errors = array("name" => $nameError, "description" => $descriptionError);
				
				// return an unsuccessful update
				return false;
			}
			
			return;
		}
		
		/**
		 * Get Errors method: returns the errors committed during the adding/editing of categories
		 * return type: string
		 */
		function getErrors()
		{
			// return the errors
			return $this -> errors;
		}
		
		/**
		 * Validate Name method: validates the name of the category
		 * parameter: $categoryId, $category
		 * Return type: string
		 */
		function validateName($categoryId, $name)
		{
			// check if the name is blank
			if(empty($name))
			{
				// return a message that the name is blank
				return "Please provide a category name";
			}
			// check if the length of the category  is 5-15 characters long
			else if(strlen($name) < 4 || strlen($name) > 15)
			{
				// return a message that the character length is invalid
				return "Category names should be 4-15 characters long";
			}
            // check if the category name is unique
            else
            {
                if($categoryId != null)
                {
                    // if the category is not null, then that means that the category is just being updated
                    // do the necessary query when updating a category name
                    // query the database if it contains the same category name
                    $nameQuery = mysql_query("SELECT name FROM argus_categories WHERE name = '".$name."' AND category_id != '".$categoryId."'") or die(mysql_error());
                }
                else
                {
                    // if null, then that means that the user is creating a new category
                    // check the other categories if they it the category being added is unique or not
                    $nameQuery = mysql_query("SELECT name FROM argus_categories WHERE name = '".$name."'") or die(mysql_error());
                }
                
                // check the result
                if(mysql_num_rows($nameQuery) > 0)
                {
                    // return a message that the category name has already been registered
                    return "The category '".$name."' has already been registered";
                }
            }
			
			return;
		}
		
		/**
		 * Move Category Method: this moves the category UP or DOWN fixing the positions of the category
		 * Parameter: $categoryId, $action
		 */
		function moveCategory($categoryId, $action)
		{
			// query all enabled categories then fix them in ascending order using the position attribute
			$categoriesQuery = mysql_query("SELECT category_id, position FROM argus_categories WHERE status = 'ENABLED' ORDER BY position ASC") or die(mysql_error());
			
			// search the parameter category ID from the queried categories
			for($i=0; $i < mysql_num_rows($categoriesQuery); $i++)
			{
				// if the category id has been matched and found, set the category for exchanging position
				if(mysql_result($categoriesQuery,$i,"category_id") == $categoryId)
				{
					// set the category id and the position of the category that is on the top of the current category
					if($i - 1 >= 0)
					{
						$topCategoryId = mysql_result($categoriesQuery,$i-1,"category_id");
						$topCategoryPosition = mysql_result($categoriesQuery,$i-1,"position");
					}
					
					// set the position of the current category
					$position = mysql_result($categoriesQuery,$i,"position");
					
					// set the category id and the position of the category tha is on the bottom of the current category
					if($i+1 < mysql_num_rows($categoriesQuery))
					{
						$bottomCategoryId = mysql_result($categoriesQuery,$i+1,"category_id");
						$bottomCategoryPosition = mysql_result($categoriesQuery,$i+1,"position");
					}
				}
			}
			
			// check the action which action to perform
			if($action == "MOVEUP")
			{
				// switch the current position of the category with the position of the category that is on the top
				mysql_query("UPDATE argus_categories SET position='".$topCategoryPosition."' WHERE category_id='".$categoryId."' AND status='ENABLED'") or die(mysql_error());
				
				// switch the categorie's position at the top with the current position of the category
				mysql_query("UPDATE argus_categories SET position='".$position."' WHERE category_id='".$topCategoryId."' AND status='ENABLED'") or die(mysql_error());
			} 
			else
			{
				// switch the current position of the category with the position of the category that is on the bottom
				mysql_query("UPDATE argus_categories SET position='".$bottomCategoryPosition."' WHERE category_id='".$categoryId."' AND status='ENABLED'") or die(mysql_error());
				
				// switch the category's position at the bottom with the current position of the category
				mysql_query("UPDATE argus_categories SET position='".$position."' WHERE category_id='".$bottomCategoryId."' AND status='ENABLED'") or die(mysql_error());
			}
			
			return;
		}
		
		/**
		 * Update Positions Method: updates the positions orderings of the enabled categories
		 * Parameter: $positionNumbers[]
		 */
		function updatePositions($positionNumbers)
		{
			// include the position validator class and create a validator
			include("class_libraries/PositionValidator.php");
			$positionValidator = new PositionValidator();
			
			// validate the positions
			$result = $positionValidator -> validatePosition($positionNumbers);
			
			// check the result
			if($result == true)
			{
				// if the validation has passed, update the posiitions
				// query the positions of the categories and arrange them by order by position
				$idQuery = mysql_query("SELECT category_id FROM argus_categories WHERE status = 'ENABLED' ORDER BY position ASC") or die(mysql_error());
				
				// update the positions
				for($i=0; $i<mysql_num_rows($idQuery); $i++)
				{
					mysql_query("UPDATE argus_categories SET position = '".$positionNumbers[$i]."' WHERE category_id='".mysql_result($idQuery,$i,"category_id")."'") or die(mysql_error()); 
				}
			}
			
			return;
		}
		
		/**
		 * Validate Description Method: validates the description of the category if it is correct
		 * Parameters: $description
		 * Return type: string
		 */
		function validateDescription($description)
		{
			// include the description validator class and create a validator which accepts only max char length of 255
			include("class_libraries/DescriptionValidator.php");
			$descriptionValidator = new DescriptionValidator(255);
			
			// validate the description
			$result = $descriptionValidator -> validateDescription($description);
			
			// check the result
			if($result == false)
			{
				// get the error that was commited during validation and return the error
				return $descriptionValidator -> getErrors();
			}
			
			return;
		}
	}
?>