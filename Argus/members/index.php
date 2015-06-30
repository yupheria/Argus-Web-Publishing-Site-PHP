<?php
	/**
	 * Filename : index.php
	 * Description : Entry point of the publication for MEMBERS
	 * Date Created : November 29,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "MEMBER");
	
	// import the main page class
	require_once("../includes/MainPageForm.php");
	$form = new MainPageForm($_COOKIE["argus"]);
    
    /**
	 * URL EVENTS
	 *  event summary
	 *  author
	 *  archives
	 *  contact us
	 *  terms and policies
	 *  articles
	 *  events
	 *  categories
	 */
         
        switch($_GET["event"])
        {
            case "eventsummary":
                $event = "EVENT SUMMARY";
                
                break;
            
            case "author":
                $event = "AUTHOR";
                
                break;
                
            case "archives":
                $event = "ARCHIVES BY YEAR";
                
                // check if there are any 'year' specified in the URL
                if(isset($_GET["year"]))
                {
                    // change the event
                    $event = "ARCHIVES BY ISSUE";
                    
                    // check if there any 'issue' specified in the URL
                    if(isset($_GET["issue"]))
                    {
                        // change the event
                        $event = "ARCHIVES";
                    }
                }
                
                break;
                
            case "contactus":
                $event = "CONTACT US";
                
                break;
                
            case "termsandpolicies":
                $event = "TERMS AND POLICIES";
                
                break;
                
            case "events":
                $event = "EVENTS";
                
                break;
                
            case "articles":
                $event = "ARTICLES";
                
                break;
            
            case "categories":
                $event = "CATEGORIES";
                
                break;
            
            default:
                $event = "FRONT PAGE";
                
                break;
        }
    
    /**
	 * END OF URL EVENTS
	 */
    
    /**
	 * BUTTON EVENT TRIGGERS
	 *  submitComment
	 */
    
        // SUBMIT COMMENT button
        if(isset($_POST["submitComment"]))
        {
            // get the inputs from the user
            $comment = $_POST["comment"];
            
            // add the comment
            $result = $form -> submitComment($_GET["article"], $comment);
            
            // check the result
            if($result == true)
            {
                // if successful, set the message to be displayed below
                $successMessage = "The comment has been successfully submitted for moderation";
            }
            else
            {
                // get the error that was committed during the submission of comment
                $errors = $form -> getErrors();
            }
        }
        
        // SEARCH button
        if(isset($_POST["search"]))
        {
            // change the event to search
            $event = "SEARCH";
        }
    
    /**
	 * END OF BUTTON EVENT TRIGGERS
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
        
        // determine which is to be displayed
        switch($event)
        {
            case "SEARCH":
                // display the search result
                $form -> displaySearchResult($_POST["word"]);
                
                break;
                
            case "EVENT SUMMARY":
                // display the event summary
                $form -> displayEventSummary($_GET["month"], $_GET["year"]);
                
                break;
                
            case "AUTHOR":
                // display the author information
                $form -> displayAuthorInfo($_GET["account"]);
                
                break;
                
            case "ARCHIVES":
                // display the archives that is inside an issue in a specified year
                $form -> displayArchives($_GET["year"], $_GET["issue"]);
            
                break;
            
            case "ARCHIVES BY ISSUE":
                // display the archives by issue in a specified year
                $form -> displayArchivesByIssue($_GET["year"]);
                
                break;
            
            case "ARCHIVES BY YEAR":
                // display the archives
                $form -> displayArchivesByYear();
                
                break;
                
            case "CONTACT US":
                // display the contact us page
                $form -> displayInfo("CONTACT US");
                
                break;
            
            case "TERMS AND POLICIES":
                // display the terms and policies
                $form -> displayInfo("TERMS AND POLICIES");
                
                break;
                
            case "EVENTS":
                // display the events in a particular day
                $form -> displayEvents($_GET["day"], $_GET["month"], $_GET["year"]);
                
                break;
                
            case "ARTICLES":
                // check if there were errors committed during the submission of comments
                if(isset($_POST["submitComment"]) && $result == false)
                {
                    // display the errors
                    echo "<p align='center'><font color='red'>".$errors["comment"]."</font></p>";
                }
                // set the success message if the comment has been successfully submitted
                else if(isset($_POST["submitComment"]) && $result == true)
                {
                    // display the message
                    echo "<p align='center'><font color='green'>".$successMessage."</font></p>";   
                }
                
                // display the article
                $form -> displayArticle($_GET["article"]);
                
                break;
            
            case "CATEGORIES":
                // display all the articles in the given category
                $form -> displayCategoryArticles($_GET["category"]);
                
                break;
                
            case "FRONT PAGE":
                // display the currently published articles in the front page
                $form -> displayFrontPageArticles();
            
                break;
        }
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>