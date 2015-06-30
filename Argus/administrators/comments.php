<?php
	/**
	 * Filename : comments.php
	 * Description : page for managing accounts
	 * Date Created : November 29,2007
	 * Author : Argus Team
	 */
	
	// import the page class and display the page components
	require_once("../includes/Page.php");
	$page = new Page($_COOKIE["argus"], "ADMINISTRATOR");
	
	// import the comments form class
	require_once("../includes/CommentsForm.php");
	$form = new CommentsForm();
	
	/**
	 * URL EVENTS:
	 *  viewcomment
	 *  pending
	 *  approved
	 *  rejected
	 */
    
        switch($_GET["event"])
        {
            case "viewcomment":
                $event = "VIEW COMMENT";
                
                break;
            
            case "rejected":
                $event = "REJECTED";
                
                // check for actions in the URL
                if($_GET["action"] == "approve")
                {
                    // approve the comment
                    $form -> approveComment($_GET["comment"]);
                }
                else if($_GET["action"] == "delete")
                {
                    // delete the comment
                    $form -> deleteComment($_GET["comment"]);
                }
                
                break;
            
            case "approved":
                $event = "APPROVED";
                
                // check for actions in the URL
                if($_GET["action"] == "reject")
                {
                    // reject the comment
                    $form -> rejectComment($_GET["comment"]);
                }
                
                break;
                
            default:
                $event = "PENDING";
                
                // check for actions in the URL
                if($_GET["action"] == "approve")
                {
                    // approve the comment
                    $form -> approveComment($_GET["comment"]);
                }
                else if($_GET["action"] == "reject")
                {
                    // reject the comment
                    $form -> rejectComment($_GET["comment"]);
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
	 */
    
        // APPROVE button
        if(isset($_POST["approve"]))
        {
            // get all the checked comments and approves them
            for($i=0; $i < count($_POST["commentIds"]); $i++)
            {
                $form -> approveComment($_POST["commentIds"][$i]);
            }
        }
        
        // REJECT button
        if(isset($_POST["reject"]))
        {
            // get all the checked comments and then rejects them
            for($i=0; $i < count($_POST["commentIds"]); $i++)
            {
                $form -> rejectComment($_POST["commentIds"][$i]);
            }
        }
        
        // DELETE button
        if(isset($_POST["delete"]))
        {
            // get all the checked comments and then deletes them
            for($i=0; $i < count($_POST["commentIds"][$i]); $i++)
            {
                $form -> deleteComment($_POST["commentIds"][$i]);
            }
        }
        
        // DELETE ALL button
        if(isset($_POST["deleteAll"]))
        {
            // delete all the rejectec comments
            $form -> deleteAllComments();
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
    
		// display the tools
		$page -> displayTools();
        
        echo "</div>";
	?>
	<!-- left side column: contains sub options and articles and where manipulation of tools occurs -->
	<?php
        $page -> displayDivCode("LEFT");
        
		// display the banner
		$form -> displayBanner();
      
        // determine which page to display
        switch($event)
        {
            case "VIEW COMMENT":
                // display the comment
                $form -> viewComment($_GET["comment"]);
                
                break;
            
            case "REJECTED":
                // display all rejected comments
                $form -> displayComments("REJECTED");
            
                break;
            
            case "APPROVED":
                // display all approved comments
                $form -> displayComments("APPROVED");
            
                break;
                
            case "PENDING":
                // display all pending comments
                $form -> displayComments("PENDING");
                
                break;
        }
        
        echo "</div>";
	?>
</div>
<?php
	// display the footer
	$page -> displayFooter();
?>