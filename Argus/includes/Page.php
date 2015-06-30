<?php
	/**
	 * Filename		: Page.php
	 * Description	: contains the basic components of a page. Page Components: HEADER, FOOTER, and SIDE BAR
	 * Date Created	: November 28,2007
	 * Author		: Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
	 *	Page($accountId)
	 *	void displayHeader()
	 *	void displayBanner()
	 *	void displaySearchBar()
	 *	void displayCategoryBar()
	 *	void displayContributorBar()
	 *	void displayAdministratorBar()
     *  void displayCalendarBar()
	 *	void displayFooter()
     *  void displayTools()
     *  void displayDivCode($column)
	 */
	
    $viewerConnectorPath = "includes/class_libraries/DatabaseConnector.php";
    $memberConnectorPath = "../includes/class_libraries/DatabaseConnector.php";
    
    if(file_exists($viewerConnectorPath) || file_exists($memberConnectorPath))
    {
        // import the database connector class and create a new connection to the database
        require_once("class_libraries/DatabaseConnector.php");
        $databaseConnector = new DatabaseConnector();
        
        if(file_exists("installation"))
        {            
            // when the database connector already exist, the installation folder should be automatically deleted
            // include the directory deleter class and delete the installation file
            include("class_libraries/DirectoryDelete.php");
            $directoryDelete = new DirectoryDelete();
            
            // delete the installation directory
            $directoryDelete -> deleteDirectory("installation",null);
        }
    }
    else
    {
        if(file_exists("installation/index.php"))
        {
            // redirect the user to the installation page
            header("Location: installation/index.php");
        }
        else
        {
            // redirect the user to the installation page
            header("Location: ../installation/index.php");
        }
    }
	
	class Page
	{
		var $title;
		var $subtitle;
		var $position;
        var $accountId;
        var $panelInterface;
		
		/**
		 * Constructor method
		 * Paramters: $accountId, $position
		 */
		function Page($accountId, $position)
		{
            // check if the site is online or not
            $siteOnlineQuery = mysql_query("SELECT content FROM argus_infos WHERE name='site_online'") or die(mysql_error());
            $siteOnline = explode(";",mysql_result($siteOnlineQuery,0,"content"));
            
            // the only person that can access the page is the localhost which has an ip address of 127.0.0.1
            if($siteOnline[2] == "false" && $_SERVER["REMOTE_ADDR"] != "127.0.0.1")
            {
                if(file_exists("unavailable.php"))
                {
                    // if the site is disabled, redirect the user to the unavailable page
                    header("Location: unavailable.php");
                }
                else
                {
                    header("Location: ../unavailable.php");
                }
            }
            
			// set the title and subtitle of the page
            $this -> accountId = $accountId;
            
            // query the status of the panel interface if it is switched or not
            $panelInterfaceQuery = mysql_query("SELECT content FROM argus_infos WHERE name='interface_panel'") or die(mysql_error());
            $this -> panelInterface = mysql_result($panelInterfaceQuery,0,"content");
            
            // query the title of the publication name from the database
			$titleQuery = mysql_query("SELECT content FROM argus_infos WHERE name='publication_name'") or die(mysql_error());
            
            // expected result from the query is something like "Argus;College of Information and Computing Science"
            // separate the title from the description
            $titleAndSubtitle = mysql_result($titleQuery,0,"content");
            $titleAndSubtitle = explode(";",$titleAndSubtitle);
            
			$this -> title = $titleAndSubtitle[0];
			$this -> subtitle = $titleAndSubtitle[1];

			// get the position of the account from the database to validate the page and display the appropriate page
			$positionQuery = mysql_query("SELECT position FROM argus_accounts WHERE account_id = '".$accountId."'") or die(mysql_error());
			
			// check if there is a position queried from the database
			if(mysql_num_rows($positionQuery) > 0)
			{
				$this -> position = mysql_result($positionQuery,0,"position");
			}
						
			// validates the PAGE for NON-MEMEBRS
			if($position == null)
			{
				// check if the cookie has been set
				if(isset($accountId))
				{
					// if the cookie has been set, which means that a user has logged in and trying to access the NON-MEMBER page
					// then appropriate validation occurs and redirects the USER to the allowed pages depending on the position
					
					switch($this -> position)
					{
						case "MEMBER":
							// redirection for MEMBERS
							header("Location: members/index.php");
							break;
						
						case "CONTRIBUTOR":
							// redirection for CONTRIBUTORS
							header("Location: contributors/index.php");
							break;
							
						case "ADMINISTRATOR":
							// redirection for ADMINISTRATORS
							header("Location: administrators/index.php");
							break;
					}
				}
			}
			// validates the PAGE for MEMBERS, CONTRIBUTORS, and ADMINISTRATORS
			else
			{
				// inorder to access MEMBER, CONTRIBUTOR, and ADMINISTRATOR pages, the ACCOUNT ID should have been set on the cookie
				if(!isset($accountId))
				{
					// it is possible that the COOKIE has been set but MEMBERS are able to access to the ADMINISTRATOR and CONTRIBUTOR
					// pages. For security purposes check if the MEMBER is accessing his/her own MEMBERS page and same with the other POSITIONS
					if($this -> position != $position)
					{
						// redirect the user to the NON-MEMBER page and let that page redirect the user to the correct page
						header("Location: ../index.php");
					}
					else
					{
						// redirect the user to the NON-MEMBER page if the cookie has not been set
						header("Location: ../index.php");
					}
				}
			}
			
			return;
		}
		 
		/**
		 * Display Header method: displays the header of the publication page including the CSS
		 */
		function displayHeader()
		{
			// display the WEB PAGE standards
			echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>";
			echo "<html xmlns='http://www.w3.org/1999/xhtml'>";
            
            // open the HTML tag. The HTML tag will be closed at the  DISPLAY FOOTER METHOD
			echo "<head>";
            echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";  
			
			// display the title and subtitle that is found above the file menu
			echo "<title>".$this -> title." | ".$this -> subtitle."</title>";
			
			// display the CSS properties of the page and set the theme path which is queried from the database
			$themeQuery = mysql_query("SELECT path FROM argus_themes WHERE status = 'ENABLED'") or die(mysql_error());
			$path = mysql_result($themeQuery,0,"path");
			
			// set the CSS path for NON-MEMBERS, MEMBERS, CONTRIBUTORS, and ADMINISTRATORS
			switch($this -> position)
			{
				case null:
					// CSS PATH for NON-MEMBERS
					echo "<link href='".$path."' rel='stylesheet' type='text/css'>";
					break;
					
				default:
					// CSS PATH for MEMBERS, CONTRIBUTORS, and ADMINISTRATORS
					echo "<link href='../".$path."' rel='stylesheet' type='text/css'>";
			}
			
            // close the head
            echo "</head>";
            
			return;
		}
		
		/**
		 * Display Banner method: displays the banner of the page and menus such as HOMEPAGE, PROFILE, and SIGNOUT
		 */
		function displayBanner()
		{
			echo "<div id='header'>";
			
			// display the publication name
			echo "<h1>".$this -> title."</h1>";
			echo "<h2>".$this -> subtitle."</h2>";
			
			echo "<ul>";
			echo "<li><a href='index.php'><div id='home'></div></a></li>";
			
			// display banner for NON-MEMBERS, MEMEBRS, CONTRIBUTORS, and ADMINISTRATORS
			switch($this -> position)
			{
				case null:
					// banner for NON-MEMBERS
					echo "<li><a href='join.php'><div id='register'></div></a></li>";
					echo "<li><a href='signin.php'><div id='signIn'></div></a></li>";
					break;
				
				default:
					// banner for MEMBERS, CONTRIBUTORS, and ADMINISTRATORS
					echo "<li><a href='profilesedit.php'><div id='profile'></div></a></li>";
					echo "<li><a href='../index.php?event=signout'><div id='signOut'></div></a></li>";
			}
			
			echo "</ul>";
			echo "</div>";
			
			return;
		}
		
		/**
		 * Display Search Bar method: displays the search panel
		 */
		function displaySearchBar()
		{
			echo "<div id='panelhead'><h4>Search</h4><div id='searchIcon'></div></div>";
			echo "<div class='bg3'><br />";
			echo "<form id='searchbox' method='post' action='index.php'>";
			echo "<input type='text' style='width:140px;' name='word'>&nbsp;";
			echo "<input type='submit' id='noshow' name='search' />";
			echo "</form>";
			echo "</div>";
			
			return;
		}
		
		/**
		 * Display Category Bar method: returns available categories that are enabled by the administrator for viewing of published articles
		 */
		function displayCategoryBar()
		{
			// display all ENABLED categories
			$categoriesQuery = mysql_query("SELECT category_id, name FROM argus_categories WHERE status='ENABLED' ORDER BY position ASC") or die(mysql_error());
			
			// display only the category bar if there are 1 or more categories ENABLED
			if(mysql_num_rows($categoriesQuery) > 0)
			{
				echo "<div id='panelhead'><h4>Categories</h4><div id='sectionIcon'></div></div>";
				echo "<div class='bg3' id='categories'>";
				echo "<ul>";
				
				for($i=0; $i<mysql_num_rows($categoriesQuery); $i++)
				{
					$categoryId = mysql_result($categoriesQuery,$i,"category_id");
					$name = mysql_result($categoriesQuery,$i,"name");
					
					// display the category
					if($i == 0)
					{
						echo "<li class='first'><a href='index.php?event=categories&category=".$categoryId."'>".$name."</a></li>";
					}
					else
					{
						echo "<li><a href='index.php?event=categories&category=".$categoryId."'>".$name."</a></li>";
					}
				}
				
				echo "</ul>";
				echo "</div>";
			}
			
			return;
		}
		
		/**
		 * Display Contributor Bar method: displays the tools of contributor accounts
		 */
		function displayContributorBar()
		{                         
            // include the javascript for the menus
            echo "<link type='text/css' rel='stylesheet' href='../miscs/js/menu/css/style.css'>";
            echo "<script type='text/javascript' src='../miscs/js/menu/jquery.js'></script>";
            echo "<script type='text/javascript' src='../miscs/js/menu/menu.js'></script>";
            
            // query the number of UNREAD mails of the user
            $mailsQuery = mysql_query("SELECT mail_id FROM argus_mails WHERE account_id = '".$this -> accountId."' AND type = 'UNREAD'") or die(mysql_error());
            $mailsCount = mysql_num_rows($mailsQuery);
            
			echo "<div id='panelhead'><h4>Staff Tools</h4><div id='staffIcon'></div></div>";
			echo "<div class='bg3' align='center'>";
			echo "<ul id='Nav'>";
			
            // mail manager
            echo "<li class='first'>";
            if($mailsCount > 0)
            {
                // if there are unread mails, notify the user
			    echo "<b><a href='mailbox.php'>Mailbox (".$mailsCount.")</a></b>";
            }
            else
            {
                echo "<a href='mailbox.php'>Mailbox</a>";
            }
            
            echo "<ul class='Menu'>
                    <li><a href='mailbox.php?event=saved'>Saved</a></li>
                    <li><a href='mailbox.php?event=deleted'>Deleted</a></li>
                    <li><a href='mailscompose.php'>Compose</a></li>
                    <li><a href='mailbox.php?event=contacts'>Contacts</a></li>
                  </ul>";
            echo "</li>";
            
            // articles manager
			echo "<li>";
            echo "<a href='articles.php'>Articles Manager</a>";
            echo "<ul class='Menu'>
                    <li><a href='articles.php?event=saved'>Saved</a></li>
                    <li><a href='articles.php?event=deleted'>Deleted</a></li>
                    <li><a href='articlescompose.php'>Compose</a></li>
                    <li><a href='articles.php?event=submitted'>Submitted</a></li>
                   </ul>";
            echo "</li>";
            
            // images manager
			echo "<li>";
            echo "<a href='images.php'>Images Manager</a>";
            echo "<ul class='Menu'>
                    <li><a href='images.php?event=saved'>Saved</a></li>
                    <li><a href='images.php?event=deleted'>Deleted</a></li>
                    <li><a href='imagesupload.php'>Upload</a></li>";
            echo "</ul>";
            echo "</li>";
			
			echo "</ul>";
			echo "</div>";
			
			return;
		}
		
		/**
		 * Display Administrator Bar method: displays the tools of administrator accounts
		 */
		function displayAdministratorBar()
		{            
            // query for new PENDING articles
            $articlesQuery = mysql_query("SELECT article_id FROM argus_articles WHERE status = 'PENDING'") or die(mysql_error());
            $pendingArticlesCount = mysql_num_rows($articlesQuery);
            
            // query for new PENDING comments
            $commentsQuery = mysql_query("SELECT comment_id FROM argus_comments WHERE status = 'PENDING'") or die(mysql_error());
            $pendingCommentsCount = mysql_num_rows($commentsQuery);
            
			echo "<div id='panelhead'><h4>Administrator Tools</h4><div id='adminIcon'></div></div>";
			echo "<div class='bg3'>";
			echo "<ul>";
			
			// accounts manager
			echo "<li class='first'><a href='accounts.php'>Accounts Manager</a></li>";
            
            if($pendingArticlesCount > 0)
            {
                // if there are pending articles, notify the user
			    echo "<li><a href='argusarticles.php'><b>Articles Manager (".$pendingArticlesCount.")</b></a></li>";
            }
            else
            {
                echo "<li><a href='argusarticles.php'>Articles Manager</a></li>";
            }
            
			echo "<li><a href='categories.php'>Categories Manager</a></li>";
			echo "<li><a href='issues.php'>Issues Manager</a></li>";
            
            if($pendingCommentsCount > 0)
            {
                // if there are pending comments, notify the user
			    echo "<li><a href='comments.php'><b>Comments Manager (".$pendingCommentsCount.")</b></a></li>";
            }
            else
            {
                echo "<li><a href='comments.php'>Comments Manager</a></li>";
            }
            
            echo "<li><a href='events.php'>Events Manager</a></li>";
            echo "<li><a href='archives.php'>Archives Manager</a></li>";
			echo "<li><a href='settings.php'>Web Settings</a></li>";
			
			echo "</ul>";
			echo "</div>";
			
			return;
		}
        
        /**
         * Display Calendar bar method: displays the calendar
         */
        function displayCalendarBar()
        {
            // include the calendar class
            include("class_libraries/Calendar.php");
            $calendar = new Calendar();
            $setDefaultValue = true;
            
            // set the calendar attributes
            if($_GET["year"] != null && $_GET["month"] != null)
            {
                // get the attributes from the URL
                $year = $_GET["year"];
                $month = $_GET["month"];
                
                // check if the year is numeric and month is numeric
                if(ctype_digit($year) && ctype_digit($month))
                {
                    // if they are both numeric, check if the month value is valid.
                    // there 12 months in 1 year therefore valid month numbers ranges from 1 - 12
                    if($month >= 1 && $month <= 12)
                    {
                        $setDefaultValue = false;
                    }
                }
                else
                {
                    // set the default value which is the current month and year
                    $setDefaultValue = true;
                }
            }
            else
            {
                // set the default value which is the current month and year
                $setDefaultValue = true;
            }
            
            if($setDefaultValue == true)
            {
                // set the default value of the calendar which is the current year and month
                // Y = Full year
                // n = Numeric represntation of the month
                // check if there any events in the url
                $time = time();
                $year = date("Y", $time);
                $month = date("n", $time);
            }
            
            // generate the calendar
            echo "<div id='panelhead'><h4>Calendar</h4><div id='calendarIcon'></div></div>";
            echo "<div class='bg3' id='tablePanel' align='center'>";
            echo "<p>".$calendar -> generateCalendar($year, $month, $days, 2)."</p>";
            echo "</div>";
            
            return;
        }

		
		/**
		 * Display Footer method: displays the footer of the publication page
		 */
		function displayFooter()
		{
			echo "<div id='footer'>";
			echo "<p>";
			echo "<a href='index.php'>Home</a> . ";
			echo "<a href='#'>About Us</a>. ";
			echo "<a href='index.php?event=contactus'>Contact Us</a> . ";
			echo "<a href='index.php?event=termsandpolicies'>Terms and Policies</a> . ";
            echo "<a href='index.php?event=archives'>Archives</a>";
			echo "</p>";
			echo "<br />";
			echo "<p>powered by argus</p>";
			echo "</div>";
			echo "</html>";
			
			return;
		}
        
        /**
         * Display Tools method: displays the tools for administrator and contributors
         */
        function displayTools()
        {   
            // display the default tools
            $this -> displaySearchBar();
            
            // check the position of the account
            if($this -> position == "ADMINISTRATOR")
            {
                // display the tools for the administrator
                $this -> displayAdministratorBar();
                $this -> displayContributorBar();
            }
            else if($this -> position == "CONTRIBUTOR")
            {
                // display the tools for the contributor
                $this -> displayContributorBar();
            }
            
            $this -> displayCategoryBar();
            $this -> displayCalendarBar();
            
            return;
        }
        
        /**
         * Display Div Code method: displays the div code in the HTML for changing of panels purposes
         * Parameter: $column
         */
        function displayDivCode($column)
        {
            // check which column is to be displayed
            if($column == "RIGHT")
            {
                // left column properties
                if($this -> panelInterface == "false")
                {
                    // default left column
                    echo "<div id='colOne' style='float:right'>";
                }
                else
                {
                    echo "<div id='colOne' style='float:left'>";
                }
            }
            else
            {
                // right column properties
                if($this -> panelInterface == "false")
                {
                    // default right column
                    echo "<div id='colTwo' style='float:left'>";
                }
                else
                {
                    echo "<div id='colTwo' style='float:right'>";
                }
            }
        }
	}
?>