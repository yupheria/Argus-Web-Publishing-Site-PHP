<?php
	/**
	 * Filename : MainPageForm.php
	 * Description : contains behaviours in showing certain pages in the main page
	 * Date Created : November 28,2007
	 * Author : Argus Team
	 */
	 
	 /**
	  * METHODS SUMMARY:
      * MainPageForm($accountId)
	  *	string displayBanner()
      * string getAuthorName($accountId)
      * displayCategoryArticles($categoryId)
      * displayArticle($articleId)
      * boolean submitComment($articleId, $comment)
      * array getErrors()
      * string displayFrontPageArticles()
      * string setImagePath($content)
      * string displayInfo($infoType)
      * string stripCommentTags($comment)
      * string displayArchivesByYear()
      * string displayArchivesByIssue($year)
      * string displayArchives($year, $issue)
      * string displayAuthorInfo($accountId)
      * string createTickler($content)
      * string displayEvents($day, $month, $year)
      * string displayEventSummary($month, $year)
      * string search
      * boolean validateIntro($intro)
	  */
	 
	 class MainPageForm
	 {
        var $accountId;
        var $errors;
        
        /**
         * Constructor method
         * Parameter: $accountId
         */
        function MainPageForm($accountId)
        {
            // set the account id
            $this -> accountId = $accountId;
            
            return;
        }
        
	 	/**
		 * Display Banner method: displays the banner for the main page
		 */
		function displayBanner()
		{
            // query the current name and description of the issue
            $issueQuery = mysql_query("SELECT name, description FROM argus_issues WHERE status = 'PUBLISHED'") or die(mysql_error());
            $name = mysql_result($issueQuery,0,"name");
            $description = mysql_result($issueQuery,0,"description");
            
            // query the welcome banner
            $welcomeBannerQuery = mysql_query("SELECT content FROM argus_infos WHERE name = 'welcome_banner'") or die(mysql_error());
            $welcomeBanner = mysql_result($welcomeBannerQuery,0,"content");
            
			echo "<div class='bg2'>";
			echo "<h2><em>".$name."</em></h2>";
			echo "<h3>".$description."</h3>";
			echo "<p>".$welcomeBanner."</p>";
            
            // check if the account id has been set
            if($this -> accountId != null)
            {
                // if not null then that means that a user is logged in
                // query the name of the user from the datatabase
                $accountQuery = mysql_query("SELECT name FROM argus_accounts WHERE account_id = '".$this -> accountId."'") or die(mysql_error());
                
                // set the attributes
                $name = mysql_result($accountQuery,0,"name");
                
                // display a welcome message
                echo "<p id='box' align='center'><img src='../miscs/images/Default/author.png' align='top'><b>Welcome ".$name."</b></p>";
            }
            
            echo "</div>";
			
			return;
		}
        
        /**
         * Get Author Name method: returns the author name of the article
         * Parameter: $accountId
         * Return Type: string
         */
        function getAuthorName($accountId)
        {
            // include the Name Retreiver class and create a name retriever for author
            require_once("class_libraries/NameRetriever.php");
            $nameRetriever = new NameRetriever("account_id");
            
            // get the author name and return it
            return $nameRetriever -> getName($accountId);
        }
        
        /**
         * Display Category Articles Method: displays the published articles in a specific chosen category
         * Parameter: $categoryId
         * Return Type: String
         */
        function displayCategoryArticles($categoryId)
        {
            // query the category from the database if it exist
            $categoryNameQuery = mysql_query("SELECT name FROM argus_categories WHERE category_id = '".$categoryId."' AND status = 'ENABLED'") or die(mysql_error());
            
            // check the result
            if(mysql_num_rows($categoryNameQuery) > 0)
            {
                $name = mysql_result($categoryNameQuery,0,"name");
                
                // set the title
                echo "
                <h3>".$name."</h3>
                <div class='bg1'>";
                
                // query all the published articles that are in that category ordering them by there position
                $articlesQuery = mysql_query("SELECT article_id, account_id, title, intro, date_published FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED' ORDER BY position ASC") or die(mysql_error());
            
                // check if the reusult
                if(mysql_num_rows($articlesQuery) == 0)
                {
                    // notify the user that there are no articles in that category
                    echo "<p><h3 align='center'>There are no articles in the ".$name." category</h3></p>";
                }
                else
                {
                    // display all the published articles
                    for($i=0; $i < mysql_num_rows($articlesQuery); $i++)
                    {
                        // set the attributes
                        $articleId = mysql_result($articlesQuery,$i,"article_id");
                        $accountId = mysql_result($articlesQuery,$i,"account_id");
                        $title = mysql_result($articlesQuery,$i,"title");
                        $authorName = $this -> getAuthorName(mysql_result($articlesQuery,$i,"account_id"));
                        $intro = $this -> setImagePath(mysql_result($articlesQuery,$i,"intro"));
                        $datePublished = date("F d, Y", mysql_result($articlesQuery,$i,"date_published"));
                        
                        // validate the intro of the article if it is empty or not
                        $result = $this -> validateIntro($intro);
                        
                        // check the result
                        if($result == false)
                        {
                            // if false, then that means that the intro is empty, if the intro is empty
                            // query the article content from the database
                            $contentQuery = mysql_query("SELECT content FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
                            $content = $this -> setImagePath(mysql_result($contentQuery,0,"content"));
                            
                            // create a tickler out of the content of the article which will become the intro
                            $intro = $this -> createTickler($content, 200);
                        }
                        
                        
                        // count the number of comments in that article
                        $commentsQuery = mysql_query("SELECT comment_id FROM argus_comments WHERE article_id = '".$articleId."' AND status='APPROVED'") or die(mysql_error());
                        $commentsCount = mysql_num_rows($commentsQuery);
                        
                        // display the attributes
                        echo "
                        <div id='article'>
                        <h2>".$title."</h2>
                        <p>".$intro."</p>
                        <p class='post-footer' align='right'>
                        <a href='index.php?event=author&account=".$accountId."' class='author'>".$authorName."</a>
                        <a href='index.php?event=articles&article=".$articleId."' class='readmore'>Read more</a>
                        <a href='index.php?event=articles&article=".$articleId."#comments' class='comments'>Comments (".$commentsCount.")</a>
                        <span class='date'>".$datePublished."</span>
                        </p>
                        </div>";
                    }
                }
                
                echo "</div>";
            }
            
            return;
        }
        
        /**
         * Display Article Method: displays the article
         * Parameter: $articleId
         * Return Type: string
         */
        function displayArticle($articleId)
        {
            // query the article from the database
            $articleQuery = mysql_query("SELECT title, account_id, category_id, content, date_published FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
            
            // check if the article exists from the database
            if(mysql_num_rows($articleQuery) > 0)
            {
                // check if the user has already viewed this page by identifying the IP address
                $ip = $_SERVER["REMOTE_ADDR"];
                $ipQuery = mysql_query("SELECT article_id FROM argus_article_hits WHERE used_ips LIKE '%".$ip."%' AND article_id = '".$articleId."'") or die(mysql_error());
                
                // check the result
                if(mysql_num_rows($ipQuery) == 0)
                {
                    // if 0 then that means that it's the first time for the computer to visit the page
                    // increment the article hits and record the new ip address
                    $articleHitsQuery = mysql_query("SELECT hits,used_ips FROM argus_article_hits WHERE article_id = '".$articleId."'") or die(mysql_error());
                    $articleHits = mysql_result($articleHitsQuery,0,"hits");
                    $articleUsedIps = mysql_result($articleHitsQuery,0,"used_ips");
                    $articleUsedIps .= $ip.";";
                    mysql_query("UPDATE argus_article_hits SET hits = '".($articleHits+1)."', used_ips = '".$articleUsedIps."' WHERE article_id = '".$articleId."'") or die(mysql_error());
                }
                
                // include the script for rating
                include("ajax_libraries/_drawrating.php");
                echo "<script type='text/javascript' language='javascript' src='../miscs/js/rating/behavior.js'></script>";
                echo "<script type='text/javascript' language='javascript' src='../miscs/js/rating/rating.js'></script>";
                
                // set the attributes
                $title = mysql_result($articleQuery,0,"title");
                $accountId = mysql_result($articleQuery,0,"account_id");
                $authorName = $this -> getAuthorName($accountId);
                $categoryId = mysql_result($articleQuery,0,"category_id");
                $categoryNameQuery = mysql_query("SELECT name FROM argus_categories WHERE category_id = '".$categoryId."' AND status = 'ENABLED'") or die(mysql_error());
                $categoryName = mysql_result($categoryNameQuery,0,"name");
                $content = mysql_result($articleQuery,0,"content");
                $datePublished = date("F d, Y", mysql_result($articleQuery,0,"date_published"));
                
                // the image path from the ADMINISTRATOR, CONTRIBUTOR, and MEMBER is different
                // from the image path of the NON-MEMBER. In order to view the images for NON-MEMBER,
                // the image path is to be reconfigured.
                
                // change the image path inside the content
                if($this -> accountId == null)
                {
                    $memberImagePath = "../images";
                    $nonMemberImagePath = "images";
                    $content = str_replace($memberImagePath, $nonMemberImagePath, $content);
                }
                
                // set the title of the page
                echo "
                <h3><a href='index.php?event=categories&category=".$categoryId."'>".$categoryName."</a> &raquo; ".$title."</h3>
                <div class='bg1'>";
                
                // display the article
                echo "
                <div id='article'>
                <h2>".$title."</h2>
                <p>".$content."</p>
                <p class='post-footer' align='right'>
                <a href='index.php?event=author&account=".$accountId."' class='author'>".$authorName."</a>
                <span class='date'>".$datePublished."</span>
                </p>";
                
                // check if the account id has been set to display the CSS properly
                if($this -> accountId != null)
                {
                    // CSS path for MEMBERS, CONTRIBUTORS, AND ADMINISTRATOR
                    echo "<link rel='stylesheet' type='text/css' href='../miscs/js/rating/css/rating.css' />";
                    
                    // display the rating bar using only 5 as the highest
                    echo rating_bar($articleId,5);
                }
                else
                {
                    // CSS path for guests
                    echo "<link rel='stylesheet' type='text/css' href='miscs/js/rating/css/rating.css' />";
                    
                    // display the rating bar BUT they are not allowed to rate
                    echo rating_bar($articleId,5,'static');
                }
                
                echo "</div>";
                echo "</div>";
                
                // set the comments
                // create an ANCHOR tag here so that when the user clicks on the COMMENTS link
                // the user is automatically sees the comments and does not need to scroll down to see the comments
                echo "<a name='comments'></a>";
                echo "<h3>".$title." Comments</h3>";
                echo "<div class='bg1'>";
                
                // Query the comments of the article
                $commentsQuery = mysql_query("SELECT account_id, comment, date_commented FROM argus_comments WHERE article_id = '".$articleId."' AND status = 'APPROVED' ORDER BY date_commented DESC") or die(mysql_error());
                
                // check if there are comments
                if(mysql_num_rows($commentsQuery) == 0)
                {
                    // notify the user that there are no comments for this particular article
                    echo "<p><h3 align='center'>There are no comments posted for this article</h3></p>";
                }
                else
                {   
                    $color = false;
                    
                    // display all the comments of the article
                    for($i=0; $i<mysql_num_rows($commentsQuery); $i++)
                    {
                        // display the comments in an alternate color
                        if($color == true)
                        {
                            echo "<p id='box'>";
                            $color = false;
                        }
                        else
                        {
                            echo "<p id='box1'>";
                            $color = true;
                        }                        
                        
                        // set the attributes
                        $commentator = $this -> getAuthorName(mysql_result($commentsQuery,$i,"account_id"));
                        $dateCommented = date("F d, Y", mysql_result($commentsQuery,$i,"date_commented"));
                        $comment = mysql_result($commentsQuery,$i,"comment");
                        
                        // display the attributes
                        echo "
                        <b>".$commentator."<br>"
                        .$dateCommented."</b><br>";
                        echo $comment;
                        
                        echo "</blockquote>";
                    }
                }
                
                // check the account id
                if($this -> accountId != null)
                {
                    // if account id is not null, then that means that a MEMBER, CONTRIBUTOR, or ADMINSITRATOR has logged in.
                    // display the comment facility box for comments
                    echo "<form method='post' action='".$_SERVER["PHP_SELF"]."?event=articles&article=".$articleId."'>";
                    echo "<p id='box'>";
                    echo "<b>Comment</b><br>";
                    echo "<textarea id='textbox' name='comment'></textarea>";
                    echo "</p>";
                    echo "<p align='center'><input type='submit' id='submit2' value='submit comment' name='submitComment'></p>";
                    echo "</form>";
                }
                
                echo "</div>";
            }
        
            return;
        }
        
        /**
         * Submit Comment Method: submits comments for moderation
         * Parameter: $articleId, $commentId
         * Return Type: boolean
         */
        function submitComment($articleId, $comment)
        {
            // escape the characters that are needed to be escaped to avoid sql injection
            $comment = mysql_escape_string($comment);
            
            // validate the comment
            $commentError = $this -> validateComment($comment);
            
            // check the validation status
            if($commentError == null)
            {
                // before the comment is going to be inserted to the database, remove all unwanted tags of the comment to protect the page
                $comment = $this -> stripCommentTags($comment);
                
                // queue the comments for moderation
                mysql_query("INSERT INTO argus_comments(account_id, article_id, comment, date_commented, status)
                             VALUES ('".$this -> accountId."','".$articleId."','".$comment."','".time()."','PENDING')") or die(mysql_error());   
                
                // return successful submission of comment
                return true;
            }
            else
            {
                // set the errors
                $this -> errors = array("comment" => $commentError);
                
                // return unsuccessful submission of comment
                return false;
            }
            
            return;
        }
        
        /**
         * Validate Comment Method: validates the comment of articles
         * Parameter: $comment
         * Return Type: string
         */
        function validateComment($comment)
        {
            // validate the comment
            if(trim($comment) == null)
            {
                // return an error that the comment is empty
                return "Please provide a comment";
            }
            
            return;
        }
        
        /**
         * Get Errors method: returns the errors that was commmitted during the submission of comment
         * Return type: array
         */
        function getErrors()
        {
            // return the error
            return $this -> errors;
        }
        
        /**
         * Display Front Page articles method: displays the articles that are at FEATURED and MAIN
         * Return Type: String
         */
        function displayFrontPageArticles()
        {
            // query all MAIN articles order them by there position
            $mainArticlesQuery = mysql_query("SELECT article_id, category_id, title, intro, date_published FROM argus_articles WHERE status = 'PUBLISHED' AND publish_type = 'MAIN' ORDER BY publish_position ASC") or die(mysql_error());
            
            // query all FEATURED articles order them by there position
            $featuredArticlesQuery = mysql_query("SELECT article_id, account_id, category_id, title, intro, date_published FROM argus_articles WHERE status = 'PUBLISHED' AND publish_type = 'FEATURED' ORDER BY publish_position ASC") or die(mysql_error());
            
            // query 5 Most rated articles order them by there position
            $topRatedArticlesQuery = mysql_query("SELECT article_id, total_votes FROM argus_article_ratings WHERE total_value != '0' ORDER BY total_votes DESC LIMIT 5") or die(mysql_error());
            
            // query 5 Most viewed articles order them by there position
            $topViewedArticlesQuery = mysql_query("SELECT article_id, hits FROM argus_article_hits WHERE hits != '0' ORDER BY hits DESC LIMIT 5") or die(mysql_error());
            
            // check if there are main articles
            if(mysql_num_rows($mainArticlesQuery) > 0)
            {
                // set the attributes
                // the main article that is to be displayed on the front page are articles that are positioned at the top
                $articleId = mysql_result($mainArticlesQuery,0,"article_id");
                $categoryId = mysql_result($mainArticlesQuery,0,"category_id");
                $categoryName = $this -> getCategoryName($categoryId);
                $title = mysql_result($mainArticlesQuery,0,"title");
                $intro = $this -> setImagePath(mysql_result($mainArticlesQuery,0,"intro"));
                $datePublished = date("F d, Y", mysql_result($mainArticlesQuery,0,"date_published"));
                
                // check the intro of the article if empty or not
                $result = $this -> validateIntro($intro);
                
                // check the result
                if($result == false)
                {
                    // if false, then that means that the intro is blank
                    // query the full content of the article from the database
                    $contentQuery = mysql_query("SELECT content FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
                    $content = $this -> setImagePath(mysql_result($contentQuery,0,"content"));
                    
                    // if the intro is blank create a tickler which will be the intro
                    $intro = $this -> createTickler($content, 200);
                }
                
                // display the main article that is found at the most top position
                echo "<h3>Headline</h3>";
                echo "<div class='bg2'>";
                echo "<h2><em>".$title."</em></h2>";
                echo "<h3>".$datePublished."</h3>";
                echo "<div>".$intro."</div>";
                
                // count the number of comments in that article
                $commentsQuery = mysql_query("SELECT comment_id FROM argus_comments WHERE article_id = '".$articleId."' AND status='APPROVED'") or die(mysql_error());
                $commentsCount = mysql_num_rows($commentsQuery);
                
                // display the other main articles but only the title will be showed
                echo "<ul>";
                
                for($i=1; $i<mysql_num_rows($mainArticlesQuery); $i++)
                {
                    $articleId = mysql_result($mainArticlesQuery,$i,"article_id");
                    $title = mysql_result($mainArticlesQuery,$i,"title");
                    echo "<li><a href='index.php?event=articles&article=".$articleId."'>".$title."</a></li>";
                }
                
                echo "</ul>";
                
                // display the attributes
                echo "
                <div id='article'>
                <p class='post-footer' align='right'>
                <a href='index.php?event=articles&article=".$articleId."' class='readmore'>Read full article</a>
                <a href='index.php?event=articles&article=".$articleId."#comments' class='comments'>Comments (".$commentsCount.")</a>
                </p>
                </div>";
                echo "</div>";
            }
            
            // check if there are featured articles
            if(mysql_num_rows($featuredArticlesQuery) > 0)
            {
                // display all featured articles
                echo "<h3>Featured</h3>";
                echo "<div class='bg1'>";
                
                // require the article content validator class
                require_once("class_libraries/ArticleContentValidator.php");
                $contentValidator = new ArticleContentValidator();
                
                for($i=0; $i<mysql_num_rows($featuredArticlesQuery); $i++)
                {
                    // set the attributes
                    $articleId = mysql_result($featuredArticlesQuery,$i,"article_id");
                    $title = mysql_result($featuredArticlesQuery,$i,"title");
                    $intro = $this -> setImagePath(mysql_result($featuredArticlesQuery,$i,"intro"));
                    $datePublished = date("F d, Y",mysql_result($featuredArticlesQuery,$i,"date_published"));
                    $accountId = mysql_result($featuredArticlesQuery,$i,"account_id");
                    $authorName = $this -> getAuthorName($accountId);
                    $categoryName = $this -> getCategoryName(mysql_result($featuredArticlesQuery,$i,"category_id"));
                    
                    // check if the intro is blank or not
                    $result = $this -> validateIntro($intro);
                    
                    // check the result
                    if($result == false)
                    {
                        // if false, then that means that the intro is empty, if the intro is empty
                        // query the article content from the database
                        $contentQuery = mysql_query("SELECT content FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
                        $content = $this -> setImagePath(mysql_result($contentQuery,0,"content"));
                        
                        // create a tickler out of the content of the article which will become the intro                     
                        $intro = $this -> createTickler($content, 100);
                    }
                    
                    // count the number of comments in that article
                    $commentsQuery = mysql_query("SELECT comment_id FROM argus_comments WHERE article_id = '".$articleId."' AND status='APPROVED'") or die(mysql_error());
                    $commentsCount = mysql_num_rows($commentsQuery);
                    
                    // display the attributes
                    echo "
                    <div id='article'>
                    <h2>".$title."</h2>
                    <p>".$intro."</p>
                    <p class='post-footer' align='right'>
                    <a href='index.php?event=author&account=".$accountId."' class='author'>".$authorName."</a>
                    <a href='index.php?event=articles&article=".$articleId."' class='readmore'>Read more</a>
                    <a href='index.php?event=articles&article=".$articleId."#comments' class='comments'>Comments (".$commentsCount.")</a>
                    <span class='date'>".$datePublished."</span>
                    </p>
                    </div>";           
                }
                
                echo "</div>";
            }
            
            // check if there any top rated articles
            if(mysql_num_rows($topRatedArticlesQuery) > 0 || mysql_num_rows($topViewedArticlesQuery) > 0)
            {
                // display the top rated articles
                echo "<h3>More Articles</h3>";
                echo "<div class='bg1'>";
                
                // check if there are any top rated articles
                if(mysql_num_rows($topRatedArticlesQuery) > 0)
                {                
                    echo "<div id='article'>";
                    echo "<h2>Top Rated</h2>";
                    echo "<p>";
                    echo "<ul>";
                    
                    for($i=0; $i<mysql_num_rows($topRatedArticlesQuery); $i++)
                    {
                        // set the attributes
                        $articleId = mysql_result($topRatedArticlesQuery,$i,"article_id");
                        $totalVotes = mysql_result($topRatedArticlesQuery,$i,"total_votes");
                        
                        // query the article title from the database
                        $articleQuery = mysql_query("SELECT title FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
                        $title = mysql_result($articleQuery,0,"title");
                        
                        // display the attributes
                        echo "<li><a href='index.php?event=articles&article=".$articleId."'>".$title."</a> (".$totalVotes." votes)</li>";
                    }
                    
                    echo "</ul>";
                    echo "</p>";
                    echo "</div>";
                }
                
                // check if there are any top viewed articles
                if(mysql_num_rows($topViewedArticlesQuery) > 0)
                {
                    echo "<div id='article'>";
                    echo "<h2>Top Viewed</h2>";
                    echo "<p>";
                    echo "<ul>";
                    
                    for($i=0; $i<mysql_num_rows($topViewedArticlesQuery); $i++)
                    {
                        // set the attributes
                        $articleId = mysql_result($topViewedArticlesQuery,$i,"article_id");
                        $totalHits = mysql_result($topViewedArticlesQuery,$i,"hits");
                        
                        // query the article title from the database
                        $articleQuery = mysql_query("SELECT title FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
                        $title = mysql_result($articleQuery,0,"title");
                        
                        // display the attributes
                        echo "<li><a href='index.php?event=articles&article=".$articleId."'>".$title."</a> (".$totalHits." views)</li>";
                    }
                    
                    echo "</ul>";
                    echo "</p>";
                    echo "</div>";
                }
                
                echo "</div>";
            }
            
            // check if there are any top viewed articles
            if(mysql_num_rows($topViewedArticlesQuery) > 0)
            {
                // display the top viewed articles
            }
            
            return;
        }
        
        /**
         * Get category name method: returns the name of the category
         * Parameter: $categoryId
         * Return type: string
         */
        function getCategoryName($categoryId)
        {
            // include the name retriever class for categories
            require_once("class_libraries/NameRetriever.php");
            $nameRetriever = new NameRetriever("category_id");
            
            // get the name
            $name = $nameRetriever -> getName($categoryId);
            
            // return the name
            return $name;
        }
        
        /**
         * Set Image Path method: sets the image paths of the article
         * Parameter: $content
         * Return Type: string
         */
        function setImagePath($content)
        {
            // the image path from the ADMINISTRATOR, CONTRIBUTOR, and MEMBER is different
            // from the image path of the NON-MEMBER. In order to view the images for NON-MEMBER,
            // the image path is to be reconfigured.
        
            // change the image path inside the content
            if($this -> accountId == null)
            {
                $memberImagePath = "../images";
                $nonMemberImagePath = "images";
                $content = str_replace($memberImagePath, $nonMemberImagePath, $content);
            }
            
            // return the content
            return $content;
        }
        
        /**
         * Display Event Method: displays the current event in a particular day
         * Parameter: $day, $year, $month
         * Return Type: String
         */
        function displayEvents($day, $month, $year)
        {
            // query the events details on that particular day, month, and year
            $eventsQuery = mysql_query("SELECT title, content FROM argus_events WHERE day='".$day."' AND month='".$month."' AND year='".$year."' AND status='SAVED'") or die(mysql_error());
            
            // display the events
            echo "<h3>Calendar &raquo; ".$month."/".$day."/".$year."</h3>";
            echo "<div class='bg1'>";
            
            // check there is a result queried from the database
            if(mysql_num_rows($eventsQuery) > 0)
            {   
                for($i=0; $i<mysql_num_rows($eventsQuery); $i++)
                {
                    // set the attributes
                    $title = mysql_result($eventsQuery,$i,"title");
                    $content = mysql_result($eventsQuery,$i,"content");
                    
                    echo "
                    <div id='article'>
                        <h2>".$title."</h2>
                        <p>".$content."</p>
                    </div>";   
                }
            }
            else
            {
                // notify the user that there are no events for this year
                echo "<p><h3 align='center'>There are no events</h3></p>";
            }
            
            echo "</div>";
            
            return;
        }
        
        /**
         * Display Info method: displays the terms and policies or contacts us page
         * Return Type: string
         */
        function displayInfo($infoType)
        {
            // determing the info type
            if($infoType == "TERMS AND POLICIES")
            {
                // query the terms and policies from the database
                $infoQuery = mysql_query("SELECT content FROM argus_infos WHERE name='terms_and_policies'") or die(mysql_error());
                
                // set the attributes
                if(isset($_COOKIE["argus"]))
                {
                    $content = $this -> setImagePath($content);
                }
                
                // display the Title of the page
                echo "<h3>Terms and Policies</h3>";
                echo "<div class='bg1'>";
                echo "<div id='article'>";
                echo "<h2>Terms and Policies</h2>";
                echo mysql_result($infoQuery,0,"content");
                echo "</div>";
            }
            else
            {
                // query the contact us from the database
                $infoQuery = mysql_query("SELECT content FROM argus_infos WHERE name='contact_us'") or die(mysql_error());
                
                // display the title of the page
                echo "<h3>Contact us</h3>";
                echo "<div class='bg1'>";
                echo "<div id='article'>";
                echo "<h2>Contacts</h2>";
                echo mysql_result($infoQuery,0,"content");
                echo "</div>";
            }
            
            echo "</div>";
            
            return;
        }
        
        /**
         * Strip comments Tags method: this method strips all the unwanted tags on comments to protect the page
         * Parameter: $comment
         * Return Type: String
         */
        function stripCommentTags($comment)
        {
            $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
               '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
               '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
               '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
               );
            
            // remove the tags
            $comment = preg_replace($search, '', $comment);
               
            return $comment;
        }
        
        /**
         * Display Archives By Year method: displays the enabled archives by year
         * Return Type: String
         */
        function displayArchivesByYear()
        {
            // query all enabled archives from the category by year and counting how many articles are in that year
            $archivesQuery = mysql_query("SELECT COUNT(archive_id), year FROM argus_archives WHERE status = 'ENABLED' GROUP BY year") or die(mysql_error());
            
            // set the title of the page
            echo "<h3>Archives</h3>";
            echo "<div class='bg1' id='tablePanel'>";
            
            // check if there are archives in the database
            if(mysql_num_rows($archivesQuery) ==  0)
            {
                // notify the user that there are no available archives
                echo "<p><h3 align='center'>There are no available archives</h3></p>";
            }
            else
            {
                // display all available archives in a table form
                echo "<table width='100%'>";
                echo "<tr>";
                echo "<th align='center'>Year</th>";
                echo "<th align='center'>Number of Articles</th>";
                echo "</tr>";
                
                // display the years
                $color = true;
                
                while($row = mysql_fetch_array($archivesQuery))
                {
                    // display the table in an alternate color
                    if($color == true)
                    {
                        echo "<tr class='bg1'";
                        $color = false;
                    }
                    else
                    {
                        echo "<tr>";
                        $color = true;
                    }
                    
                    // set the attributes
                    $year = $row["year"];
                    $articlesCount = $row["COUNT(archive_id)"];
                    
                    // display the attributes
                    echo "<td align='center'><a href='index.php?event=archives&year=".$year."'>".$year."</a></td>";
                    echo "<td align='center'>".$articlesCount."</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            }
            
            echo "</div>";
            
            return;
        }
        
        /**
         * Display Archives By Issue Method: displays enabled archives by issue in a specified year
         * Parameter: $year
         * Return Type: string
         */
        function displayArchivesByIssue($year)
        {
            // query all articles in the specified year
            $archivesQuery = mysql_query("SELECT COUNT(archive_id), issue FROM argus_archives WHERE year='".$year."' AND status='ENABLED' GROUP BY issue") or die(mysql_error());
            
            // set the page
            echo "<h3><a href='index.php?event=archives'>Archives</a> &raquo; ".$year."</h3>";
            echo "<div class='bg1' id='tablePanel'>";
            
            // check if the archive exists
            if(mysql_num_rows($archivesQuery) == 0)
            {
                // notify the user that there are no available archives on that year
                echo "<p><h3 align='center'>There are no available archives</h3></p>";
            }
            else
            {
                // display the archives in a table
                echo "<table width='100%'>";
                echo "<tr>";
                echo "<th align='center'>Issue</th>";
                echo "<th align='center'>Number of Articles</th>";
                echo "</tr>";
                
                // display the archives
                $color = true;
                
                while($row = mysql_fetch_array($archivesQuery))
                {
                    // display the table in an alternate color
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
                    $issue = $row["issue"];
                    $articlesCount = $row["COUNT(archive_id)"];
                    
                    // display the attributes
                    echo "<td align='center'><a href='index.php?event=archives&year=".$year."&issue=".$issue."'>".$issue."</a></td>";
                    echo "<td align='center'>".$articlesCount."</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            }
            
            echo "</div>";
            
            return;
        }
        
        /**
         * Display Archives: displays archives in a specified issue and year
         * Parameter: $year, $issue
         * Return Type: string
         */
        function displayArchives($year, $issue)
        {
            // query all archives articles in the specified year and issue
            $archivesQuery = mysql_query("SELECT path, date_archived, title FROM argus_archives WHERE year='".$year."' AND issue='".$issue."'") or die(mysql_error());
            
            // set the title of the page
            echo "<h3><a href='index.php?event=archives'>Archives</a> &raquo; <a href='index.php?event=archives&year=".$year."'>".$year."</a> &raquo; ".$issue."</h3>";
            echo "<div class='bg1' id='tablePanel'>";
            
            // display the archives in a table
            echo "<table width='100%'>";
            echo "<tr>";
            echo "<th align='center'>Title</th>";
            echo "<th align='center'>Date Archived</th>";
            echo "</tr>";
            
            // display the archives
            $color = true;
            
            for($i=0; $i<mysql_num_rows($archivesQuery); $i++)
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
                $path = mysql_result($archivesQuery,$i,"path");
                $date_archived = date("m/d/y", mysql_result($archivesQuery,$i,"date_archived"));
                $title = mysql_result($archivesQuery,$i,"title");
                
                // check if the account id has been set up
                if(!isset($this -> accountId))
                {
                    // change the path if the account id has not been set up
                    $path = str_replace("../","",$path);
                }
                
                // display the attributes
                echo "<td align='center'><a href='".$path."'>".$title."</a></td>";
                echo "<td align='center'>".$date_archived."</td>";
                
                echo "</tr>";
            }
            
            echo "</table>";
            
            echo "</div>";
            
            return;
        }
        
        /**
         * Display Author Info method: displays the information of the author
         * Parameter: $accountId
         * Return Type: string
         */
        function displayAuthorInfo($accountId)
        {
            // query the account information from the database
            $accountQuery = mysql_query("SELECT id_number, name, position, photo_path FROM argus_accounts WHERE account_id = '".$accountId."'") or die(mysql_error());
            
            // check if the account exists in the database
            if(mysql_num_rows($accountQuery) > 0)
            {
                // set the attributes
                $idNumber = mysql_result($accountQuery,0,"id_number");
                $name = mysql_result($accountQuery,0,"name");
                $position = mysql_result($accountQuery,0,"position");
                $photoPath = mysql_result($accountQuery,0,"photo_path");
                
                // set the title of the page
                echo "<h3>".$name."</h3>";
                echo "<div class='bg1'>";
                
                // check if the author has a photo
                if(empty($photoPath))
                {
                    // check who is using this class
                    if($this -> accountId != null)
                    {
                        // default photo path for admin, contributor, and members
                        $photoPath = "../images/accounts/Default.png";
                    }
                    else
                    {
                        // default photo path for guest and visitors
                        $photoPath = "images/accounts/Default.png";
                    }
                }
                else
                {
                    // check who is using this class
                    if($this -> accountId == null)
                    {
                        $photoPath = str_replace("../","",$photoPath);
                    }
                }
                
                echo "<p align='center'>";
                echo "<img src='".$photoPath."'><br />";
                echo "</p>";
                
                echo "<h3 align='center'>";
                echo $idNumber."<br />";
                echo $name."<br />";
                echo $position."<br />";
                echo "</h3>";
                echo "<p><br /></p>";
                
                echo "</div>";
                
                // query the published articles of the user
                $articlesQuery = mysql_query("SELECT article_id, title, category_id FROM argus_articles WHERE account_id = '".$accountId."' AND status='PUBLISHED'") or die(mysql_error());
            
                // check if there are articles Queried
                if(mysql_num_rows($articlesQuery) > 0)
                {
                    // set the title
                    echo "<h3>Published Articles</h3>";
                    echo "<div class='bg1' id='tablePanel'>";
                    
                    // display the articles in a table form
                    echo "<table width='100%'>";
                    echo "<tr>";
                    echo "<th align='center'>Title</th>";
                    echo "<th align='center'>Category</th>";
                    echo "</tr>";
                    
                    // display the articles
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
                        $articleId = mysql_result($articlesQuery,$i,"article_id");
                        $title = mysql_result($articlesQuery,$i,"title");
                        $category = $this -> getCategoryName(mysql_result($articlesQuery,$i,"category_id"));
                        
                        // display the attributes
                        echo "<td align='center'><a href='index.php?event=articles&article=".$articleId."'>".$title."</a></td>";
                        echo "<td align='center'>".$category."</td>";
                        
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                    
                    echo "</div>";
                }
            }

            return;
        }
        
        /**
         * Create Tickler Method: auto generates a tickler for the article
         * Parameter: $content, $wordLimit
         * Return Type: string
         */
        function createTickler($content, $wordLimit)
        {            
            // divide the content into an array
            $explodedContent = explode(" ", $content);
         
            // limit the tickler to 200 words only   
            for ($i=0; $i<$wordLimit; $i++) 
            {
                // set the tickler
                $tickler .= $explodedContent[$i]." ";
            }
            
            // return the tickler
            return $tickler;
        }
     
        /**
         * Display Summary of Events method: displays all events for the current month in the current year
         * Parameter: $month, $year
         * Return type: string
         */
        function displayEventSummary($month, $year)
        {   
            // query all events in the given month of the given year
            $eventsQuery = mysql_query("SELECT title, content, day FROM argus_events WHERE month='".$month."' AND year='".$year."' AND status='SAVED' ORDER BY day ASC") or die(mysql_error());
            
            // set the title of the page
            echo "<h3>Summary of Events</h3>";
            echo "<div class='bg1'>";
            
            // display the events
            for($i=0; $i<mysql_num_rows($eventsQuery); $i++)
            {
                // set the attributes
                $title = mysql_result($eventsQuery,$i,"title");
                $content = mysql_result($eventsQuery,$i,"content");
                $day = mysql_result($eventsQuery,$i,"day");
                
                echo "
                <div id='article'>
                <h2>".$title."</h2>
                <p>Day of event: ".$day."<br />".$content."</p>
                </div>";
            }
            
            echo "</div>";
            
            return;
        }
        
        /**
         * Display Search Result method: search the word and displayes the result
         * Parameter: $word
         * Return Type: string
         */
        function displaySearchResult($word)
        {
            // check if the key word has a content
            if(trim($word) == "")
            {
                // do not process anything
                return;
            }
            
            // search the word from the published articles
            $searchPublishedQuery = mysql_query("SELECT article_id, title, intro FROM argus_articles WHERE (title LIKE '%".$word."%' OR intro LIKE '%".$word."%' OR content LIKE '%".$word."%') AND status='PUBLISHED'") or die(mysql_error());
            
            // search the archived articles
            $searchArchivedQuery = mysql_query("SELECT article_id, title, issue, date_archived, year FROM argus_archives WHERE title LIKE '%".$word."%' AND year != '".date("Y", time())."'") or die(mysql_error());
            
            // search the authors
            $searchAuthorsQuery = mysql_query("SELECT account_id, name, id_number, position FROM argus_accounts WHERE name LIKE '%".$word."%'") or die(mysql_error());
            
            // display the title of the page
            echo "<h3>Search Result</h3>";
            echo "<div class='bg1'>";
            
            // check if there are results
            if(mysql_num_rows($searchPublishedQuery) == 0 && mysql_num_rows($searchArchivedQuery) == 0 && mysql_num_rows($searchAuthorsQuery) == 0)
            {
                // display that there are no search results
                echo "<p><h3 align='center'>There are no search results</h3></p>";
            }
            else
            {
                // check if there are results in the published articles
                if(mysql_num_rows($searchPublishedQuery) > 0)
                {
                    // display the search results
                    for($i=0; $i<mysql_num_rows($searchPublishedQuery); $i++)
                    {
                        // set the attributes
                        $articleId = mysql_result($searchPublishedQuery,$i,"article_id");
                        $title = mysql_result($searchPublishedQuery,$i,"title");
                        $intro = mysql_result($searchPublishedQuery,$i,"intro");
                        
                        // check if the intro is blank or not
                        $result = $this -> validateIntro($intro);
                        
                        // check the result
                        if($result == false)
                        {
                            // if the content is blank query the content of the article
                            $contentQuery = mysql_query("SELECT content FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
                            $content = mysql_result($contentQuery,0,"content");
                            
                            // create a tickler out of the content
                            $intro = $this -> createTickler($content, 50);
                        }
                        
                        // check who is using the main page form class
                        if($this -> accountId == null)
                        {
                            // check if there are images inside the intro... the images will be copied to another folder so
                            // that when the user deletes the image, the article is not affected.
                            preg_match_all('/<img.*?src\s*=\s*["\'](.+?)["\']/im', $intro, $imagePaths);
                            
                            for($i=0; $i<count($imagePaths[1]); $i++)
                            {
                                // replace the image paths so that the none members can view the images inside the content
                                // image path syntax that was extracted
                                // e.g. ../images/client/123.jpg
                                // we try to get the 123.jpg only
                                $explodedImagePath = explode("/", $imagePaths[1][$i]);
                                $imageName = $explodedImagePath[count($explodedImagePath) - 1];
                                
                                // set the image path
                                $memberImagePath = "../images/server/".$imageName;
                                $nonMemberImagePath = "images/server/".$imageName;
                    
                                // replace the image path
                                $intro = str_replace($memberImagePath, $nonMemberImagePath, $intro);
                            }
                        }
                        
                        
                        
                        // display the result
                        echo "<div id='article'>";
                        echo "<h2><a href='index.php?event=articles&article=".$articleId."'>".$title."</a></h2>";
                        echo "<p>".$intro."</p>";
                        echo "</div>";
                    }
                }
                
                // check if there are results in the archive articles
                if(mysql_num_rows($searchArchivedQuery) > 0)
                {
                    // display the search results
                    for($i=0; $i<mysql_num_rows($searchArchivedQuery); $i++)
                    {
                        // set the attributes
                        $articleId = mysql_result($searchArchivedQuery,$i,"article_id");
                        $title = mysql_result($searchArchivedQuery,$i,"title");
                        $issue = mysql_result($searchArchivedQuery,$i,"issue");
                        $dateArchived = date("M d,Y",mysql_result($searchArchivedQuery,$i,"date_archived"));
                        $year = mysql_result($searchArchivedQuery,$i,"year");
                        
                        // check who is using this class
                        if($this -> accountId != null)
                        {
                            // if not null then the administrator, contributor, or member is using this class
                            $articlePath = "../archives/".$year."/".$issue."/".$articleId.".html";
                        }
                        else
                        {
                            // article path for guests or visitors
                            $articlePath = "archives/".$year."/".$issue."/".$articleId.".html";
                        }
                        
                        // display the attributes
                        echo "<div id = 'article'>";
                        echo "<h2><a href='".$articlePath."'>".$title."</a></h2>";
                        echo "
                        <p>
                            Status : Archived<br />
                            Date Archived : ".$dateArchived."<br />
                            Issue : ".$issue."
                        </p>";
                        echo "</div>";
                    }
                }
                
                // check if there are results in the accounts
                if(mysql_num_rows($searchAuthorsQuery) > 0)
                {
                    // display the results
                    for($i=0; $i<mysql_num_rows($searchAuthorsQuery); $i++)
                    {
                        // set the attributes
                        $accountId = mysql_result($searchAuthorsQuery,$i,"account_id");
                        $name = mysql_result($searchAuthorsQuery,$i,"name");
                        $idNumber = mysql_result($searchAuthorsQuery,$i,"id_number");
                        $position = mysql_result($searchAuthorsQuery,$i,"position");
                        
                        // display the attributes
                        echo "<div id='article'>";
                        echo "<h2><a href='index.php?event=author&account=".$accountId."'>".$name."</a></h2>";
                        echo "
                        <p>
                            ID Number : ".$idNumber."<br />
                            Position : ".$position."
                        </p>";
                        echo "</div>";
                    }
                }
            }
            
            echo "</div>";
            return;
        }
        
        /**
         * Validate Intro method: validates the intro if it is empty or not
         * Parameter: $intro
         * Return Type: boolean
         */
        function validateIntro($intro)
        {
            // include the article content validator class and validate the intro
            require_once("class_libraries/ArticleContentValidator.php");
            $contentValidator = new ArticleContentValidator();
            
            // validate the content
            $result = $contentValidator -> validateContent($intro);
            
            // return the result
            return $result;
        }
	 }
?>