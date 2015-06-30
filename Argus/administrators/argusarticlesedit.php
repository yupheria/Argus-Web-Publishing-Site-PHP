<?php
	/**
	 * Filename : argusarticles edit.php
	 * Description : page for editing submitted articles
	 * Date Created : December 3,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the articles form
	require_once("../includes/ArticlesForm.php");
	$form = new ArticlesForm();
	
	/**
	 * BUTTON TRIGGER EVENTS:
	 *  save
	 */
    
        // SAVE button
        if(isset($_POST["save"]))
        {
            // get all the inputs from the user
            $title = $_POST["title"];
            $categoryId = $_POST["categoryId"];
            $issueId = $_POST["issueId"];
            $intro = $_POST["intro"];
            $content = $_POST["content"];
            $publishType = $_POST["publishType"];
            
            // save the article
            $result = $form -> saveArticle($_GET["article"], $title, $categoryId, $issueId, $intro, $content, $publishType);
            
            // check the result
            if($result == true)
            {
                // set a success message of save which will be displayed below
                $successMessage = "Saved";
            }
            else
            {
                // get the errors that was committed which will be displayed below
                $errors = $form -> getErrors();
            }
        }
	
	/**
	 * END OF BUTTON TRIGGER EVENTS
	 */
    
    // check if the Article ID has been set in the URL
    // this page can only be accessed if there is an article id in the URL
    if(isset($_GET["article"]))
    {
        // query the article from the database
        $articleQuery = mysql_query("SELECT account_id, category_id, issue_id, title, intro, content, date_submitted, date_modified, status, publish_type FROM argus_articles WHERE article_id = '".$_GET["article"]."' AND status = 'APPROVED'") or die(mysql_error());
        
        // check if there is an article queried from the database
        if(mysql_num_rows($articleQuery) > 0)
        {
            // set the attributes
            $authorName = $form -> getAuthorName(mysql_result($articleQuery,0,"account_id"));
            $categoryId = mysql_result($articleQuery,0,"category_id");
            $categoryName = $form -> getCategoryName($categoryId);
            $issueId = mysql_result($articleQuery,0,"issue_id");
            $issueName = $form -> getIssueName($issueId);
            $title = mysql_result($articleQuery,0,"title");
            $intro = mysql_result($articleQuery,0,"intro");
            $content = mysql_result($articleQuery,0,"content");
            $dateSubmitted = date("F d, Y", mysql_result($articleQuery,0,"date_submitted"));
            $dateModified = $form -> getDateModified(mysql_result($articleQuery,0,"date_modified"));
            $status = mysql_result($articleQuery,0,"status");
            $publishType = mysql_result($articleQuery,0,"publish_type");
        }
        else
        {
            // if no article was queried, redirect the user to the deafult page
            header("Location: index.php");
        }
    }
    else
    {
        // if the article id has not been set, redirect the user to the default page
        header("Location: index.php");
    }
	
	// display the header
	$page -> displayHeader();
	
	// display the banner
	$page -> displayBanner();
