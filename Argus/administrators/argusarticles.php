<?php
    /**
	 * Filename : argusarticles.php
	 * Description : page for managing submitted and published articles
	 * Date Created : December 7,2007
	 * Author : Argus Team
	 */
   
    // import the page class and display the page components
    require_once("../includes/Page.php");
    $page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
    
    // import the Articles form
    require_once("../includes/ArticlesForm.php");
    $form = new ArticlesForm();
    
    /**
	 * URL EVENTS:
	 *  viewpublished
	 *  viewissue
	 *  issues
	 *  view
	 *  approved
	 *  rejected
	 *  submitted
	 */
    
        switch($_GET["event"])
        {
            case "viewpagepublished":
                $event = "VIEW PAGE PUBLISHED";
                
                //check for actions in the URL
                if($_GET["action"] == "moveup" || $_GET["action"] == "movedown")
                {
                    // move the artivle either UP or DOWn
                    $form -> moveFrontPageArticle($_GET["article"], strtoupper($_GET["action"]));
                }
                else if($_GET["action"] == "remove")
                {
                    // remove the article from the published section
                    $form -> removePublishedArticle($_GET["article"]);
                }
            
                break;
                
            case "viewcategorypublished":
                $event = "VIEW CATEGORY PUBLISHED";
                
                // check for actions in the URL
                if($_GET["action"] == "moveup" || $_GET["action"] == "movedown")
                {
                    // move the article either UP or DOWN
                    $form -> moveArticle($_GET["article"], strtoupper($_GET["action"]));
                }
                else if($_GET["action"] == "remove")
                {
                    // remove the article from the published section
                    $form -> removePublishedArticle($_GET["article"]);
                }
                
                break;
            
            case "published":
                $event = "PUBLISHED";
                
                // check for actions in the URL
                if($_GET["action"] == "remove")
                {
                    // determine which articles are going to be removed if it's from a specific category or from a front page
                    if(isset($_GET["category"]))
                    {
                        // remove the published articles in a specific category
                        $form -> removePublishedCategoryArticles($_GET["category"]);
                    }
                    else if(isset($_GET["page"]))
                    {
                        // remove the published articles in a specific page
                        $form -> removePublishedPageArticles(strtoupper($_GET["page"]));
                    }
                }
                
                break;
            
            case "viewissue":
                $event = "VIEW ISSUE";
                
                // check for actions in the URL
                if($_GET["action"] == "remove")
                {
                    // remove the specific article from an issue
                    $form -> removeIssuedArticle($_GET["article"]);
                }
                else if($_GET["action"] == "reject")
                {
                    // reject the article
                    $form -> rejectArticle($_GET["article"]);
                }
                
                break;
            
            case "issues":
                $event = "ISSUES";
                
                // check for actions in the URL
                if($_GET["action"] == "remove")
                {
                    // remove the articles in the given particular issue id
                    $form -> removeIssuedArticles($_GET["issue"]);
                } 
                else if($_GET["action"] == "publish")
                {
                    // publish the issue
                    $form -> publishIssue($_GET["issue"]);
                }
                
                break;
                
            case "view":
                $event = "VIEW";
                
                break;
            
            case "approved":
                $event = "APPROVED";
                
                // check for actions in the URL
                if($_GET["action"] == "reject")
                {
                    // reject the article
                    $form -> rejectArticle($_GET["article"]);
                }
                else if($_GET["action"] == "publish")
                {
                    // publish the article
                    $form -> publishArticle($_GET["article"]);
                }
            
                break;
            
            case "rejected":
                $event = "REJECTED";
                
                // check for actions in the URL
                if($_GET["action"] == "approve")
                {
                    // approve the article
                    $form -> approveArticle($_GET["article"]);
                }
                else if($_GET["action"] == "delete")
                {
                    // delete the article
                    $form -> deleteArticle($_GET["article"]);
                }
                                
                break;
            
            default:
                $event = "PENDING";
                
                // check for actions in the url
                if($_GET["action"] == "approve")
                {
                    // approve the article
                    $form -> approveArticle($_GET["article"]);
                }
                else if($_GET["action"] == "reject")
                {
                    // reject the article
                    $form -> rejectArticle($_GET["article"]);
                }
                
                break;
        }
    
    /**
	 * END OF URL EVENTS
	 */
    
    /**
	 * BUTTON TRIGGER EVENTS:
	 *  approve
	 *  reject
	 *  delete
	 *  deleteAll
	 *  publishIssue
	 *  removeIssuedArticles
	 *  removeAllIssuedArticles
	 *  removePublishedArticles
	 *  removeAllPublishedArticles
	 *  publishArticles
	 *  removePublishedArticle
	 *  removeIssuedArticle
	 *  updatePositions
	 *  removePublishedPageArticles
	 *  removeAllPublishedPageArticles
	 */
    
        // APPROVE button
        if(isset($_POST["approve"]))
        {
            // get all the checked articles and then approve them
            for($i=0; $i < count($_POST["articleIds"]); $i++)
            {
                $form -> approveArticle($_POST["articleIds"][$i]);
            }
        }
        
        // REJECT button
        if(isset($_POST["reject"]))
        {
            // get all the checked articles and then rejects them
            for($i=0; $i < count($_POST["articleIds"]); $i++)
            {
                $form -> rejectArticle($_POST["articleIds"][$i]);
            }
        }
        
        // DELETE button
        if(isset($_POST["delete"]))
        {
            // get all the checked articles and then deletes them
            for($i=0; $i < count($_POST["articleIds"]); $i++)
            {
                $form -> deleteArticle($_POST["articleIds"][$i]);
            }
        }
        
        // DELETE ALL button
        if(isset($_POST["deleteAll"]))
        {
            // delete all rejected articles
            $form -> deleteAllRejectedArticles();
        }
        
        // SET ISSUES button
        if(isset($_POST["setIssues"]))
        {
            // get all the values from the combo box and update the issues of the articles
            $form -> setArticleIssues($_GET["issue"], $_POST["issueIds"]);
        }
        
        // PUBLISH ISSUE button
        if(isset($_POST["publishIssue"]))
        {
            // publish the articles that is in that issue
            $form -> publishIssue($_GET["issue"]);
            
            // after the issue has been published, redirect the user to the published articles
            header("Location: argusarticles.php?event=published");
        }
        
        // REMOVE ISSUED ARTICLES button
        if(isset($_POST["removeIssuedArticles"]))
        {
            // get all the checked issues and remove all the articles that is inside them
            for($i=0; $i < count($_POST["issueIds"]); $i++)
            {
                $form -> removeIssuedArticles($_POST["issueIds"][$i]);
            }
        }
        
        // REMOVE ALL ISSUED ARTICLES button
        if(isset($_POST["removeAllIssuedArticles"]))
        {
            // remove all articles that has an issue
            $form -> removeAllIssuedArticles();
        }
        
        // REMOVE PUBLISHED ARTICLES button
        if(isset($_POST["removePublishedArticles"]))
        {
            // get all the checked categories and remove all the articles that is inside them
            for($i=0; $i < count($_POST["categoryIds"]); $i++)
            {
                $form -> removePublishedCategoryArticles($_POST["categoryIds"][$i]);
            }
        }
        
        // REMOVE ALL PUBLISHED ARTICLES button
        if(isset($_POST["removeAllPublishedArticles"]))
        {
            // remove all published articles
            $form -> removeAllPublishedArticles();
        }
        
        // PUBLISH ARTICLES button
        if(isset($_POST["publishArticles"]))
        {
            // get all the checked articles then publishes them
            for($i=0; $i < count($_POST["articleIds"]); $i++)
            {
                $form -> publishArticle($_POST["articleIds"][$i]);
            }
        }
        
        // REMOVE PUBLISHED ARTICLE button
        if(isset($_POST["removePublishedArticle"]))
        {
            // get all the checked articles then removes them from the published section
            for($i=0; $i < count($_POST["articleIds"]); $i++)
            {
                $form -> removePublishedArticle($_POST["articleIds"][$i]);
            }
        }
        
        // REMOVE ISSUED ARTICLE button
        if(isset($_POST["removeIssuedArticle"]))
        {
            // get all the checked articles then removes them from the issue section
            for($i=0; $i < count($_POST["articleIds"]); $i++)
            {
                $form -> removeIssuedArticle($_POST["articleIds"][$i]);
            }
        }
        
        // UPDATE POSITIONS button
        if(isset($_POST["updatePositions"]))
        {
            // get all the inputted values of the position then pass it as a parameter.
            $form -> updatePublishedPositions($_GET["category"], $_POST["positions"]);
        }
        
        // REMOVE PUBLISHED PAGE ARTICLES button
        if(isset($_POST["removePublishedPageArticles"]))
        {
            // remove the articles in a front page
            for($i=0; $i < count($_POST["pages"]); $i++)
            {
                $form -> removePublishedPageArticles($_POST["pages"][$i]);
            }
        }
        
        // REMOVE ALL PUBLISHED PAGE ARTICLES button
        if(isset($_POST["removeAllPublishedPageArticles"]))
        {
            // remove all main or featured articles
            $form -> removeAllPublishedPageArticles();
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
        
        // determine what to display
        switch($event)
        {
            case "VIEW PAGE PUBLISHED":
                // view all published articles in a specific front page
                $form -> displayPublishedPageArticles(strtoupper($_GET["page"]));
                
                break;
            
            case "VIEW CATEGORY PUBLISHED":
                // view all published articles in a specific category
                $form -> displayPublishedCategoryArticles($_GET["category"]);
                
                break;
            
            case "PUBLISHED":
                // display all published categories
                $form -> displayPublished();
                
                break;
            
            case "VIEW ISSUE":
                // display all articles in a particular issue
                $form -> displayIssueArticles($_GET["issue"]);
                
                break;
                
            case "ISSUES":
                // display all the available issues
                $form -> displayIssues();
                
                break;
            
            case "VIEW":
                // view the article
                $form -> viewArticle($_GET["article"]);
                
                break;
                
            case "APPROVED":
                // display all approved articles
                $form -> displayArticles("APPROVED", $_GET["page"]);
                
                break;
                
            case "REJECTED":
                // display all rejected articles
                $form -> displayArticles("REJECTED", $_GET["page"]);
                
                break;
            
            case "PENDING":
                // display all submitted articles
                $form -> displayArticles("PENDING", $_GET["page"]);
                
                break;
        }
        
        echo "</div>";
    ?>
</div>
<?php
    // display the footer
    $page -> displayFooter();
?>