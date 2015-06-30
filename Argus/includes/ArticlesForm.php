<?php
    /**
     * Filename : ArticlesForm.php
     * Description : class file for managing articles that has already been submitted
     * Date Created : December 7,2007
     * Author : Argus Team
     */
    
    /**
     * METHODS SUMMARY:
     */
    
    class ArticlesForm
    {
        var $errors;
        
        /**
         * Approve Article method: approves an article sending the article to the approved section
         * Parameter: $articleId
         */
        function approveArticle($articleId)
        {
            // approve the article
            mysql_query("UPDATE argus_articles SET status = 'APPROVED' WHERE article_id = '".$articleId."'") or die(mysql_error());
             
            return;
        }
        
        /**
         * Display Banner method: displays the banner and menu for articles
         * Return type: string
         */
        function displayBanner()
        {
            echo "
            <div class='bg2'>
            <h2><em>Articles Manager</em></h2>
            <p align='center'>";
            
            // menus
            echo "
            <a href='argusarticles.php'>Submitted</a> . 
            <a href='argusarticles.php?event=approved'>Approved</a> . 
            <a href='argusarticles.php?event=rejected'>Rejected</a> . 
            <a href='argusarticles.php?event=published'>Published</a> . 
            <a href='argusarticles.php?event=issues'>Issues</a> . 
            <a href='search.php?event=articles'>Search</a>";
            
            echo "
            </p>
            </div>";
        
            return;
        }
        
        /**
         * Display Articles method: display all articles depending on the status parameter of the method
         * Parameter: $status, $page
         * Return type: string
         */
        function displayArticles($status, $page)
        {
            if($status == "REJECTED" || $status == "PENDING")
            {
                // query the total number of articles on that status
                $articlesCountQuery = mysql_query("SELECT article_id FROM argus_articles WHERE status = '".$status."'") or die(mysql_error());
            }
            else
            {
                // query the total number of articles on that status
                $articlesCountQuery = mysql_query("SELECT article_id FROM argus_articles WHERE status = '".$status."' AND issue_id = '0'") or die(mysql_error());
            }
            
            // get the articles count
            $articlesCount = mysql_num_rows($articlesCountQuery);
            
            // set the number of articles to be displayed per page
            $limit = 15;
            
            // compute the number of pages
            $numberOfPages = $articlesCount/$limit;
            
            // check the status of the current page
            if(empty($page) && !ctype_digit($page))
            {
                // set the default page which is page 1
                $page = 1;
            }
            
            // compute the limit Value
            $limitValue = $page * $limit - ($limit);
        
            if($status == "REJECTED" || $status == "PENDING")
            {
                // query the articles in the database with the PENDING and REJECTED status of article
                $articlesQuery = mysql_query("SELECT article_id, account_id, category_id, title, date_submitted FROM argus_articles WHERE status = '".$status."' ORDER BY date_modified DESC LIMIT ".$limitValue.",".$limit."") or die(mysql_error());
            }
            else
            {
                // query the articles in the database with APPROVED status and has no ISSUE ID yet
                // all articles in the approved section are articles with no issue ids yet.
                // articles with issue id will be found on the issues section of the page
                $articlesQuery = mysql_query("SELECT article_id, account_id, issue_id, category_id, title, publish_type, date_modified FROM argus_articles WHERE status = '".$status."' AND issue_id = '0' ORDER BY date_modified DESC LIMIT ".$limitValue.",".$limit."") or die(mysql_error());
                
                // if status is APPROVED, query also all the available issues
                $issuesQuery = mysql_query("SELECT issue_id, name FROM argus_issues WHERE status = 'ENABLED'") or die(mysql_error());
            }
            
            // set the title of the form
            echo "
            <h3>".ucfirst(strtolower($status))."</h3>
            <div class='bg1' id='tablePanel'>";
            
            // check if there are articles queried from the status provided
            if(mysql_num_rows($articlesQuery) == 0)
            {
                // notify the user that the articles that are being requested are not available
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
                
                if($status == "PENDING" || $status == "REJECTED")
                {
                    // create the form for PENDING and REJECTED articles
                    echo "<form id='form_id' method='post' action='".$_SERVER['PHP_SELF']."?event=".strtolower($status)."'>";
                }
                else
                {
                    // create the form for APPROVED articles setting the issue to 0 so that
                    // when the set issue button is triggered, the program will know which issue
                    // is to be updated
                    echo "<form id='form_id' method='post' action='".$_SERVER['PHP_SELF']."?event=".strtolower($status)."&issue=0'>";
                }
                
                echo"
                <table width='100%'>
                <tr>
                <th><input type='checkbox' onClick='toggleCheckBoxes(\"articleIds\")'></th>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>";
                
                if($status == "APPROVED")
                {
                    // additional display of attributes for approved articles
                    echo "
                    <th>Type</th>
                    <th>Modified</th>
                    <th>Issue</th>";                    
                }
                else
                {
                    // display for pending and rejected articles
                    echo "<th>Submitted</th>";                         
                }
                
                echo "
                <th>Action</th>
                </tr>";
                
                // display the articles
                $color = true;
                
                for($i=0; $i < mysql_num_rows($articlesQuery); $i++)
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
                    $authorName = $this -> getAuthorName(mysql_result($articlesQuery,$i,"account_id"));
                    $categoryName = $this -> getCategoryName(mysql_result($articlesQuery,$i,"category_id"));
                    $title = $this -> limitTitle(stripslashes(mysql_result($articlesQuery,$i,"title")));
                    
                    // display the attributes
                    echo "
                    <td><input type='checkbox' name='articleIds[]' value='".$articleId."'></td>
                    <td><a href='argusarticles.php?event=view&article=".$articleId."'>".$title."</a></td>
                    <td>".$authorName."</td>
                    <td>".$categoryName."</td>";
                    
                    // additional display of attributes for APPROVED articles
                    if($status == "APPROVED")
                    {
                        $publishType = mysql_result($articlesQuery,$i,"publish_type");
                        $issueName = $this -> getIssueName(mysql_result($articlesQuery,$i,"issue_id"));
                        $dateModified = $this -> getDateModified(mysql_result($articlesQuery,$i,"date_modified"));
                        
                        echo "<td>".$publishType."</td>";
                        echo "<td>".$dateModified."</td>";
                        echo "<td>
                        <select name='issueIds[]' id='textbox' style='width:70px'>
                        <option value='0'>None</option>";
                        
                        // set the position of the issue in the combo box
                        for($j=0; $j < mysql_num_rows($issuesQuery); $j++)
                        {
                            $issueId = mysql_result($issuesQuery,$j,"issue_id");
                            $issueName = $this -> getIssueName($issueId);
                            echo "<option value='".$issueId."'>".$issueName."</option>";
                        }
                        
                        echo "
                        </select>
                        </td>";
                    }
                    else
                    {
                        $dateSubmitted = date("m/d/y", mysql_result($articlesQuery,$i,"date_submitted"));
                        echo "<td>".$dateSubmitted."</td>"; 
                    }
                    
                    echo "<td>";
                    
                    // set the actions
                    if($status == "PENDING")
                    {
                        // set the actions for PENDING articles
                        echo "
                        <a href='argusarticles.php?event=".strtolower($status)."&action=approve&article=".$articleId."' title='Approve'><img src='../miscs/images/Default/article_approve.png'></a> 
                        <a href='argusarticles.php?event=".strtolower($status)."&action=reject&article=".$articleId."' title='Reject'><img src='../miscs/images/Default/article_trash.png'></a>";
                    }
                    else if($status == "REJECTED")
                    {
                        // set the actions for REJECTED articles
                        echo "
                        <a href='argusarticles.php?event=".strtolower($status)."&action=approve&article=".$articleId."' title='Approve'><img src='../miscs/images/Default/article_approve.png'></a> 
                        <a href='argusarticles.php?event=".strtolower($status)."&action=delete&article=".$articleId."' title='Delete'><img src='../miscs/images/Default/article_delete.png'></a>";
                    }
                    else if($status == "APPROVED")
                    {
                        // set the actions for APPROVED articles
                        echo "
                        <a href='argusarticlesedit.php?article=".$articleId."' title='Edit'><img src='../miscs/images/Default/article_edit.png'></a> 
                        <a href='argusarticles.php?event=".strtolower($status)."&action=publish&article=".$articleId."' title='Publish'><img src='../miscs/images/Default/issue_post.png'></a> 
                        <a href='argusarticles.php?event=".strtolower($status)."&action=reject&article=".$articleId."' title='Reject'><img src='../miscs/images/Default/article_trash.png'></a>";
                    }
                    
                    echo "
                    </td>
                    </tr>";
                }
                
                echo "</table>";
                
                // set the buttons for managing the articles
                echo "<table width='100%'>";
                echo "<tr><td>";
                
                if($status == "PENDING")
                {
                    // buttons for PENDING articles
                    echo "
                    <input type='submit' id='submit1' name='approve' value='Approve'> 
                    <input type='submit' id='submit1' name='reject' value='Reject'";
                }
                else if($status == "REJECTED")
                {
                    // buttons for REJECTED articles
                    echo "
                    <input type='submit' id='submit1' name='approve' value='Approve'> 
                    <input type='submit' id='submit1' name='delete' value='Delete'> 
                    <input type='submit' id='submit1' name='deleteAll' value='Delete all'>";
                }
                else if($status == "APPROVED")
                {
                    // buttons for APPROVED articles
                    echo "
                    <input type='submit' id='submit1' name='reject' value='Reject'>
                    <input type='submit' id='submit1' name='setIssues' value='Set Issues'>
                    <input type='submit' id='submit1' name='publishArticles' value='Publish'>";
                }
                
                echo "
                </td>
                <td align='right'>";
                
                // display the previous page link
                if($page > 1)
                {
                    echo "<a href='argusarticles.php?event=".$_GET["event"]."&page=".($page-1)."'><img src='../miscs/images/Default/previous.png'></a> ";
                }
                
                // display the next page link
                if($page < $numberOfPages)
                {
                    echo "<a href='argusarticles.php?event=".$_GET["event"]."&page=".($page+1)."'><img src='../miscs/images/Default/next.png'></a>";
                }
                
                echo "
                </td>
                </tr>
                </table>                
                </form>";
            }
            
            echo "</div>";
        }
        
        /**
         * Delete Article Method: delets an article permanently
         * Parameter: $articleId
         */
        function deleteArticle($articleId)
        {
            // delete the article permanently
            // before an article is going to be deleted, delete the images attached to the content of the article
            // query the content of the article
            $articleQuery = mysql_query("SELECT content FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
            $content = mysql_result($articleQuery,0,"content");
            
            preg_match_all('!<img.*?src\s*=\s*"([^"]+)!im', $content, $imagePaths);
                
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
                
                // delete the image
                unlink("../images/server/".$imageName);
            }
            
            mysql_query("DELETE FROM argus_articles WHERE article_id = '".$articleId."' AND status = 'REJECTED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Delete All Rejected Articles method: deletes all articles that are rejected
         */
        function deleteAllRejectedArticles()
        {
            // delete all
            mysql_query("DELETE FROM argus_articles WHERE status = 'REJECTED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Delete comments method: deletes comments of an article
         * parameter: $articleId
         */
        function deleteComments($articleId)
        {
            // delete all the comments of the article
            mysql_query("DELETE FROM argus_comments WHERE article_id = '".$articleId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Delete Article Details method: deletes details of an article from the database
         * parameter: $articleId
         */
        function deleteArticleDetails($articleId)
        {
            // delete the article ratings from the database
            mysql_query("DELETE FROM argus_article_ratings WHERE article_id = '".$articleId."'") or die(mysql_error());
            
            // delete the article hits from the database
            mysql_query("DELETE FROM argus_article_hits WHERE article_id = '".$articleId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Display Issues method: displays all the available issues including the number of articles that is in that issue
         */
        function displayIssues()
        {
            // query all the available issues that was set
            $issuesQuery = mysql_query("SELECT issue_id, name FROM argus_issues WHERE status = 'ENABLED'") or die(mysql_error());
            
            // set the title and the form
            echo "<h3>Issues</h3>";
            echo "<div class='bg1' id='tablePanel'>";
            
            // check if there is an issue queried from the database
            if(mysql_num_rows($issuesQuery) == 0)
            {
                // notify the user that there are no issues available in the database
                echo "<p><h3 align='center'>There are no AVAILABLE issues</h3></p>";
            }
            else
            {
                // inlcude the TOOL TIP ajax and create a tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                // include the checkbox funtions where check box are allowed to be selected/unselected all
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
                // set the form and table where the issues are going to be displayed
                echo "
                <form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=".$_GET["event"]."'>
                <table width='100%'>
                <tr>
                <th><input type='checkbox' onClick='toggleCheckBoxes(\"issueIds\")'></th>
                <th>Name</th>
                <th>Number of Articles</th>
                <th>Action</th>
                </tr>";
                
                // display the issues
                $color = true;
                
                for($i=0; $i<mysql_num_rows($issuesQuery); $i++)
                {
                    // create a row in an alternate color
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
                    $issueId = mysql_result($issuesQuery,$i,"issue_id");
                    $name = mysql_result($issuesQuery,$i,"name");
                    
                    // query the number of articles that is in that issue
                    $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE issue_id = '".$issueId."'") or die(mysql_error());
                    $articlesCount = mysql_num_rows($articlesQuery);
                    
                    // display the attributes
                    echo "<td><input type='checkbox' name='issueIds[]' value='".$issueId."'></td>";
                    echo "<td><a href='argusarticles.php?event=viewissue&issue=".$issueId."'>".$name."</a></td>";
                    echo "<td>".$articlesCount."</td>";
                    
                    // set the action for each issue
                    echo "
                    <td>
                    <a href='argusarticles.php?event=".$_GET["event"]."&action=remove&issue=".$issueId."' title='Remove'><img src='../miscs/images/Default/article_remove.png'></a> 
                    <a href='argusarticles.php?event=".$_GET["event"]."&action=publish&issue=".$issueId."' title='Publish'><img src='../miscs/images/Default/issue_post.png'></a>
                    </td>";
                    
                    
                    echo "</tr>";
                }
                
                echo "</table>";
                
                // set the buttons for managing issues
                echo "<table width='100%'>";
                echo "<tr>";
                echo "<td>";
                echo "<input type='submit' id='submit1' value='Remove articles' name='removeIssuedArticles'> ";
                echo "<input type='submit' id='submit1' value='Remove all articles' name='removeAllIssuedArticles'>";
                echo "</td>";
                echo "</tr>";
                echo "</table>";
                
                echo "</form>";
            }
            
            echo "</div>";
            return;
        }
          
        /**
         *  display Issue Articles Method: displays the articles in a particular issue
         */
        function displayIssueArticles($issueId)
        {
            // get the name of the issue
            $issueName = $this -> getIssueName($issueId);
            
            // check if there really is a name of the issue id
            if($issueName != "UNKOWN")
            {
                // query all the articles that is in that particular issue
                $articlesQuery = mysql_query("SELECT article_id, account_id, issue_id, category_id, title, publish_type, date_modified FROM argus_articles WHERE status = 'APPROVED' AND issue_id = '".$issueId."' ORDER BY date_modified DESC") or die(mysql_error());
                
                // query also all the available issues
                $issuesQuery = mysql_query("SELECT issue_id, name FROM argus_issues WHERE status = 'ENABLED'") or die(mysql_error());
                
                // set the title and the form
                echo "<h3><a href='argusarticles.php?event=issues'>Issues</a> &raquo ".$issueName."</h3>";
                echo "<div class='bg1' id='tablePanel'>";
                
                // check if there is a result from the database
                if(mysql_num_rows($articlesQuery) == 0)
                {
                    // notify the user that there are no articles in that particular issue
                    echo "<p><h3 align='center'>There are no articles in the ".$issueName." issue</h3></p>";
                }
                else
                {
                    // inlcude the TOOL TIP ajax and create a tool tip
                    include("ajax_libraries/ToolTip.php");
                    $toolTip = new ToolTip();
                    $toolTip -> setupForm();
                    
                    // include the checkbox funtions where check box are allowed to be selected/unselected all
                    echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                    
                    // create the form and table where to display the queried articles
                    echo "
                    <form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=viewissue&issue=".$issueId."'>
                    <table width='100%'>
                    <tr>
                    <th><input type='checkbox' onClick='toggleCheckBoxes(\"articleIds\")'></th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Modified</th>
                    <th>Issue</th>
                    <th>Action</th>
                    </tr>";
                    
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
                        $authorName = $this -> getAuthorName(mysql_result($articlesQuery,$i,"account_id"));
                        $categoryName = $this -> getCategoryName(mysql_result($articlesQuery,$i,"category_id"));
                        $title = $this -> limitTitle(stripslashes(mysql_result($articlesQuery,$i,"title")));
                        $publishType = mysql_result($articlesQuery,$i,"publish_type");
                        $issueName = $this -> getIssueName(mysql_result($articlesQuery,$i,"issue_id"));
                        $dateModified = $this -> getDateModified(mysql_result($articlesQuery,$i,"date_modified"));
                        
                        // display the attributes
                        echo "
                        <td><input type='checkbox' name='articleIds[]' value='".$articleId."'></td>
                        <td><a href='argusarticles.php?event=view&article=".$articleId."'>".$title."</a></td>
                        <td>".$authorName."</td>
                        <td>".$categoryName."</td>
                        <td>".$publishType."</td>
                        <td>".$dateModified."</td>
                        <td>
                        <select name='issueIds[]' id='textbox' style='width:70px'>
                        <option value='0'>None</option>";
                        
                        // set the position of the issue in the combo box
                        for($j=0; $j < mysql_num_rows($issuesQuery); $j++)
                        {
                            $queriedIssueId = mysql_result($issuesQuery,$j,"issue_id");
                            $queriedIssueName = $this -> getIssueName($queriedIssueId);
                            
                            // set the selected issue id
                            if($issueId == $queriedIssueId)
                            {
                                echo "<option value='".$queriedIssueId."' selected='selected'>".$queriedIssueName."</option>";
                            }
                            else
                            {
                                echo "<option value='".$queriedIssueId."'>".$queriedIssueName."</option>";
                            }
                        }
                        
                        echo "
                        </select>
                        </td>";
                        
                        // set the actions
                        echo "
                        <td>
                        <a href='argusarticlesedit.php?article=".$articleId."' title='Edit'><img src='../miscs/images/Default/article_edit.png'></a> 
                        <a href='argusarticles.php?event=viewissue&issue=".$issueId."&action=remove&article=".$articleId."' title='Remove'><img src='../miscs/images/Default/article_remove.png'></a> 
                        <a href='argusarticles.php?event=viewissue&issue=".$issueId."&action=reject&article=".$articleId."' title='Reject'><img src='../miscs/images/Default/article_trash.png'></a>
                        </td>";
                        
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                    
                    // set the buttons for managing the articles in the specific issue
                    echo "
                    <table width='100%'>
                    <tr>
                    <td>
                    <input type='submit' id='submit1' name='reject' value='Reject'>
                    <input type='submit' id='submit1' name='setIssues' value='Set issues'> 
                    <input type='submit' id='submit1' name='removeIssuedArticle' value='Remove'>
                    </td>
                    <td align='right'>
                    <input type='submit' id='submit1' name='publishIssue' value='Publish issue'>
                    </td>
                    </tr>
                    </table>
                    </form>";
                }
                
                echo "</div>";
            }
            
            return;
        }
        
        /**
         * Display Published method: displays all the categories and the number of published articles
         * Return type: string
         */
        function displayPublished()
        {
            // query all available categories
            $categoriesQuery = mysql_query("SELECT category_id, name FROM argus_categories WHERE status = 'ENABLED'") or die(mysql_error());
            
            // set the title of the page
            echo "
            <h3>Published</h3>
            <div class='bg1' id='tablePanel'>
            <form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=published'>";  
            
            // check if there are enabled categories
            if(mysql_num_rows($categoriesQuery) == 0)
            {
                // if there are no available categories, notify the user
                echo "<p><h3 align='center'>There are no AVAILABLE categories</h3></p>";
            }
            else
            {
                // inlcude the TOOL TIP ajax and create a tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                // include the checkbox funtions where check box are allowed to be selected/unselected all
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
                // set the form and table
                echo "
                <table width='100%'>
                <tr>
                <th><input type='checkbox' onClick='toggleCheckBoxes(\"categoryIds\")'></th>
                <th>Name</th>
                <th>Number of Articles</th>
                <th>Action</th>
                </tr>";
                
                // display the categories
                $color = true;
                
                for($i=0; $i < mysql_num_rows($categoriesQuery); $i++)
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
                    $categoryId = mysql_result($categoriesQuery,$i,"category_id");
                    $name = mysql_result($categoriesQuery,$i,"name");
                    
                    // count how many articles are there in that specific category
                    $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED'") or die(mysql_error());
                    $articlesCount = mysql_num_rows($articlesQuery);
                    
                    // display the attributes
                    echo "
                    <td><input type='checkbox' name='categoryIds[]' value='".$categoryId."'></td>
                    <td><a href='argusarticles.php?event=viewcategorypublished&category=".$categoryId."'>".$name."</a></td>
                    <td>".$articlesCount."</td>";
                                        
                    // set the action for managing the enabled categories
                    echo "
                    <td>
                    <a href='argusarticles.php?event=published&action=remove&category=".$categoryId."' title='Remove'><img src='../miscs/images/Default/article_remove.png'></a> 
                    </td>";
                    
                    echo "</tr>";
                }
                
                echo "</table>";
                
                // display the buttons for managing the published articles
                echo "
                <table width='100%'>
                <tr>
                <td>
                <input type='submit' id='submit1' value='Remove articles' name='removePublishedArticles'> 
                <input type='submit' id='submit1' value='Remove all articles' name='removeAllPublishedArticles'>
                </td>
                </tr>
                </table>";
            }
            
            echo "</div>";
            
            // display the published articles by TEMPLATE
            echo "<h3>Front Page</h3>";
            echo "<div class='bg1' id='tablePanel'>";
            echo "<table width='100%'>";
            echo "<tr>";
            echo "<th><input type='checkbox' onClick='toggleCheckBoxes(\"pages\")'></th>";
            echo "<th>Front Page</th>";
            echo "<th>Number of Articles</th>";
            echo "<th>Action</th>";
            echo "</tr>";
            echo "<tr class='bg1'>";
            
            // query the number of published articles from the main page where they are MAIN
            $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE status = 'PUBLISHED' AND publish_type = 'MAIN'") or die(mysql_error());
            $articlesCount = mysql_num_rows($articlesQuery);
            
            // display the queried MAIN published articles
            echo "<td><input type='checkbox' name='pages[]' value='MAIN'></td>";
            echo "<td><a href='argusarticles.php?event=viewpagepublished&page=main'>Main Articles</a></td>";
            echo "<td>".$articlesCount."</td>";
            echo "<td><a href='argusarticles.php?event=published&action=remove&page=main' title='Remove'><img src='../miscs/images/Default/article_remove.png'></a></td>";
            echo "</tr>";
            
            echo "<tr>";
            
            // query the number of published articles from the main page where they are FEATURED
            $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE status = 'PUBLISHED' AND publish_type = 'FEATURED'") or die(mysql_error());
            $articlesCount = mysql_num_rows($articlesQuery);
            
            // display the queried FEATURED published articles
            echo "<td><input type='checkbox' name='pages[]' value='FEATURED'></td>";
            echo "<td><a href='argusarticles.php?event=viewpagepublished&page=featured'>Featured Articles</a></td>";
            echo "<td>".$articlesCount."</td>";
            echo "<td><a href='argusarticles.php?event=published&action=remove&page=featured' title='Remove'><img src='../miscs/images/Default/article_remove.png'></a></td>";
            echo "</tr>";
            echo "</table>";
            
            // display the buttons for managing the front page
            echo "<table width='100%'>";
            echo "<tr>";
            echo "<tr>";
            echo "<input type='submit' id='submit1' value='Remove articles' name='removePublishedPageArticles'> ";
            echo "<input type='submit' id='submit1' value='Remove all articles' name='removeAllPublishedPageArticles'>";
            echo "</tr>";
            echo "</table>";
            echo "</form>";
            echo "</div>";
            
            return;
        }
        
        /**
         * Display Published Category Articles: displays the published articles in a specific category
         * Parameter: $categoryId
         * Return Type: String
         */
        function displayPublishedCategoryArticles($categoryId)
        {
            // query the category name in the database
            $categoryNameQuery = mysql_query("SELECT name FROM argus_categories WHERE category_id = '".$categoryId."' AND status = 'ENABLED'") or die(mysql_error());
            
            // check the result if it exist
            if(mysql_num_rows($categoryNameQuery) > 0)
            {
                // set the category name
                $categoryName = mysql_result($categoryNameQuery,0,"name");
                
                // query all the articles that are in that category ordering them by there position
                $articlesQuery = mysql_query("SELECT article_id, publish_type, title, account_id, date_published, position FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED' ORDER BY position ASC") or die(mysql_error());
                
                // set the title and the form
                echo "<h3><a href='argusarticles.php?event=published'>Published</a> &raquo; ".$categoryName."</h3>";
                echo "<div class='bg1' id='tablePanel'>";
                
                // check if there are queried articles in that category
                if(mysql_num_rows($articlesQuery) == 0)
                {
                    // notify the user that there are no published articles in that category
                    echo "<p><h3 align='center'>There are no published articles in the ".$categoryName." category</h3></p>";
                }
                else
                {
                    // inlcude the TOOL TIP ajax and create a tool tip
                    include("ajax_libraries/ToolTip.php");
                    $toolTip = new ToolTip();
                    $toolTip -> setupForm();
                    
                    // include the checkbox funtions where check box are allowed to be selected/unselected all
                    echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                    
                    // set the form and table where to display the articles
                    echo "
                    <form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=viewcategorypublished&category=".$categoryId."'>
                    <table width='100%'>
                    <tr>
                    <th><input type='checkbox' onClick='toggleCheckBoxes(\"articleIds\")'></th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Type</th>
                    <th>Published</th>
                    <th>Position #</th>
                    <th>Order</th>
                    <th>Action</th>
                    </tr>";
                    
                    // display the articles
                    $color = true;
                    
                    for($i=0; $i < mysql_num_rows($articlesQuery); $i++)
                    {
                        // set the rows in an alternate color
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
                        $title = $this -> limitTitle(stripslashes(mysql_result($articlesQuery,$i,"title")));
                        $authorName = $this -> getAuthorName(mysql_result($articlesQuery, $i,"account_id"));
                        $publishType = mysql_result($articlesQuery,$i,"publish_type");
                        $datePublished = date("m/d/y", mysql_result($articlesQuery,$i,"date_published"));
                        $position = mysql_result($articlesQuery,$i,"position");
                        
                        // display the attributes
                        echo "
                        <td><input type='checkbox' name='articleIds[]' value='".$articleId."'></td>
                        <td><a href='argusarticles.php?event=view&article=".$articleId."'>".$title."</a></td>
                        <td>".$authorName."</td>
                        <td>".$publishType."</td>
                        <td>".$datePublished."</td>
                        <td>";
                        
                        // display the position of the article
                        echo "<select id='textbox' name='positions[]' style='width:50px'>";
                        
                        for($j=0; $j < mysql_num_rows($articlesQuery); $j++)
                        {
                            if($position == $j+1)
                            {
                                // set the position as selected
                                echo "<option value='".$position."' selected='selected'>".($j+1)."</option>";
                            }
                            else
                            {
                                echo "<option value='".($j+1)."'>".($j+1)."</option>";
                            }
                        }
                    
                        echo "</select>";
                        echo "</td>";
                        
                        // display the ordering for the article positions
                        echo "<td>";
                        
                        if($i == 0)
                        {
                            // if this article is at the most top of all categories, then disable the move up link
                            echo "<img src='../miscs/images/Default/move_up.png'> ";
                        }
                        else
                        {
                            // if this article is in between of other categories, enable the move up link
                            echo "<a href='argusarticles.php?event=".$_GET["event"]."&category=".$categoryId."&action=moveup&article=".$articleId."'><img src='../miscs/images/Default/move_up.png'></a> ";
                        }
                        
                        if($i+1 == mysql_num_rows($articlesQuery))
                        {
                            // if this article is at the most bottom of all categories, then disabled the move down link
                            echo "<img src='../miscs/images/Default/move_down.png'>";
                        }
                        else
                        {
                            // if this article is in between of other categories, enable the move down link
                            echo "<a href='argusarticles.php?event=".$_GET["event"]."&category=".$categoryId."&action=movedown&article=".$articleId."'><img src='../miscs/images/Default/move_down.png'></a>";
                        }
                        
                        echo "</td>";
                        
                        // display the actions for managing published articles
                        echo "<td>";
                        echo "<a href='argusarticles.php?event=".$_GET["event"]."&category=".$categoryId."&action=remove&article=".$articleId."' title='Remove'><img src='../miscs/images/Default/article_remove.png'></a> ";
                        echo "</td>";
                        
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                    
                    // display all the buttons for managing the published articles
                    echo "<table width='100%'>";
                    echo "<tr>";
                    echo "<td>";
                    echo "<input type='submit' id='submit1' value='Remove' name='removePublishedArticle'> ";
                    echo "<input type='submit' id='submit1' value='Update positions' name='updatePositions'>";
                    echo "</td>";
                    echo "</tr>";
                    echo "</table>";
                    
                    echo "</form>";
                }
                
                echo "</div>";
            }
            
            return;
        }
        
        /**
         * Display Published Page Articles: displays the published articles that are either FEAUTURED or MAIN
         * Parameter: $pageType
         * Return Type: string
         */
        function displayPublishedPageArticles($pageType)
        {   
            // check the page type if it is valid
            if($pageType == "FEATURED" || $pageType == "MAIN")
            {
                // determine the page type and query the articles
                $articlesQuery = mysql_query("SELECT article_id, title, category_id, account_id, date_published, publish_position FROM argus_articles WHERE status = 'PUBLISHED' AND publish_type = '".$pageType."' ORDER BY publish_position ASC") or die(mysql_error());
                
                // set the title of the page
                echo "<h3><a href='argusarticles.php?event=published'>Published</a> &raquo; ".ucfirst(strtolower($pageType))." articles</h3>";
                echo "<div class='bg1' id='tablePanel'>";
                
                // check if there are articles that was queried based from the parameter page type
                if(mysql_num_rows($articlesQuery) == 0)
                {
                    // notify the user that there are no articles on that page type
                    echo "<p><h3 align='center'>There are no ".$pageType." articles</h3></p>";
                }
                else
                {
                    // inlcude the TOOL TIP ajax and create a tool tip
                    include("ajax_libraries/ToolTip.php");
                    $toolTip = new ToolTip();
                    $toolTip -> setupForm();
                    
                    // include the checkbox funtions where check box are allowed to be selected/unselected all
                    echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                    
                    // set the table of the page where to display all queried articles
                    echo "
                    <form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=viewpagepublished&page=".$pageType."'>
                    <table width='100%'>
                    <tr>
                    <th><input type='checkbox' onClick='toggleCheckBoxes(\"articleIds\")'></th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Published</th>
                    <th class='fix'>Position #</th>
                    <th class='order'>Order</th>
                    <th class='action'>Action</th>
                    </tr>";
                    
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
                        $authorName = $this -> getAuthorName(mysql_result($articlesQuery,$i,"account_id"));
                        $categoryName = $this -> getCategoryName(mysql_result($articlesQuery,$i,"category_id"));
                        $datePublished = date("m/d/y", mysql_result($articlesQuery,$i,"date_published"));
                        $position = mysql_result($articlesQuery,$i,"publish_position"); 
                        
                        // display the attributes
                        echo "<td><input type='checkbox' name='articleIds[]' value='".$articleId."'</td>";
                        echo "<td><a href='argusarticles.php?event=view&article=".$articleId."'>".$title."</a></td>";
                        echo "<td>".$authorName."</td>";
                        echo "<td>".$categoryName."</td>";
                        echo "<td>".$datePublished."</td>";
                        
                        // set the position
                        echo "<td>";
                        echo "<select id='textbox' name='positions[]' style='width:50px'>";
                        
                        for ($j=0; $j<mysql_num_rows($articlesQuery); $j++)
                        {
                            if($j+1 == $position)
                            {
                                // set the position as selected
                                echo "<option value='".($j+1)."' selected='selected'>".$position."</option>";
                            }
                            else
                            {
                                echo "<option value='".($j+1)."'>".($j+1)."</option>";
                            }
                        }
                        
                        echo "</select>";
                        echo "</td>";
                        
                        // display the ordering for the article positions
                        echo "<td>";
                        
                        if($i == 0)
                        {
                            // if this article is at the most top of all categories, then disable the move up link
                            echo "<img src='../miscs/images/Default/move_up.png'> ";
                        }
                        else
                        {
                            // if this article is in between of other categories, enable the move up link
                            echo "<a href='argusarticles.php?event=".$_GET["event"]."&page=".strtolower($pageType)."&action=moveup&article=".$articleId."'><img src='../miscs/images/Default/move_up.png'></a> ";
                        }
                        
                        if($i+1 == mysql_num_rows($articlesQuery))
                        {
                            // if this article is at the most bottom of all categories, then disabled the move down link
                            echo "<img src='../miscs/images/Default/move_down.png'>";
                        }
                        else
                        {
                            // if this article is in between of other categories, enable the move down link
                            echo "<a href='argusarticles.php?event=".$_GET["event"]."&page=".strtolower($pageType)."&action=movedown&article=".$articleId."'><img src='../miscs/images/Default/move_down.png'></a>";
                        }
                        
                        echo "</td>";
                        
                        // display the actions for managing published articles
                        echo "<td>";
                        echo "<a href='argusarticles.php?event=".$_GET["event"]."&page=".strtolower($pageType)."&action=remove&article=".$articleId."' title='Remove'><img src='../miscs/images/Default/article_remove.png'></a> ";
                        echo "</td>";
                        
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                    
                    // display all the buttons for managing the published articles
                    echo "<table width='100%'>";
                    echo "<tr>";
                    echo "<td>";
                    echo "<input type='submit' id='submit1' value='Remove' name='removePublishedArticle'> ";
                    echo "<input type='submit' id='submit1' value='Update positions' name='updatePositions'>";
                    echo "</td>";
                    echo "</tr>";
                    echo "</table>";
                    
                    echo "</form>";
                }
                
                echo "</div>";
            }
            
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
         * Get Issue Name method: returns what issue that article
         * Parameter: $issueId
         * Return type: string
         */
        function getIssueName($issueId)
        {
            // include the Name retriever class and create name retreiver for issues
            require_once("class_libraries/NameRetriever.php");
            $nameRetriever = new NameRetriever("issue_id");
            
            // get the name of the issue and then return in
            return $nameRetriever -> getName($issueId);
        }
        
        /**
         * Get Category Name method: returns the category name of the article
         * Parameter: $categoryId
         * Return Type: string
         */
        function getCategoryName($categoryId)
        {
            // include the name retriever class and create a category name retriever
            require_once("class_libraries/NameRetriever.php");
            $nameRetriever = new NameRetriever("category_id");
            
            // get the category name and return it
            return $nameRetriever -> getName($categoryId);
        }
        
        /**
         * Get Date Modified method: returns when the article was last modified
         * Parameter: $timeStamp
         * Return type: string
         */
        function getDateModified($timeStamp)
        {
            // check if the time stamp is empty
            if(empty($timeStamp))
            {
                // set the article to UNMODIFIED
                return "UNMODIFIED";
            }
            else
            {
                // set the timestamp into a date format
                return date("m/d/y", $timeStamp);
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
         * Reject Article method: rejects an article sending the article to the rejected section
         * Parameter: $articleId
         */
        function rejectArticle($articleId)
        {
            // reject the article setting also it's ISSUE to 0
            mysql_query("UPDATE argus_articles SET status = 'REJECTED', issue_id = '0' WHERE article_id = '".$articleId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * View Article Method: views the selected article
         * Parameter: $articleId
         * Return type: string
         */
        function viewArticle($articleId)
        {
            // query the article from the database
            $articleQuery = mysql_query("SELECT account_id, category_id, issue_id, title, content, date_submitted, publish_type, status FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
        
            // check if there is an article queried from the database
            if(mysql_num_rows($articleQuery) > 0)
            {
                // set the attributes
                $authorName = $this -> getAuthorName(mysql_result($articleQuery,0,"account_id"));
                $categoryId = mysql_result($articleQuery,0,"category_id");
                $categoryName = $this -> getCategoryName($categoryId);
                $issueId = mysql_result($articleQuery,0,"issue_id");
                $issueName = $this -> getIssueName($issueId);
                $title = mysql_result($articleQuery,0,"title");
                $content = mysql_result($articleQuery,0,"content");
                $dateSubmitted = date("F d, Y", mysql_result($articleQuery,0,"date_submitted"));
                $publishType = mysql_result($articleQuery,0,"publish_type");
                $status = mysql_result($articleQuery,0,"status");
                
                // check if the issue has been set
                if($issueId == 0)
                {
                    // set the title for articles that there issue has not been set
                    echo "
                    <h3><a href='argusarticles.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo ".$title."</h3>";
                }
                // check the status of the articl if it is published
                else if($status == 'PUBLISHED')
                {
                    // set the title for the articles that there categories that has not been set
                    echo "<h3><a href='argusarticles.php?event=published'>Published</a> &raquo; ".$title."</h3>";
                }
                else
                {
                    // set the title for articles that there issues has been set
                    echo "
                    <h3><a href='argusarticles.php?event=issues'>Issues</a> &raquo <a href='argusarticles.php?event=viewissue&issue=".$issueId."'>".$issueName."</a> &raquo ".$title."</h3>";
                }
                
                echo "<div class='bg1'>";
                
                // display the attributes
                echo "
                <p>
                    Article Information
                </p>
                <p id='box'>
                Title : ".$title."<br>
                Category : ".$categoryName."<br>
                Issue : ".$issueName."<br>
                Author : ".$authorName."<br>
                Date Submitted : ".$dateSubmitted."<br>
                Publish Type : ".$publishType."<br>
                Status : ".$status."<br>
                </p>";
                
                // determine the status of the article so as to know what button is to set for managing the article
                echo "<p align='right'>";
                
                if($status == "PENDING")
                {
                    // buttons for pending
                    echo "
                    <a href='argusarticles.php?event=".strtolower($status)."&action=approve&article=".$articleId."'><input type='button' id='submit1' value='Approve'></a> 
                    <a href='argusarticles.php?event=".strtolower($status)."&action=reject&article=".$articleId."'><input type='button' id='submit1' value='Reject'></a>";
                }
                else if($status == "APPROVED")
                {
                    // buttons for approved
                    echo "
                    <a href='argusarticlesedit.php?article=".$articleId."'><input type='button' id='submit1' value='edit'></a> ";
                    
                    // check if the approved article has an issue
                    // the publish button is only provided to articles who doesnt have any issue
                    if($issueId == 0)
                    {
                        echo "<a href='argusarticles.php?event=".strtolower($status)."&action=publish&article=".$articleId."'><input type='button' id='submit1' value='Publish'></a> ";
                    }
                    else
                    {
                        // display the remove from issue button
                        echo "<a href='argusarticles.php?event=viewissue&issue=".$issueId."&action=remove&article=".$articleId."'><input type='button' id='submit1' value='Remove'></a> ";
                    }
                    
                    echo "<a href='argusarticles.php?event=".strtolower($status)."&action=reject&article=".$articleId."'><input type='button' id='submit1' value='Reject'></a>";
                }
                else if($status == "REJECTED")
                {
                    // buttons for rejected
                    echo "
                    <a href='argusarticles.php?event=".strtolower($status)."&action=approve&article=".$articleId."'><input type='button' id='submit1' value='Approve'></a> 
                    <a href='argusarticles.php?event=".strtolower($status)."&action=delete&article=".$articleId."'><input type='button' id='submit1' value='Delete'></a>";
                }
                else if($status == "PUBLISHED")
                {
                    echo "
                    <a href='argusarticles.php?event=viewcategorypublished&category=".$categoryId."&action=remove&article=".$articleId."'><input type='submit' id='submit1' value='Remove'</a>";
                }
                
                echo "</p>";
                
                // display the content
                echo "<div>".$content."</div>";
                
                echo "</div>";
            }
            
            return;
        }
        
        /**
         * Set Article Issues method: sets the issue of the article
         * Parameter: $issueId, $issueIds
         */
        function setArticleIssues($issueId, $issueIds)
        {
            // query all the articles that are in that ISSUE ID and sort them by Date Modified in descending order
            // where the most top article is the latest article that was modified
            $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE issue_id = '".$issueId."' ORDER BY date_modified DESC") or die(mysql_error());
            
            // update the articles
            for($i=0; $i < count($issueIds); $i++)
            {
                $articleId = mysql_result($articlesQuery,$i,"article_id");
                mysql_query("UPDATE argus_articles SET issue_id = '".$issueIds[$i]."' WHERE article_id = '".$articleId."'") or die(mysql_error());
            }
            
            return;
        }
        
        /**
         * Save Article method: saves the edited article
         * Parameter: $articleId, $title, $categoryId, $issueId, $into, $content, $publishType
         * Return Type: boolean
         */
        function saveArticle($articleId, $title, $categoryId, $issueId, $intro, $content, $publishType)
        {
            // escape the characters that are needed to be escaped to avoid sql injection
            $title = mysql_escape_string($title);
			$content = mysql_escape_string($content);
			$intro = mysql_escape_string($intro);
            
            // validate the title
            $titleError = $this -> validateTitle($title);
            
            // check if validation has passed
            if($titleError == null)
            {
                // if validation has succeeded, update the article from the database
                mysql_query("UPDATE argus_articles SET title='".$title."', category_id='".$categoryId."', issue_id='".$issueId."', intro='".$intro."', content='".$content."', date_modified='".time()."', publish_type='".$publishType."' WHERE article_id = '".$articleId."' AND status = 'APPROVED'") or die(mysql_error());
                
                // return successful save
                return true;
            }
            else
            {
                // if validation has failed, set the errors which will be soon received when needed
                $this -> errors = array("title" => $titleError);
                
                // return unsuccessful save
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
         * Publish Issue method: publishes all the articles that is found in that issue
         * Parameter: $issueId
         */
        function publishIssue($issueId)
        {
            // check if the issue being passed as a parameter exists in the database
            $issueQuery = mysql_query("SELECT issue_id FROM argus_issues WHERE issue_id = '".$issueId."' AND status = 'ENABLED'") or die(mysql_error());
            
            if(mysql_num_rows($issueQuery) > 0)
            {
                // if the issue exist and available, query all the articles that is in that issue for publication
                $articlesQuery = mysql_query("SELECT article_id, category_id FROM argus_articles WHERE issue_id = '".$issueId."' AND status = 'APPROVED'") or die(mysql_error());
                
                for($i=0; $i<mysql_num_rows($articlesQuery); $i++)
                {
                    $articleId = mysql_result($articlesQuery,$i,"article_id");
                    $categoryId = mysql_result($articlesQuery,$i,"category_id");
                    
                    // publish the article
                    $this -> publishArticle($articleId);
                }
                
                // set the previously published issue as ENABLED
                mysql_query("UPDATE argus_issues SET status = 'ENABLED' WHERE status = 'PUBLISHED'") or die(mysql_error());
                    
                // then set the issue to be published as PUBLISHED
                mysql_query("UPDATE argus_issues SET status = 'PUBLISHED' WHERE issue_id = '".$issueId."'") or die(mysql_error());
            }
            
            return;
        }
        
        /**
         * Publish Article: publishes an article
         * Parameter: $articleId
         */
        function publishArticle($articleId)
        {
            // check the category of the article
            $categoryQuery = mysql_query("SELECT category_id FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
            $categoryId = mysql_result($categoryQuery,0,"category_id");
            
            // query the status of the category
            $statusQuery = mysql_query("SELECT status FROM argus_categories WHERE category_id = '".$categoryId."'") or die(mysql_error());
            $status = mysql_result($statusQuery,0,"status");
            
            if($status == "DISABLED")
            {
                // force the category to be enabled if the category of the article being published is disabled
                mysql_query("UPDATE argus_categories SET status = 'ENABLED' WHERE category_id = '".$categoryId."' AND STATUS = 'DISABLED'") or die(mysql_error());
            }
            
            // count how many articles have already been published in that category then set the position
            $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED'") or die(mysql_error());
            $articlesCount = mysql_num_rows($articlesQuery);
            $position = $articlesCount + 1;
            
            // query the publish type of the article if it is MAIN or FEATURED or NONE
            // at the same time query also the issue of the article to check if the article has an issue or none
            $articleQuery = mysql_query("SELECT publish_type, issue_id FROM argus_articles WHERE article_id = '".$articleId."'") or die(mysql_error());
            
            // check if the article was queried
            if(mysql_num_rows($articleQuery) > 0)
            {
                // set the publish type
                $publishType = mysql_result($articleQuery,0,"publish_type");
                $issueId = mysql_result($articleQuery,0,"issue_id");
                
                // check the publish type
                if($publishType == "MAIN")
                {
                    // query the number of MAIN published articles
                    $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE publish_type = 'MAIN' AND status = 'PUBLISHED'") or die(mysql_error());
                    $articlesCount = mysql_num_rows($articlesQuery);
                    
                    // set the position of the article that is to be published
                    $publishPosition = $articlesCount + 1;
                }
                else if($publishType == "FEATURED")
                {
                    // query the number of FEATURED published articles
                    $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE publish_type = 'FEATURED' AND status = 'PUBLISHED'") or die(mysql_error());
                    $articlesCount = mysql_num_rows($articlesQuery);
                    
                    // set the position of the articke that is to be published
                    $publishPosition = $articlesCount + 1;
                }
                
                // check if the article has an issue id
                // articles that does not have any issue are assumed to have an issue id of "ZERO"
                if($issueId == 0)
                {
                    // if the article does not have any issue id and is being published, query the current
                    // issue which will be used
                    $currentIssueIdQuery = mysql_query("SELECT issue_id FROM argus_issues WHERE status = 'PUBLISHED'") or die(mysql_error());
                    $currentIssueId = mysql_result($currentIssueIdQuery,0,"issue_id");
                    
                    // set the issue id as the current issue id
                    $issueId = $currentIssueId;
                }
            }
            
            // update the issue of the article that is going to be published and at the same time publish the article
            mysql_query("UPDATE argus_articles SET status = 'PUBLISHED', issue_id = '".$issueId."', date_published='".time()."', position = '".$position."', publish_position = '".$publishPosition."' WHERE article_id = '".$articleId."' AND status = 'APPROVED'") or die(mysql_error());
            
            // set the rating 0
            mysql_query("INSERT INTO argus_article_ratings(article_id, total_votes, total_value) VALUES('".$articleId."','0','0')") or die(mysql_error());
            
            // set the hits to 0
            mysql_query("INSERT INTO argus_article_hits(article_id, hits) VALUES('".$articleId."','0')") or die(mysql_error());
            
            // archive article after published
            $this -> archiveArticle($articleId);
            
            return;
        }
        
        /**
         * Move Article Method: this moves the article UP or DOWN fixing the positions
         * Parameter: $articleId, $action
         */
        function moveArticle($articleId, $action)
        {
            // query the category of the article
            $categoryIdQuery = mysql_query("SELECT category_id FROM argus_articles WHERE article_id = '".$articleId."' AND status = 'PUBLISHED'") or die(mysql_error());
            $categoryId = mysql_result($categoryIdQuery,0,"category_id");
            
            // query all the published articles in that specific category id and arrange them by position
            $articlesQuery = mysql_query("SELECT article_id, position FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED' ORDER BY position ASC") or die(mysql_error());
                           
            // search the parameter article ID from the queried articles
            for($i=0; $i < mysql_num_rows($articlesQuery); $i++)
            {
                // if the article id has been matched and found, set the article for exchanging position
                if(mysql_result($articlesQuery,$i,"article_id") == $articleId)
                {
                    // set the article id and the position of the article that is on the top of the current article
                    if($i - 1 >= 0)
                    {
                        $topArticleId = mysql_result($articlesQuery,$i-1,"article_id");
                        $topArticlePosition = mysql_result($articlesQuery,$i-1,"position");
                    }
                    
                    // set the position of the current article
                    $position = mysql_result($articlesQuery,$i,"position");
                    
                    // set the article id and the position of the article that is on the bottom of the current article
                    if($i+1 < mysql_num_rows($articlesQuery))
                    {
                        $bottomArticleId = mysql_result($articlesQuery,$i+1,"article_id");
                        $bottomArticlePosition = mysql_result($articlesQuery,$i+1,"position");
                    }
                }
            }
            
            // check the action which action to perform
            if($action == "MOVEUP")
            {
                // switch the current position of the article with the position of the article that is on the top
                mysql_query("UPDATE argus_articles SET position='".$topArticlePosition."' WHERE article_id='".$articleId."' AND status='PUBLISHED'") or die(mysql_error());
                
                // switch the article's position at the top with the current position of the article
                mysql_query("UPDATE argus_articles SET position='".$position."' WHERE article_id='".$topArticleId."' AND status='PUBLISHED'") or die(mysql_error());
            } 
            else
            {
                // switch the current position of the article with the position of the article that is on the bottom
                mysql_query("UPDATE argus_articles SET position='".$bottomArticlePosition."' WHERE article_id='".$articleId."' AND status='PUBLISHED'") or die(mysql_error());
                
                // switch the article's position at the bottom with the current position of the article
                mysql_query("UPDATE argus_articles SET position='".$position."' WHERE article_id='".$bottomArticleId."' AND status='PUBLISHED'") or die(mysql_error());
            }
            
            return;
        }
        
        /**
         * Move Front Page Article method: this moves the article UP or DOWN fixing the position of frontpage articles
         * Parameter: $articleId, $action
         */
        function moveFrontPageArticle($articleId, $action)
        {
            // query the publish type of the article
            $publishTypeQuery = mysql_query("SELECT publish_type FROM argus_articles WHERE article_id = '".$articleId."' AND status = 'PUBLISHED'") or die(mysql_error());
            $publishType = mysql_result($publishTypeQuery,0,"publish_type");
            
            // query all the published articles in that specific publish type and arrange them by position
            $articlesQuery = mysql_query("SELECT article_id, publish_position FROM argus_articles WHERE publish_type = '".$publishType."' AND status = 'PUBLISHED' ORDER BY publish_position ASC") or die(mysql_error());
                           
            // search the parameter article ID from the queried articles
            for($i=0; $i < mysql_num_rows($articlesQuery); $i++)
            {
                // if the article id has been matched and found, set the article for exchanging position
                if(mysql_result($articlesQuery,$i,"article_id") == $articleId)
                {
                    // set the article id and the position of the article that is on the top of the current article
                    if($i - 1 >= 0)
                    {
                        $topArticleId = mysql_result($articlesQuery,$i-1,"article_id");
                        $topArticlePosition = mysql_result($articlesQuery,$i-1,"publish_position");
                    }
                    
                    // set the position of the current article
                    $position = mysql_result($articlesQuery,$i,"publish_position");
                    
                    // set the article id and the position of the article that is on the bottom of the current article
                    if($i+1 < mysql_num_rows($articlesQuery))
                    {
                        $bottomArticleId = mysql_result($articlesQuery,$i+1,"article_id");
                        $bottomArticlePosition = mysql_result($articlesQuery,$i+1,"publish_position");
                    }
                }
            }
            
            // check the action which action to perform
            if($action == "MOVEUP")
            {
                // switch the current position of the article with the position of the article that is on the top
                mysql_query("UPDATE argus_articles SET publish_position='".$topArticlePosition."' WHERE article_id='".$articleId."' AND status='PUBLISHED'") or die(mysql_error());
                
                // switch the article's position at the top with the current position of the article
                mysql_query("UPDATE argus_articles SET publish_position='".$position."' WHERE article_id='".$topArticleId."' AND status='PUBLISHED'") or die(mysql_error());
            } 
            else
            {
                // switch the current position of the article with the position of the article that is on the bottom
                mysql_query("UPDATE argus_articles SET publish_position='".$bottomArticlePosition."' WHERE article_id='".$articleId."' AND status='PUBLISHED'") or die(mysql_error());
                
                // switch the article's position at the bottom with the current position of the article
                mysql_query("UPDATE argus_articles SET publish_position='".$position."' WHERE article_id='".$bottomArticleId."' AND status='PUBLISHED'") or die(mysql_error());
            }
        }
        
        /**
         * Update Published Positions Method: updates the positions of the article in sequence
         * Parameter: $categoryId, $positions
         */
        function updatePublishedPositions($categoryId, $positions)
        {
            // include the position validate class and validate the position
            include("class_libraries/PositionValidator.php");
            $positionValidator = new PositionValidator();
            
            // validate the positions
            $result = $positionValidator -> validatePosition($positions);
            
            // check the result of the validation
            if($result == true)
            {
                // proceed only when the validation is successful            
                // query published articles in a specific category ordering them by there  position in an ascending order
                $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED' ORDER BY position ASC") or die(mysql_error());
                
                for($i=0; $i<mysql_num_rows($articlesQuery); $i++)
                {
                    $articleId = mysql_result($articlesQuery,$i,"article_id");
                    mysql_query("UPDATE argus_articles SET position = '".$positions[$i]."' WHERE article_id = '".$articleId."'") or die(mysql_error());
                }
            }
                        
            return;
        }
        
        /**
         * Remove Published Page Articles method: removes articles from the front page
         * $publishType
         */
        function removePublishedPageArticles($publishType)
        {
            // query all published articles depending on the parameter publish type
            $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE status='PUBLISHED' AND publish_type = '".$publishType."'") or die(mysql_error());
            
            // delete all the articles
            for($i=0; $i<mysql_num_rows($articlesQuery); $i++)
            {
                // delete the comments of each articles
                $this -> deleteComments(mysql_result($articlesQuery,$i,"article_id"));
                
                // delete the article detail
                $this -> deleteArticleDetails(mysql_result($articlesQuery,$i,"article_id"));
            }
            
            // after deleting all the comments, remove the published articles on that publish type
            mysql_query("UPDATE argus_articles SET status = 'APPROVED', issue_id = '0' WHERE status = 'PUBLISHED' AND publish_type = '".$publishType."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Remove all published page articles method: removes all published articles that are in the front page
         */
        function removeAllPublishedPageArticles()
        {   
            // query all feautured or main articles
            $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE status = 'PUBLISHED' and publish_type = 'MAIN' OR publish_type = 'FEATURED'") or die(mysql_error());
         
            // for each articles, delete all there comments
            for($i=0; $i<mysql_num_rows($articlesQuery); $i++)
            {
                // delete the comments of the article
                $this -> deleteComments(mysql_result($articlesQuery,$i,"article_id"));
                
                // delete the article details
                $this -> deleteArticleDetails(mysql_result($articlesQuery,$i,"article_id"));
            }
         
            // remove all front page articles
            mysql_query("UPDATE argus_articles SET status='APPROVED', issue_id='0' WHERE status = 'PUBLISHED' and publish_type = 'MAIN' OR publish_type = 'FEATURED'") or die(mysql_error());   
            
            return;
        }
        
        /**
         * Remove Issued Articles method: removes articles in a specific issue
         * Parameter: $issueId
         */
        function removeIssuedArticles($issueId)
        {
            // remove the articles in the given issue id parameter
            mysql_query("UPDATE argus_articles SET issue_id = '0' WHERE issue_id = '".$issueId."' AND status = 'APPROVED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Remove Issued Article method: removes a specific article that has an issue
         * Parameter: $articleId
         */
        function removeIssuedArticle($articleId)
        {
            // remove the article from the issue
            mysql_query("UPDATE argus_articles SET issue_id = '0' WHERE article_id = '".$articleId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Remove All Issued Articles method: removes all articles that has an issue
         */
        function removeAllIssuedArticles()
        {
            // update the articles issue
            mysql_query("UPDATE argus_articles SET issue_id = '0' WHERE status = 'APPROVED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Remove Published Articles method: removes published articles in a specific category, setting there issue back to default also
         * Parameter: $categoryId
         */
        function removePublishedCategoryArticles($categoryId)
        {
            // query all the publihsed articles in that category
            $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED'") or die(mysql_error());
            
            // for each article, delete there comments
            for($i=0; $i<mysql_num_rows($articlesQuery); $i++)
            {
                // delete the comments
                $this -> deleteComments(mysql_result($articlesQuery,$i,"article_id"));
                
                // delete the article details
                $this -> deleteArticleDetails(mysql_result($articlesQuery,$i,"article_id"));
            }
            
            // update the articles in that category
            mysql_query("UPDATE argus_articles SET status = 'APPROVED', issue_id = '0' WHERE category_id = '".$categoryId."' AND status = 'PUBLISHED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Remove Published Article method: Removes a specific article from the published section
         * Parameter: $articleId
         */
        function removePublishedArticle($articleId)
        {
            // before removing the article, remove the comments that are attached to it
            $this -> deleteComments($articleId);
            
            // also delete the details of the article
            $this -> deleteArticleDetails($articleId);
            
            // remove the article from the published section
            mysql_query("UPDATE argus_articles SET status = 'APPROVED', issue_id = '0' WHERE article_id = '".$articleId."' AND status = 'PUBLISHED'") or die(mysql_error());
        }
        
        /**
         * Remove All Published Articles method: removes all published articles
         */
        function removeAllPublishedArticles()
        {
            // query all the publihsed articles
            $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE status = 'PUBLISHED'") or die(mysql_error());
            
            // for each article, delete there comments
            for($i=0; $i<mysql_num_rows($articlesQuery); $i++)
            {
                // delete the comments
                $this -> deleteComments(mysql_result($articlesQuery,$i,"article_id"));
                
                // delete the article details
                $this -> deleteArticleDetails(mysql_result($articlesQuery,$i,"article_id"));
            }
            
            // remove all published articles setting there issue to default
            mysql_query("UPDATE argus_articles SET status = 'APPROVED', issue_id = '0' WHERE status = 'PUBLISHED'") or die(mysql_error());
        
            return;
        }
        
        /**
         * Archive Article method: creates an archive of an article
         * Parameter: $articleId
         */
        function archiveArticle($articleId)
        {
            // query the article contents from the database
            $articleQuery = mysql_query("SELECT account_id, category_id, issue_id, title, intro, content, date_published FROM argus_articles WHERE article_id = '".$articleId."' AND status = 'PUBLISHED'") or die(mysql_error());
            
            // set the attributes
            $author = $this -> getAuthorName(mysql_result($articleQuery,0,"account_id"));
            $category = $this -> getCategoryName(mysql_result($articleQuery,0,"category_id"));
            $issue = $this -> getIssueName(mysql_result($articleQuery,0,"issue_id"));
            $title = mysql_result($articleQuery,0,"title");
            $intro = mysql_result($articleQuery,0,"intro");
            $content = mysql_result($articleQuery,0,"content");
            $datePublished = date("F d, Y", mysql_result($articleQuery,0,"date_published"));
            
            // the file name is the article id with an extension of .HTML
            $fileName = $articleId.".html";
            
            // set directory attributes
            $year = date("Y", mysql_result($articleQuery,0,"date_published"));
            $yearDirectory = "../archives/".$year."/";
            $issueDirectory = "../archives/".$year."/".$issue."/";
            $imageDirectory = "../archives/".$year."/".$issue."/images/";
            
            // check if the directory for the year exists
            if(!file_exists($yearDirectory))
            {
                // if the directory does not exist, create the directory
                mkdir($yearDirectory);
            }
            
            // check if the directory for the issue exists
            if(!file_exists($issueDirectory))
            {
                // if the issue does not exists, create the directory
                mkdir($issueDirectory);
            }
            
            // check if the archive image for the issue exists
            if(!file_exists($imageDirectory))
            {
                // if the image directory does not exist, create the directory
                mkdir($imageDirectory);
            }
            
            // before the content is going to be added to the HTML content, archive the images of the article if any
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
                
                $oldPath = "../images/server/".$imageName;
                
                // there are instances that the image isn't in the server folder
                // check if the image exists in the image server
                if(!file_exists($oldPath))
                {
                    // if the file does not exist then find it in the client folder
                    $oldPath = "../images/client/".$imageName;
                }
                
                $newPath = $imageDirectory.$imageName;
                
                // copy the image and transfer it to another folder
                copy($oldPath, $newPath);
                
                // after copying the image, replace the content path from old path to new path
                $content = str_replace($oldPath, "images/".$imageName, $content);
            }
                        
            $htmlContent = "            
            <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
            <html xmlns='http://www.w3.org/1999/xhtml'>
            <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
            <title>Argus Archives | College of Information and Computing Sciences</title>
            <link href='../../../miscs/css/default.css' rel='stylesheet' type='text/css'>
            </head>
            <div id='header'>
            <h1>Argus Archives</h1>
            <h2>College of Information and Computing Sciences</h2>
            </div>
            <div id='content'>
            <div id='colTwo' style='width:892px'>
            <div class='bg2'>
            <h2><em>".$issue."</em></h2>
            <p>
            <strong>Argus</strong> Welcome to Argus Portal! This is a thesis project made by the argus team (Silvyster Abing, Kleine Bahatan, Brian Keith Ong, Redford Sumcad, Bjoern Groenberg, Michael Madayag, and Danilo Villanueva. Argus online publications publishes articles monthly. Argus online publication has members, contributors, and administrators. This is a page banner and can be edited by the administrators once the application set-up is complete.
            </p>
            </div>
            <h3>".$category."</h3>
            <div class='bg1'>
            <div id='article'>
            <h2>".$title."</h2>
            <p>".$content."</p>
            <p class='post-footer' align='right'>
            ".$author."
            <span class='date'>".$datePublished."</span>
            </p>
            </div>
            </div>
            </div>
            </div>
            <div id='footer'>
            <p>powered by argus</p>
            </div>
            </html>";
            
            // write the HTML content into the status HTML file
            $fileHandle = fopen($issueDirectory.$fileName, w);
            fwrite($fileHandle, $htmlContent);
            fclose($fileHandle);
            
            // after writing the static HTML, update the archives from the database
            // before updating the archives.. check whether it already exists in the database or not
            $archiveQuery = mysql_query("SELECT archive_id FROM argus_archives WHERE article_id = '".$articleId."'") or die(mysql_error());
            
            // check if the archive already exists
            if(mysql_num_rows($archiveQuery) == 0)
            {
                // create new archive
                mysql_query("INSERT INTO argus_archives(article_id, title, issue,year,date_archived,status,path)
                VALUES ('".$articleId."','".$title."','".$issue."','".$year."','".time()."','ENABLED','".$issueDirectory.$fileName."')") or die(mysql_error());
            }

            return;
        }
        
        /**
         * Limit Title method: limits the title
         * Parameter: $title
         * Return Type: string
         */
        function limitTitle($title)
        {
            // include the text limiter class and limit the words to 5 only
            require_once("class_libraries/TextLimiter.php");
            $textLimiter = new TextLimiter();
            
            // limit the title
            $title = $textLimiter -> limitText($title, 4);
            
            // return the truncated title
            return $title;
        }
    
        /**
         * Search Article method: searches the article
         * Parameter: $articleKeyword, $articleSearchType
         * Return type: string
         */
        function searchArticle($articleKeyword, $articleSearchType)
        {
            // check if the article keyword has a value
            if(trim($articleKeyword) == "")
            {
                // return and do not process anything
                return;
            }
            
            // determine the article search type
            if($articleSearchType == "byTitle")
            {
                // query all articles that has the same title
                $articlesQuery = mysql_query("SELECT article_id, title, account_id, date_submitted, date_modified, status FROM argus_articles WHERE title LIKE '%".$articleKeyword."%'") or die(mysql_error());
            }
            else
            {
                // query on the accounts based on the given author keyword
                $accountIdQuery = mysql_query("SELECT account_id FROM argus_accounts WHERE name LIKE '%".$articleKeyword."%' AND position = 'CONTRIBUTOR'") or die(mysql_error());
                
                if(mysql_num_rows($accountIdQuery) > 0)
                {
                    // set the account id
                    $accountId = mysql_result($accountIdQuery,0,"account_id");
                }
                
                // query all articles that has the author
                $articlesQuery = mysql_query("SELECT article_id, title, account_id, date_submitted, date_modified, status FROM argus_articles WHERE account_id = '".$accountId."'") or die(mysql_error());
            }
            
            // set the title of the page
            echo "<h3>Search Result</h3>";
            echo "<div class='bg1' id='tablePanel'>";
            
            // check if there are results
            if(mysql_num_rows($articlesQuery) == 0)
            {
                // notify the user that there are no search result
                echo "<p><h3 align='center'>There are no search results</h3></p>";
            }
            else
            {
                // else display the searches found in a table form
                echo "<table width='100%'>";
                echo "<tr>";
                echo "<th align='center'>Title</th>";
                echo "<th>Author</th>";
                echo "<th>Date Submitted</th>";
                echo "<th>Date Modified</th>";
                echo "<th>Status</th>";
                echo "</tr>";
                
                // display the searches
                $color = true;
                
                for($i=0; $i<mysql_num_rows($articlesQuery); $i++)
                {
                    // display the table rows in an alternate color manner
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
                    $articleId = mysql_result($articlesQuery,$i,"article_id");
                    $title = $this -> limitTitle(mysql_result($articlesQuery,$i,"title"));
                    $author = $this -> getAuthorName(mysql_result($articlesQuery,$i,"account_id"));
                    $dateSubmitted = date("m/d/y", mysql_result($articlesQuery,$i,"date_submitted"));
                    $dateModified = $this -> getDateModified(mysql_result($articlesQuery,$i,"date_modified"));
                    $status = mysql_result($articlesQuery,$i,"status");
                    
                    // display the attributes
                    echo "<td align='center'><a href='argusarticles.php?event=view&article=".$articleId."'>".$title."</a></td>";
                    echo "<td>".$author."</td>";
                    echo "<td>".$dateSubmitted."</td>";
                    echo "<td>".$dateModified."</td>";
                    echo "<td>".$status."</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            }
            
            echo "</div>";
            
            return;
        }
    }
?>