?>
<!-- page content -->
<div id='content'>
	<!-- right side bar: contains the tool bars and features of each account -->
	<?php
        echo $page -> displayDivCode("RIGHT");
        
		// display the tools
		$page -> displayTools();
        
        echo "</div>";
	?>
	<!-- left side column: contains sub options and articles and where manipulation of tools occurs -->
	<?php
        $page -> displayDivCode("LEFT");
        
		// display the banner
		$form -> displayBanner();
        
        // set the title and menu
        // display the appropriate title and menu of the page
        if($issueId == 0)
        {
            echo "<h3><a href='argusarticles.php?event=approved'>Approved</a> &raquo ".$title."</h3>";
        }
        else
        {
            echo "<h3><a href='argusarticles.php?event=issues'>Issues</a> &raquo; <a href='argusarticles.php?event=viewissue&issue=".$issueId."'>".$issueName."</a> &raquo; ".$title."</h3>";
        }
    ?>
    <div class='bg1'>
        <?php
            // display the error that was committed during the saving of article
            if(isset($_POST["save"]) && $result == false)
            {
                echo "<p><font color='red'>";
                
                // print the errors for title
                if($errors["title"] != null)
                {
                    echo $errors["title"]."<br>";
                }
                
                echo "</font></p>";
            }
            // display successful save of article
            else
            {
                echo "<p align='center'><font color='green'>".$successMessage."</font></p>";
            }
        ?>
        <form method='post' action='<?php echo $_SERVER["PHP_SELF"]."?article=".$_GET["article"] ?>'>
        <?php
            // display the attribtutes that was queried from the database
            echo "
            <p>Article information</p>
            <p id='box'>
            Title : ".$title."<br>
            Category : ".$categoryName."<br>
            Issue : ".$issueName."<br>
            Author : ".$authorName."<br>
            Date Submitted : ".$dateSubmitted."<br>
            Date Modified : ".$dateModified."<br>
            Publish Type : ".$publishType."<br>
            Status : ".$status."<br>
            </p>";
        ?>
        <p id='box'>
            <b>Title</b><br>
            <input type='text' id='textbox' value='<?php echo stripslashes($title) ?>' name='title'>
        </p>
        <p id='box'>
            <b>Category</b><br>
            <select name='categoryId' id='textbox'>
            <?php
                // query all categories from the database
                $categoriesQuery = mysql_query("SELECT category_id, name FROM argus_categories") or die(mysql_error());
                
                // display the categories
                for($i=0; $i < mysql_num_rows($categoriesQuery); $i++)
                {
                    // set the attributes
                    $queriedCategoryId = mysql_result($categoriesQuery,$i,"category_id");
                    $queriedName = mysql_result($categoriesQuery,$i,"name");
                    
                    if($categoryId == $queriedCategoryId)
                    {
                        // set the category as selected
                        echo "<option value='".$queriedCategoryId."' selected='selected'>".$queriedName."</option>";
                    }
                    else
                    {
                        echo "<option value='".$queriedCategoryId."'>".$queriedName."</option>";
                    }
                }
            ?>
            </select>
        </p>
        <p id='box'>
            <b>Issue</b><br>
            <select name='issueId' id='textbox'>
            <option value='0'></option>
                <?php
                    // query all available issue id from the database
                    $issuesQuery = mysql_query("SELECT issue_id, name FROM argus_issues WHERE status = 'ENABLED'") or die(mysql_error());
                    
                    // display all the issues that are available
                    for($i=0; $i < mysql_num_rows($issuesQuery); $i++)
                    {
                        $queriedIssueId = mysql_result($issuesQuery,$i,"issue_id");
                        $queriedName = mysql_result($issuesQuery,$i,"name");
                        
                        // check if the they have they same issue id, then set the issue name as selected
                        if($issueId == $queriedIssueId)
                        {
                            echo "<option value='".$queriedIssueId."' selected='selected'>".$queriedName."</option>";   
                        }
                        else
                        {
                            echo "<option value='".$queriedIssueId."'>".$queriedName."</option>";
                        }
                    }
                ?>
            </select>
        </p>
        <p id='box'>
            <b>Publish Type</b><br>
            <select name='publishType' id='textbox'>
                <option value='NONE'>None</option>
                <?php
                    // set the publish type
                    if($publishType == "MAIN")
                    {
                        // set main as selected
                        echo "<option value='MAIN' selected='selected'>Main</option>";
                    }
                    else
                    {
                        echo "<option value='MAIN'>Main</option>";
                    }
                    
                    if($publishType == "FEATURED")
                    {
                        echo "<option value='FEATURED' selected='selected'>Featured</option>";
                    }
                    else
                    {
                        echo "<option value='FEATURED'>Featured</option>";
                    }
                ?>
                </option>
            </select>
        </p>
        <p>
            <b>Intro</b><br>
            <?php
                // include the text editor class and set up the javascript code
                include("../includes/ajax_libraries/TextEditor.php");
                $textEditor = new TextEditor();
                $textEditor -> setupTextEditor("ADVANCED");
            ?>
            <textarea name='intro' style='width:100%; height:200px'>
                <?php
                    // TINY MCE editor automatically creates a slashes on words that has the characte "'" to make
                    // sure the integrity of the database is still there.
                    // Example: the word Silver's will be transformed into Silver\'s.
                    // But when displaying the correct word, we should remove the SLASH and we make use of the                
                    echo stripslashes($intro);
                ?>
            </textarea>
        </p>
        <p>
            <b>Content</b><br>
            <textarea name='content' style='width:100%; height:400px'>
                <?php
                    // stripslash the content
                    echo stripslashes($content);
                ?>
            </textarea>
        </p>
        <p align='center'>
            <input type='submit' id='submit2' value='save' name='save'>
        </p>
    </form>
    </div>
	</div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>