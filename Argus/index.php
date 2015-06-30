<?php
	/**
	 * Filename : index.php
	 * Description : Entry point of the publication for NON-MEMBERS
	 * Date Created : November 28,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("includes/Page.php");
	$page = new Page($_COOKIE["argus"], null);
	
	// import the main page form class
	require_once("includes/MainPageForm.php");
	$form = new MainPageForm(null);
	
	/**
	 * URL EVENTS
     *  event summary
     *  contact us
     *  terms and policies
     *  archives
     *  author
     *  events
     *  articles
     *  categories
	 *	signout
	 */
	 	
		switch($_GET["event"])
		{
            case "eventsummary":
                $event = "EVENT SUMMARY";
                
                break;
                
            case "contactus":
                $event = "CONTACT US";
                
                break;
                
            case "termsandpolicies":
                $event = "TERMS AND POLICIES";
                
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
                
            case "author":
                $event = "AUTHOR";
                
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
            
			case "signout":
				// signout the user by destroying the SESSION COOKIE
				setcookie("argus","",time()-3600);
				break;
            
            default:
                $event = "FRONT PAGE";
                
                break;
		}
	
	/**
	 * END OF URL EVENTS
	 */
    
    /**
     * BUTTON EVENTS
     *  search
     */
    
        // SEARCH button
        if(isset($_POST["search"]))
        {
            // change the event to search
            $event = "SEARCH";
        }
    
    /**
     * END OF BUTTON EVENTS
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
        
		// display the tools for NON-MEMBERS
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
                
            case "CONTACT US":
                // display the contact us page
                $form -> displayInfo("CONTACT US");
                
                break;
            
            case "TERMS AND POLICIES":
                // display the terms and policies
                $form -> displayInfo("TERMS AND POLICIES");
                
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
                
            case "AUTHOR":
                // display the information of the author
                $form -> displayAuthorInfo($_GET["account"]);
                
                break;
                
            case "EVENTS":
                // display the events in a particular day
                $form -> displayEvents($_GET["day"], $_GET["month"], $_GET["year"]);
            
                break;
                
            case "ARTICLES":
                // display the article
                $form -> displayArticle($_GET["article"]);
                
                break;
                
            case "CATEGORIES":
                // display all the articles in the given category
                $form -> displayCategoryArticles($_GET["category"]);
                
                break;
            
            case "FRONT PAGE":
                // display the front page
                $form -> displayFrontPageArticles();
        }
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>