<?php
	/**
	 * Filename : search.php
	 * Description : page for displaying the search facility
	 * Date Created : February 5,2008
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
		
	/**
	 * URL EVENTS:
     *  accounts
     *  argusarticles
	 */
    
        switch($_GET["event"])
        {
            case "accounts":
                $event = "ACCOUNTS";
                
                //include the Accounts Form class
                include("../includes/AccountsForm.php");
                $form = new AccountsForm();
                
                break;
                
            default:
                $event = "ARGUS ARTICLES";
                
                //include the Articles Class
                include("../includes/ArticlesForm.php");
                $form = new ArticlesForm();
                
                break;
        }
	
	/**
	 * END OF URL EVENTS
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
        
		// display the tools
		$page -> displayTools();
        
        echo "</div>";
	?>
	<!-- left side column: contains sub options and articles and where manipulation of tools occurs -->
	<?php
        $page -> displayDivCode("LEFT");
        
		// display the banner
		$form -> displayBanner();
        
        // determine the event and which search facility is to be provided depending on the event
        switch($event)
        {
            case "ARGUS ARTICLES":
                // display the Facility for Article Search
                echo "<h3>Article Search</h3>";
                echo "<div class='bg1'>";
                echo "<p>Article Search Information</p>";                
                echo "<form method='post' action='".$_SERVER["PHP_SELF"]."?event=articles'>";
                echo "<p id='box'>";
                echo "<b>Search Box</b><br />";
                echo "<input type='text' id='textbox' name='articleKeyword' value='".$_POST["articleKeyword"]."'>";
                echo "<b>Search By</b><br />";
                echo "<select id='textbox' name='articleSearchType'>";
                
                // set which was searched
                if($_POST["articleSearchType"] == "byTitle")
                {
                    echo "<option value='byTitle' selected='selected'>Title</option>";
                }
                else
                {
                    echo "<option value='byTitle'>Title</option>";
                }
                
                if($_POST["articleSearchType"] == "byAuthor")
                {
                    echo "<option value='byAuthor' selected='selected'>Author</option>";
                }
                else
                {
                    echo "<option value='byAuthor'>Author</option>";
                }
                
                echo "</select>";
                echo "</p>";
                echo "<p align='center'>";
                echo "<input type='submit' id='submit2' value='Search' name='articleSearch'>";
                echo "</p>";
                echo "</div>";
                echo "</form>";
                
                // Article search Button event
                if(isset($_POST["articleSearch"]))
                {
                    // get the input from the user
                    $articleKeyword = $_POST["articleKeyword"];
                    $articleSearchType = $_POST["articleSearchType"];
                    
                    // search the article
                    $form -> searchArticle($articleKeyword, $articleSearchType);
                }
                
                break;
            
            case "ACCOUNTS":
                // display the Facility for Account Search
                echo "<h3>Accounts Search</h3>";
                echo "<div class='bg1'>";
                echo "<p>Accouts Search Information</p>";                
                echo "<form method='post' action='".$_SERVER["PHP_SELF"]."?event=accounts'>";
                echo "<p id='box'>";
                echo "<b>Search Box</b><br />";
                echo "<input type='text' id='textbox' name='accountKeyword' value='".$_POST["accountKeyword"]."'>";
                echo "<b>Search By</b><br />";
                echo "<select id='textbox' name='accountSearchType'>";
            
                if($_POST["accountSearchType"] == "byUsername")
                {
                    echo "<option value='byUsername' selected='selected'>Username</option>";
                }
                else
                {
                    echo "<option value='byUsername'>Username</option>";
                }
                
                if($_POST["accountSearchType"] == "byName")
                {
                    echo "<option value='byName' selected='selected'>Name</option>";
                }
                else
                {
                    echo "<option value='byName'>Name</option>";
                }
                
                if($_POST["accountSearchType"] == "byEmail")
                {
                    echo "<option value='byEmail' selected='selected'>Email</option>";
                }
                else
                {
                    echo "<option value='byEmail'>Email</option>";
                }
            
                echo "</select>";
                echo "</p>";
                echo "<p align='center'>";
                echo "<input type='submit' id='submit2' value='Search' name='accountSearch'>";
                echo "</p>";
                echo "</div>";
                
                // Account search Button event
                if(isset($_POST["accountSearch"]))
                {
                    // get the input from the user
                    $accountKeyword = $_POST["accountKeyword"];
                    $accountSearchType = $_POST["accountSearchType"];
                    
                    // search the article
                    $form -> searchAccount($accountKeyword, $accountSearchType);
                }
            
                break;
        }
	?>
    </div>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>