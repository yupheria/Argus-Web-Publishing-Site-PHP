<?php
	/**
	 * Filename	: CommentsForm.php
	 * Description	: contains functions and properties of comments
	 * Date Created	: December 10,2007
	 * Author	: Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
     *  displayBanner()
     *  displayComments($status)
     *  string getAuthorName($accountId)
     *  string getArticleTitle($articleId)
     *  void approveComment($commentId)
     *  void rejectComment($commentId)
     *  void deleteComment($commentId)
     *  void deleteAllComments()
     *  string viewComment($commentId)
     *  string stripTags($comment)
	 */
	
	class CommentsForm
	{
        /**
         * Display Banner Method: displays the menus for managing comments
         * Return type: String
         */
        function displayBanner()
        {
            echo "
            <div class='bg2'>
            <h2><em>Comments Manager</em></h2>
            <p align='center'>";
            
            // menus
            echo "
            <a href='comments.php'>Pending</a> . 
            <a href='comments.php?event=approved'>Approved</a> . 
            <a href='comments.php?event=rejected'>Rejected</a>";
            
            echo "
            </p>
            </div>";
            
            return;
        }
        
        /**
         * Display Comments Method: displays the comments of articles depeding on the passed parameter
         * Parameter: $status
         * Return Type: String
         */
        function displayComments($status)
        {
            // query the comments from the database
            $commentsQuery = mysql_query("SELECT comment_id, account_id, article_id, date_commented FROM argus_comments WHERE status = '".$status."'") or die(mysql_error());

            // set the title
            echo "
            <h3>".ucfirst(strtolower($status))."</h3>
            <div class='bg1' id='tablePanel'>";
            
            // check if there are comments queried
            if(mysql_num_rows($commentsQuery) == 0)
            {
                // notify the user that there are no comments in that status
                echo "<p><h3 align='center'>There are no ".$status." comments</h3></p>";
            }
            else
            {
                // inlcude the TOOL TIP ajax and create a tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                // include the checkbox funtions where check box are allowed to be selected/unselected all
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
                // set the form and table where to display the comments
                echo "
                <form id='form_id' method='post' action='".$_SERVER["PHP_SELF"]."?event=".strtolower($status)."'>
                <table width='100%'>
                <tr>
                <th><input type='checkbox' onClick='toggleCheckBoxes(\"commentIds\")'></th>
                <th>Commentator</th>
                <th>Article</th>
                <th>Date Commented</th>
                <th>Comment</th>
                <th>Action</th>
                </tr>";
                
                // display the comments
                $color == true;
                
                for($i=0; $i<mysql_num_rows($commentsQuery); $i++)
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
                    $commentId = mysql_result($commentsQuery,$i,"comment_id");
                    $authorName = $this -> getAuthorName(mysql_result($commentsQuery,$i,"account_id"));
                    $articleTitle = $this -> getArticleTitle(mysql_result($commentsQuery,$i,"article_id"));
                    $dateCommented = date("m/d/y", mysql_result($commentsQuery,$i,"date_commented"));
                    
                    // display the attributes
                    echo "
                    <td><input type='checkbox' name='commentIds[]' value='".$commentId."'></td>
                    <td>".$authorName."</td>
                    <td>".$articleTitle."</td>
                    <td>".$dateCommented."</td>
                    <td><a href='comments.php?event=viewcomment&comment=".$commentId."'>view comment</a></td>
                    <td>";
                    
                    // set the actions
                    if($status == "PENDING")
                    {
                        // actions for pending articles
                        echo "
                        <a href='comments.php?event=".strtolower($status)."&action=approve&comment=".$commentId."' title='Approve'><img src='../miscs/images/Default/article_approve.png'></a> 
                        <a href='comments.php?event=".strtolower($status)."&action=reject&comment=".$commentId."' title='Reject'><img src='../miscs/images/Default/article_trash.png'></a>";
                    }
                    else if($status == "APPROVED")
                    {
                        // actions for approved articles
                        echo "
                        <a href='comments.php?event=".strtolower($status)."&action=reject&comment=".$commentId."' title='Reject'><img src='../miscs/images/Default/article_trash.png'></a>";
                    }
                    else
                    {
                        // actions for rejected articles
                        echo "
                        <a href='comments.php?event=".strtolower($status)."&action=approve&comment=".$commentId."' title='Approve'><img src='../miscs/images/Default/article_approve.png'></a> 
                        <a href='comments.php?event=".strtolower($status)."&action=delete&comment=".$commentId."' title='Delete'><img src='../miscs/images/Default/comment_delete.png'></a>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
                
                echo "
                </table>";
                
                // display the buttons for managing the comments
                echo "
                <table width='100%'>
                <tr>
                <td>";
                
                if($status == "PENDING")
                {
                    // button for pending comments
                    echo "
                    <input type='submit' id='submit1' value='Approve' name='approve'> 
                    <input type='submit' id='submit1' value='Reject' name='reject'>";
                }
                else if($status == "APPROVED")
                {
                    // button for approved comments
                    echo "<input type='submit' id='submit1' value='Reject' name='reject'>";
                }
                else
                {
                    // button for rejected comments
                    echo "
                    <input type='submit' id='submit1' value='Approve' name='approve'> 
                    <input type='submit' id='submit1' value='Delete' name='delete'> 
                    <input type='submit' id='submit1' value='Delete all' name='deleteAll'>";
                }
                
                echo "
                </td>
                </tr>
                </table>
                </form>";
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
         * Get Article Title method: returns the article
         * Parameter: $articleId
         * Return Type: String
         */
        function getArticleTitle($articleId)
        {
            // query the article title from the database
            $titleQuery = mysql_query("SELECT title FROM argus_articles WHERE article_id = '".$articleId."' AND status = 'PUBLISHED'") or die(mysql_error());
            $title = mysql_result($titleQuery,0,"title");
            
            // return the title
            return $title;
        }
        
        /**
         * Approve Comment Method: approves a specific comment
         * Parameter: $commentId
         */
        function approveComment($commentId)
        {
            // approve the comment
            mysql_query("UPDATE argus_comments SET status = 'APPROVED' WHERE comment_id = '".$commentId."'") or die(mysql_error());
        
            return;
        }
        
        /**
         * Reject Comment Method: rejects a specific comment
         * Parameter: $commentId
         */
        function rejectComment($commentId)
        {
            // reject the comment
            mysql_query("UPDATE argus_comments SET status = 'REJECTED' WHERE comment_id = '".$commentId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Delete Comment Method: deletes a specific comment
         * Parameter: $commentId
         */
        function deleteComment($commentId)
        {
            // delete the comment
            mysql_query("DELETE FROM argus_comments WHERE comment_id = '".$commentId."'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Delete All Comments method: delets all rejected comments
         */
        function deleteAllComments()
        {
            // delete all the comments
            mysql_query("DELETE FROM argus_comments WHERE status = 'REJECTED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * View Comment method: displays the content of the comment
         * Parameter: $commentId
         * Return Type: String
         */
        function viewComment($commentId)
        {
            // check if the comment exist in the database
            $commentQuery = mysql_query("SELECT account_id, article_id, comment, date_commented, status FROM argus_comments WHERE comment_id = '".$commentId."'") or die(mysql_error());
            
            // check the result
            if(mysql_num_rows($commentQuery) > 0)
            {
                // set the attributes
                $authorName = $this -> getAuthorName(mysql_result($commentQuery,0,"account_id"));
                $articleTitle = $this -> getArticleTitle(mysql_result($commentQuery,0,"article_id"));
                $dateCommented = date("F d, Y", mysql_result($commentQuery,0,"date_commented"));
                $comment = mysql_result($commentQuery,0,"comment");
                $status = mysql_result($commentQuery,0,"status");
                
                // set the title of the page
                echo "
                <h3><a href='comments.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo Comment</h3>
                <div class='bg1'>";
                
                // display the comment attributes
                echo "<p>Comment Information</p>";
                echo "
                <p id='box'>
                Commentator : ".$authorName."<br>
                Article : ".$articleTitle."<br>
                Date Commented : ".$dateCommented."<br>
                Status : ".$status."<br>
                </p>";
                
                // set the buttons for managing the articles
                echo "<p align='right'>";
                
                if($status == "PENDING")
                {
                    // actions for pending articles
                    echo "
                    <a href='comments.php?event=".strtolower($status)."&action=approve&comment=".$commentId."'><input type='button' id='submit1' value='approve'></a> 
                    <a href='comments.php?event=".strtolower($status)."&action=reject&comment=".$commentId."'><input type='button' id='submit1' value='reject'></a>";
                }
                else if($status == "APPROVED")
                {
                    // actions for approved articles
                    echo "
                    <a href='comments.php?event=".strtolower($status)."&action=reject&comment=".$commentId."'><input type='button' id='submit1' value='reject'></a>";
                }
                else
                {
                    // actions for rejected articles
                    echo "
                    <a href='comments.php?event=".strtolower($status)."&action=approve&comment=".$commentId."'><input type='button' id='submit1' value='approve'></a> 
                    <a href='comments.php?event=".strtolower($status)."&action=delete&comment=".$commentId."'><input type='button' id='submit1' value='delete'></a>";
                }
                
                echo "</p>";
                
                echo "
                <p id='box'>
                Comment :<br>".$comment."
                </p>";
                
                echo "</div>";
            }
                    
            return;
        }
	}
?>