<?php
	/**
	 * Filename : articlescompose.php
	 * Description : page for creating articles
	 * Date Created : December 3,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the saved articles form
	require_once("../includes/SavedArticlesForm.php");
	$form = new SavedArticlesForm($_COOKIE["argus"]);
    
    // set the article ID
    $articleId = $_GET["article"];
    
    // set the event
    $event = $_GET["event"];
	
	/**
	 * URL EVENTS:
	 *	edit
	 * 	compose
	 */
	
		switch($event)
		{
			case "edit":
				$event = "EDIT";
                // query the attributes of the article that is to be edited
                $articleQuery = mysql_query("SELECT  category_id, title, content, status FROM argus_saved_articles WHERE saved_article_id = '".$articleId."' AND account_id = '".$_COOKIE["argus"]."'") or die(mysql_error());
                
                // check if there is an information of the article queried from the database
                if(mysql_num_rows($articleQuery) > 0)
                {
                    // set the queried attributes which will be displayed below
                    $title = stripslashes(mysql_result($articleQuery,0,"title"));
                    $categoryId = mysql_result($articleQuery,0,"category_id");
                    $content = mysql_result($articleQuery,0,"content");
                    $status = mysql_result($articleQuery,0,"status");
                }
                else
                {
                    // if no information has been found set the event back to default
                    $event = "COMPOSE";
                }
                
                
				break;
			
			default:
				$event = "COMPOSE";
				
				break;
		}
	
	/**
	 * END OF URL EVENTS
	 */
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *	create
	 *	save
	 */
	
		// CREATE Button
		if(isset($_POST["create"]))
		{
			// retrieve all the inputs from the user
			$title = $_POST["title"];
			$categoryId = $_POST["category"];
			$content = $_POST["content"];
		
            // import the id generator class
            include("../includes/class_libraries/IdGenerator.php");
            $idGenerator = new IdGenerator();
			
            // create an Article id
			$articleId = $idGenerator -> generateId("saved_article_id");
					
			// create the article
			$result = $form -> createArticle($articleId, $title, $categoryId, $content);
			
			// check the result
			if($result == true)
			{
                // if the article has been succcessfully created
                // switch the EVENT to EDIT MODE
                $event = "EDIT";
                
                // set the status of the article so that the TITLE and the form is going to be appropriately displayed
                $status = "SAVED";
                
                // set the success message
                $successMessage = "Saved";
			}
			else
			{
				// get the errors which will be displayed below
				$errors = $form -> getErrors();
			}
		}
        
        // SAVE button
        if(isset($_POST["save"]))
        {
            // get all the inputs from the user
            $title = stripslashes($_POST["title"]);
            $categoryId = $_POST["category"];
            $content = $_POST["content"];
            
            // update the article
            $result = $form -> saveArticle($_GET["article"], $title, $categoryId, $content);
            
            // check the result
            if($result == true)
            {
                // set the success message
                $successMessage = "Saved";
            }
            else
            {
                // get the errors which will be displayed below
                $errors = $form -> getErrors();
            }
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
    
		// display the tools for ADMINISTRATORS
		$page -> displayTools();
        
        echo "</div>";
	?>
	<!-- left side column: contains sub options and articles and where manipulation of tools occurs -->
	<?php
        $page -> displayDivCode("LEFT");
        
		// display the banner
		$form -> displayBanner();
		
        // set the title of the form
        if(isset($status))
        {
            // title for SAVED and DELETED
            echo "
            <h3><a href='articles.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo; ".$title."</h3>
            <form method='post' action='".$_SERVER['PHP_SELF']."?event=".strtolower($event)."&article=".$articleId."'>";
        }
        else
        {
            // default title and form
            echo "
            <h3>Compose</h3>
            <form method='post' action='".$_SERVER['PHP_SELF']."'>";
        }
        
        echo "<div class='bg1'>";
		
        // display the errors that was committed during the saving of articles
		if((isset($_POST["create"]) || isset($_POST["save"])) && $result == false)
		{
			echo "<p><font color='red'>";
			
			// display error for title
			if($errors["title"] != null)
			{
				echo $errors["title"]."<br>";
			}
            
            // display error for the content
            if($errors["content"] != null)
            {
                echo $errors["content"]."<br>";
            }
			
			echo "</font></p>";
		}
        // display the success messages
        else if((isset($_POST["create"]) || isset($_POST["save"])) && $result == true)
        {
            echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
        }
	?>
        <!-- form tag is defined above -->
			<p>Article Information</p>
			<!-- title -->
			<p id='box'>
				<b>Title</b><br>
				<input type='text' id='textbox' name='title' value='<?php echo $title; ?>'>
			</p>
			<!-- category -->
			<p id='box'>
				<b>Category</b><br>
				<select id='textbox' name='category'>
				<?php
					// query all categories from the database including disabled categories
					$categoriesQuery = mysql_query("SELECT category_id, name FROM argus_categories") or die(mysql_error());
					
					// display the categories
					for($i=0; $i < mysql_num_rows($categoriesQuery); $i++)
					{
						// set the attributes
						$queriedCategoryId = mysql_result($categoriesQuery,$i,"category_id");
						$name = mysql_result($categoriesQuery,$i,"name");
						
						if($categoryId == $queriedCategoryId)
						{
							// set the category as selected
							echo "<option value='".$queriedCategoryId."' selected='selected'>".$name."</option>";
						}
						else
						{
							echo "<option value='".$queriedCategoryId."'>".$name."</option>";
						}
					}
				?>
				</select>
			</p>
			<!-- content -->
			<p>
                <?php
                    // include the text editor class and set up the javascript code
                    include("../includes/ajax_libraries/TextEditor.php");
                    $textEditor = new TextEditor();
                    $textEditor -> setupTextEditor("ADVANCED");
                ?>
				<b>Content</b><br>
				<textarea name='content' style='width:100%; height:400px'>
                    <?php
                        // TINY MCE editor automatically creates a slashes on words that has the characte "'" to make
                        // sure the integrity of the database is still there.
                        // Example: the word Silver's will be transformed into Silver\'s.
                        // But when displaying the correct word, we should remove the SLASH and we make use of the                
                        echo stripslashes($content);
                    ?>
                </textarea>
			</p>
			<p align='center'>
				<?php
					// set the appropriate button to be displayed
					if($event == "COMPOSE")
					{
						// button for COMPOSE mode
						echo "<input type='submit' id='submit2' value='create' name='create'> ";
					}
					else
					{
						// button for EDIT mode
						echo "<input type='submit' id='submit2' value='save' name='save'> ";
					}
				?>
			</p>
		</form>
	</div>
	</div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>