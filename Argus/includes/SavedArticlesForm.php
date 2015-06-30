<?php
	/**
	 * Filename : SavedArticlesForm.php
	 * Description : class file that contains the properties and behavoiour of saved articles
	 * Date Created : December 3,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	SavedArticlesForm($accountId)
	 *	string displayBanner()
	 *	string displayArticles($status, $page)
	 *	string getCategoryName($categoryId)
	 *	void removeArticle($articleId)
	 *	void restoreArticle($articleId)
	 *	boolean saveArticle($articleId, $title, $categoryId, $content)
	 *	boolean createArticle($articleId, $title, $categoryId, $content)
	 *	string validateTitle($title)
     *  string validateContent($content)
	 *	string getErrors()
     *  string viewArticle($articleId)
     *  void deleteArticle($articleId)
     *  void submitArticle($articleId)
     *  string displaySubmitted()
     *  void deleteAllArticles()
     *  string limitText($text)
	 */
	
	class SavedArticlesForm
	{
		var $accountId;
		var $errors;
		
		/**
		 * Constructor method
		 * parameter: accountId
		 */
		function SavedArticlesForm($accountId)
		{
			// set the accountId
			$this -> accountId = $accountId;
			
			return;
		}
		
		/**
		 * Display Banner method: displays the menu and options for managing saved articles
		 * return type: string
		 */
		function displayBanner()
		{
			echo "
            <div class='bg2'>
			    <h2><em>Articles Manager</em></h2>
			    <p align='center'>";
			
			// menus
			echo "
            <a href='articles.php'>Saved</a> . 
			<a href='articles.php?event=deleted'>Deleted</a> . 
			<a href='articlescompose.php'>Compose</a> . 
			<a href='articles.php?event=submitted'>Submitted</a>";
			
			echo "
            </p>
			</div>";
			
			return;
		}
		
		/**
		 * Display Articles method: displays articles depending on which article is to be displayed
		 * Parameters: $status, $page
		 * Return type: string
		 */
		function displayArticles($status, $page)
		{
            // query the total number of articles of the account
            $articlesCountQuery = mysql_query("SELECT saved_article_id FROM argus_saved_articles WHERE account_id = '".$this -> accountId."' AND status='".$status."'") or die(mysql_error());
            $totalArticlesCount = mysql_num_rows($articlesCountQuery);
            
            // set the number of limit on how many articles are to be showed per page
            $limit = 15;
            
            // compute the total number of page
            $numberOfPages = ceil($totalArticlesCount/$limit);
            
            // check the page if it is empty or not and check if the page is a digit or not
            if(empty($page) && !ctype_digit($page))
            {
                // set the default page which is 1
                $page = 1;
            }
            
            // compute the limit value
            $limitValue = $page * $limit - ($limit);
            
			// query the articles that is to be displayed
			$articlesQuery = mysql_query("SELECT saved_article_id, category_id, title, date_created, date_modified, times_submitted FROM argus_saved_articles WHERE account_id = '".$this -> accountId."' AND status = '".$status."' ORDER BY date_modified DESC LIMIT ".$limitValue.",".$limit."") or die(mysql_error());
			
			// set the title of the form
            echo "
            <h3>".ucfirst(strtolower($status))."</h3>
			<div class='bg1' id='tablePanel'>";
			
			// check if there are information queried from the database
			if(mysql_num_rows($articlesQuery) == 0)
			{
				// if none, notify the user that the information requested is unavailable
				echo "<p><h3 align='center'>There are no ".$status." articles</h3></p>";
			}
			else
			{
                // inlcude the TOOL TIP ajax and create a tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                // include the checkbox funtions where check box are allowed to be selected/unselected all
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
				// set the form and table where to display the queried articles
				echo "
                <form id='form_id' method='post' action='".$_SERVER['PHP_SELF']."?event=".strtolower($status)."'>
				<table width='100%'>
				<tr>
				<th><input type='checkbox' onClick='toggleCheckBoxes(\"articleIds\")'></th>
				<th>Title</th>
				<th>Category</th>
				<th>Date Created</th>
				<th>Date Modified</th>
                <th>Submits</th>
				<th>Action</th>
				</tr>";
				
				// display the articles
				$color = true;
				
				for($i=0; $i<mysql_num_rows($articlesQuery); $i++)
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
					
					// set the attributes
					$articleId = mysql_result($articlesQuery,$i,"saved_article_id");
                    $title = $this -> limitTitle(stripslashes(mysql_result($articlesQuery,$i,"title")));
					$categoryName = $this -> getCategoryName(mysql_result($articlesQuery,$i,"category_id"));
					$dateCreated = date("m/d/y", mysql_result($articlesQuery,$i,"date_created"));
					$dateModified = date("m/d/y", mysql_result($articlesQuery,$i,"date_modified"));
                    $timesSubmitted = mysql_result($articlesQuery,$i,"times_submitted");
                    
					// display the attributes
					echo "
                    <td><input type='checkbox' name='articleIds[]' value='".$articleId."'></td>
					<td><a href='articles.php?event=view&article=".$articleId."'>".$title."</a></td>
					<td>".$categoryName."</td>
					<td>".$dateCreated."</td>
					<td>".$dateModified."</td>
                    <td>".$timesSubmitted."</td>
					<td>
                    <a href='articlescompose.php?event=edit&article=".$articleId."' title='Edit'><img src='../miscs/images/Default/article_edit.png'></a> ";
					
					// set the actions
					if($status == "SAVED")
					{
						// actions for SAVED articles
						echo "
                        <a href='articles.php?event=".strtolower($status)."&action=submit&article=".$articleId."' title='Submit'><img src='../miscs/images/Default/article_submit.png'></a> 
						<a href='articles.php?event=".strtolower($status)."&action=remove&article=".$articleId."' title='Remove'><img src='../miscs/images/Default/article_trash.png'></a>";
					}
					else
					{
						// actions for DELETED articles
						echo "
                        <a href='articles.php?event=".strtolower($status)."&action=restore&article=".$articleId."' title='Restore'><img src='../miscs/images/Default/article_restore.png'></a> 
						<a href='articles.php?event=".strtolower($status)."&action=delete&article=".$articleId."' title='Delete'><img src='../miscs/images/Default/article_delete.png'></a>";
					}
					
					echo "</td>";
					echo "</tr>";
				}
				
				echo "</table>";
				
				// set the buttons for managing the articles
				echo "<table width='100%'>";
				echo "<tr><td>";
				
				if($status == "SAVED")
				{
					// buttons for SAVED articles
					echo "<input type='submit' id='submit1' value='Remove' name='remove'> ";
					echo "<input type='submit' id='submit1' value='Submit' name='submit'>";
				}
				else
				{
					// buttons for DELETED articles
					echo "<input type='submit' id='submit1' value='Restore' name='restore'> ";
					echo "<input type='submit' id='submit1' value='Delete' name='delete'> ";
					echo "<input type='submit' id='submit1' value='Delete all' name='deleteAll'>";
				}
				
				echo "</td>";
                echo "<td align='right'>";
                
                // display the previous page link
                if($page > 1)
                {
                    echo "<a href='articles.php?event=".$_GET["event"]."&page=".($page-1)."'><img src='../miscs/images/Default/previous.png' title='Previous'></a> ";
                }
                
                // display the next page link
                if($page < $numberOfPages)
                {
                    echo "<a href='articles.php?event=".$_GET["event"]."&page=".($page+1)."'><img src='../miscs/images/Default/next.png' title='Next'></a>";
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
		 * Get Category Name method: get's the name of the category from the database
		 * Parameter: $categoryId
		 * return type: string
		 */
		function getCategoryName($categoryId)
		{
			// include the category name retriever class and retrieve the name of the category
			require_once("class_libraries/NameRetriever.php");
			$nameRetriever = new NameRetriever("category_id");
			
			// retrieve the name and return the name that was received
			return $nameRetriever -> getName($categoryId);
		}
		
		/**
		 * Remove Article method: removes a saved article sending the article to the deleted sections
		 * Parameter: $articleId
		 */
		function removeArticle($articleId)
		{
			// remove the article from the saved section
			mysql_query("UPDATE argus_saved_articles SET status = 'DELETED' WHERE saved_article_id = '".$articleId."' AND status = 'SAVED' AND account_id = '".$this -> accountId."'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Restore Article method: brings back the deleted article to the saved section
		 * Parameter: $articlId
		 */
		function restoreArticle($articleId)
		{
			// restore the article to the saved section
			mysql_query("UPDATE argus_saved_articles SET status = 'SAVED' WHERE saved_article_id = '".$articleId."' AND status = 'DELETED' AND account_id = '".$this -> accountId."'") or die(mysql_error());
			
			return;
		}
		
		/**
		 * Save Article method: saves an article into the database
		 * Parameter: $articleId, $title, $categoryId, $content
		 * return type: boolean
		 */
		function saveArticle($articleId, $title, $categoryId, $content)
		{
            // escape the characters that needs escaping to avoid sql injection
            $title = mysql_escape_string($title);
            
			// validate title
			$titleError = $this -> validateTitle($title);
            
            // validate content
            $contentError = $this -> validateContent($content);
            
            // check if the validation has passed
			if($titleError == null && $contentError == null)
			{
                // make sure that the characte "'" will be ignored when stored in the database to maintain the integrity of the database
                // the mysql_escape_string function will add a SLASH on a word that has the character "'".
                // Example: Silver's will be transformed into a word Silver\'s
                $title = mysql_escape_string($title);
                $content = mysql_escape_string($content);
                
				// update the article in the database
				mysql_query("UPDATE argus_saved_articles SET title = '".$title."', category_id = '".$categoryId."', content = '".$content."', date_modified = '".time()."' WHERE saved_article_id = '".$articleId."' AND account_id = '".$this -> accountId."'") or die(mysql_error());
				
				// return successful save
				return true;
			}
			else
			{
				// if validation is not successful, set the error messages
				$this -> errors = array("title" => $titleError, "content" => $contentError);
				
				// return unsuccessful save
				return false;
			}
			
			return;
		}
        
        /**
         * Validate Content Method: validates the content of the article
         * Parameter: $content
         * Return Type: string
         */
        function validateContent($content)
        {
            // include the content validator class and validate the content
            include("class_libraries/ArticleContentValidator.php");
            $contentValidator = new ArticleContentValidator();
            
            // validate the content
            $result = $contentValidator -> validateContent($content);
            
            // check the result
            if($result == false)
            {
                // if empty, return a message that the content is empty
                return "Please provide a content for the article";
            }
            
            return;
        }
		
		/**
		 * Create Article method: creates a new article
		 * Parameters: $articleId, $title, $categoryId, $content
		 * Return Type: boolean
		 */
		function createArticle($articleId, $title, $categoryId, $content)
		{            
			// validate the title
			$titleError = $this -> validateTitle($title);
            
            // validate the content
            $contentError = $this -> validateContent($content);
			
			// check the validation
			if($titleError == null && $contentError == null)
			{
                // make sure that the characte "'" will be ignored when stored in the database to maintain the integrity of the database
                // the mysql_escape_string function will add a SLASH on a word that has the character "'".
                // Example: Silver's will be transformed into a word Silver\'s
                $title = mysql_escape_string($title);
                $content = mysql_escape_string($content);
                
				// if validation has passed, create the new article
				mysql_query("INSERT INTO argus_saved_articles(saved_article_id, account_id, category_id, title, content, date_created, date_modified, status, times_submitted)
							 VALUES ('".$articleId."', '".$this -> accountId."', '".$categoryId."', '".$title."', '".$content."', '".time()."', '".time()."', 'SAVED','0')") or die(mysql_error());
			
				// return successful creation of article
				return true;
			}
			else
			{
				// set the error
				$this -> errors = array("title" => $titleError, "content" => $contentError);
				
				// return un successful creation of article
				return false;
			}
			
			return;
		}
		
		/**
		 * Validate Title method: validates the title if it has a valid length
		 * Parameter: $title
		 * return type: string
		 */
		function validateTitle($title)
		{
			// include the title validator and create a validator that will validate the title with 5-100 characters long
			include("class_libraries/TitleValidator.php");
			$titleValidator = new TitleValidator(5,100);
			
			// validate the title
			$result = $titleValidator -> validateTitle($title);
			
			// check the result
			if($result == false)
			{
				// get the error and return the result
				return $titleValidator -> getErrors();
			}
			
			return;
		}
		
		/**
		 * Get Errors method: returns the errors that was committed during the saving of article
		 * Return type: string
		 */
		function getErrors()
		{
			// return the errors
			return $this -> errors;
		}
        
        /**
         * View Article Method: views a specific article
         * Parameter: $articleId
         * Return type: string
         */
        function viewArticle($articleId)
        {
            // query the article from the database
            $articleQuery = mysql_query("SELECT category_id, title, content, date_created, date_modified, status, times_submitted FROM argus_saved_articles WHERE saved_article_id = '".$articleId."'") or die(mysql_error());
            
            // check if the article was queried from the database
            if(mysql_num_rows($articleQuery) > 0)
            {
                // set the attributes
                $categoryName = $this -> getCategoryName(mysql_result($articleQuery,0,"category_id"));
                $title = stripslashes(mysql_result($articleQuery,0,"title"));
                $content = mysql_result($articleQuery,0,"content");
                $dateCreated = date("F d, Y", mysql_result($articleQuery,0,"date_created"));
                $dateModified = date("F d, Y", mysql_result($articleQuery,0,"date_modified"));
                $status = mysql_result($articleQuery,0,"status");
                $timesSubmitted = mysql_result($articleQuery,0,"times_submitted");
                
                // set the title for SAVED article
                echo "<h3><a href='articles.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo; ".$title."</h3>";
                
                echo "<div class='bg1'>";
                
                // display the article
                echo "
                <p>Article Information</p>
                <p id='box'>
                Title : ".$title."<br>
                Category : ".$categoryName."<br>
                Date Created : ".$dateCreated."<br>
                Date Modified : ".$dateModified."<br>
                Status : ".$status."<br>
                Times Submitted : ".$timesSubmitted."<br>
                </p>";
                
                // display the buttons for managing the article that is displayed
                echo "
                <p align='right'>
                <a href='articlescompose.php?event=edit&article=".$articleId."'><input type='submit' id='submit1' value='edit'></a> ";
                
                if($status == "SAVED")
                {
                    // buttons for enabled status
                    echo "
                    <a href='articles.php?event=".strtolower($status)."&action=remove&article=".$articleId."'><input type='submit' id='submit1' value='remove'></a> 
                    <a href='articles.php?event=".strtolower($status)."&action=submit&article=".$articleId."'><input type='submit' id='submit1' value='submit'></a>";
                }
                else
                {
                    // buttons for DELETED status
                    echo "
                    <a href='articles.php?event=".strtolower($status)."&action=restore&article=".$articleId."'><input type='submit' id='submit1' value='restore'></a> 
                    <a href='articles.php?event=".strtolower($status)."&action=delete&article=".$articleId."'><input type='submit' id='submit1' value='delete'></a> ";
                }
                
                echo "
                </p>
                <div>".$content."</div>
                </div>";
            }
            
            return;
        }
        
        /**
        * Delete Article Method: deletes an article
        * Parameter: $articleId
        */
        function deleteArticle($articleId)
        {
            // delete the article
            mysql_query("DELETE FROM argus_saved_articles WHERE saved_article_id = '".$articleId."' AND account_id = '".$this -> accountId."'") or die(mysql_error());
        
            return;
        }
        
        /**
         * Submit Article Method: submits the article to the administrator for approval
         * Parameter: $articleId
         */
        function submitArticle($articleId)
        {
            // check if the administrator allows submission of articles
            $submitArticleQuery = mysql_query("SELECT content FROM argus_infos WHERE name='submit_article'") or die(mysql_error());
            $submitArticle = mysql_result($submitArticleQuery,0,"content");
            
            if($submitArticle == "false")
            {
                // return and never process anything
                return;
            }
            
            // query the number of limit that an article is allowed to be submitted
            $submitLimitQuery = mysql_query("SELECT content FROM argus_infos WHERE name='submit_limit'") or die(mysql_error());
            $submitLimit = mysql_result($submitLimitQuery,0,"content");
            
            // query the article information from the database and transfer it to another table
            // in the database with addition information
            $articleQuery = mysql_query("SELECT category_id, title, content, times_submitted FROM argus_saved_articles WHERE saved_article_id = '".$articleId."' AND account_id = '".$this -> accountId."'") or die(mysql_error());
            
            // check if the article exist in the database
            if(mysql_num_rows($articleQuery) > 0)
            {
                // set the information of the article
                $categoryId = mysql_result($articleQuery,0,"category_id");
                $title = mysql_escape_string(mysql_result($articleQuery,0,"title"));
                $content = mysql_result($articleQuery,0,"content");
                $timesSubmitted = mysql_result($articleQuery,0,"times_submitted");
                
                // check if the article has reached it's submit limit
                if($timesSubmitted >= $submitLimit)
                {
                    // return and do not process anything
                    return;
                }
                
                // check if there are images inside the content... the images will be copied to another folder so
                // that when the user deletes the image, the article is not affected.
                preg_match_all('/<img.*?src\s*=\s*["\'](.+?)["\']/im', $content, $imagePaths);
                
                for($i=0; $i<count($imagePaths[1]); $i++)
                {
                    // image path syntax that was extracted
                    // e.g. ../images/client/123.jpg
                    // we try to get the 123.jpg only
                    $explodedImagePath = explode("/", $imagePaths[1][$i]);
                    
                    // syntax for exploded image path that was extracted into an array
                    // [..] [images] [client] [123.jpg]
                    //  0      1        2         3
                    // Get only the 123.jpg which will be used
                    $imageName = $explodedImagePath[count($explodedImagePath) - 1];
                    
                    // get the extention name of the image
                    $explodedImageName = explode(".",$imageName);
                    $extentionName = $explodedImageName[count($explodedImageName)-1];
                    
                    // create a random number which will be the new name of the image
                    do
                    {
                        $randomNumber = rand();
                        $newImageName = $randomNumber.".".$extentionName;
                        
                        // keep creating a new image name until the name is unique
                    } while(file_exists("../images/server/".$newImageName));
                    
                    $oldPath = "../images/client/".$imageName;
                    $newPath = "../images/server/".$newImageName;
                    
                    // copy the image and transfer it to another folder
                    copy($oldPath, $newPath);
                    
                    // after copying the image, replace the content path from old path to new path
                    $content = str_replace($oldPath, $newPath, $content);
                }
                
                // also set the isssue to 0
                // by default, submitted articles have no issues and articles with no issues are flagged with 0
                $issueId = 0;
                
                // also set the publish type to default which is NONE
                $publishType = "NONE";
                
                // update the times submitted of the article
                mysql_query("UPDATE argus_saved_articles SET times_submitted='".($timesSubmitted + 1)."' WHERE saved_article_id = '".$articleId."'");
                
                $content = mysql_escape_string($content);
                
                // insert the article information into another table in a PENDING STATUS
                mysql_query("INSERT INTO argus_articles(account_id, category_id, issue_id, title, content, date_submitted, status, publish_type)
                             VALUES('".$this -> accountId."','".$categoryId."','".$issueId."','".$title."','".$content."','".time()."','PENDING','".$publishType."')") or die(mysql_error());;
            }
            
            return;
        }
        
        /**
         * Display Submitted Method: displays the articles that were submitted
         * Return type: string
         */
        function displaySubmitted()
        {
            // query all the submitted articles of the user from the database
            $articlesQuery = mysql_query("SELECT title, category_id, date_submitted, status FROM argus_articles WHERE account_id = '".$this -> accountId."' ORDER BY date_submitted DESC") or die(mysql_error());
            
            // query the number of submit limits
            $submitLimitQuery = mysql_query("SELECT content FROM argus_infos WHERE name='submit_limit'") or die(mysql_error());
            $submitLimit = mysql_result($submitLimitQuery,0,"content");
            
            // set the title of the form
            echo "
            <h3>Submitted</h3>
            <div class='bg1' id='tablePanel'>";
            
            // check if there are submitted articles
            if(mysql_num_rows($articlesQuery) == 0)
            {
                // notify the user that there are no submitted articles
                echo "<p><h3 align='center'>There are no SUBMITTED articles</h3></p>";
            }
            else
            {
                // create the table
                echo "
                <table width='100%'>
                <tr>
                <th align='center'>Title</th>
                <th>Category</th>
                <th>Date Submitted</th>
                <th>Status</th>
                </tr>";
                
                // display the articles
                $color = true;
                
                for($i=0; $i<mysql_num_rows($articlesQuery); $i++)
                {
                    // display the articles in an alternate color rows
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
                    $title = $this -> limitTitle(stripslashes(mysql_result($articlesQuery,$i,"title")));
                    $categoryName = $this -> getCategoryName(mysql_result($articlesQuery,$i,"category_id"));
                    $dateSubmitted = date("m/d/y", mysql_result($articlesQuery,$i,"date_submitted"));
                    $status = mysql_result($articlesQuery,$i,"status");
                    
                    // display the attributes
                    echo "
                    <td align='center'>".$title."</td>
                    <td>".$categoryName."</td>
                    <td>".$dateSubmitted."</td>
                    <td>".$status."</td>
                    </tr>";
                    }
            
                echo "</table>";
            }
            
            // display the allowed number of times an article can be submitted
            echo "<p id='box' align='center'>Each articles are only allowed to be submitted ".$submitLimit." times</p>";
            
            echo "</div>";
            
            return;
        }
        
        /**
         * Delete All Article method: deletes all removed articles permanently out of the database
         */
        function deleteAllArticles()
        {
            // delete all trash articles of the user
            mysql_query("DELETE FROM argus_saved_articles WHERE account_id = '".$this -> accountId."' AND status='DELETED'") or die(mysql_error());
            
            return;
        }
    
        /**
         * Limit Title method: limits the title of the article
         * Parameter: $title
         * Return Type: string
         */
        function limitTitle($title)
        {
            // include the text limiter class and limit the title to 5 words only
            require_once("class_libraries/TextLimiter.php");
            $textLimiter = new TextLimiter();
            
            $title = $textLimiter -> limitText($title, 4);
            
            // return the truncated title
            return $title;
        }
	}
?>