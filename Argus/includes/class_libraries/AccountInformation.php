<?php
	/**
	 * Filename : AccountInformation.php
	 * Description : Displays the statistics and information of an account
	 * Date Created : December 27,2007
	 * Author : Argus Team
	 */
	
	/**
	 * METHODS SUMMARY:
     * void AccountInformation($accountId)
	 */
	
	class AccountInformation
	{
        var $accountId;
        
        /**
         * Constructor Method
         * Parameter: $accountId
         */
        function AccountInformation($accountId)
        {
            // set the account id
            $this -> accountId = $accountId;
            
            return;
        }
        
        /**
         * Display Mail Information method: displays the information of mails of an account
         * Return Type: String
         */
        function displayMailInformation()
        {
            // query saved mails of the account
            $savedMailsQuery = mysql_query("SELECT mail_id FROM argus_mails WHERE account_id = '".$this -> accountId."' AND status = 'SAVED'") or die(mysql_error());
            
            // query deleted mails of the account
            $deletedMailsQuery = mysql_query("SELECT mail_id FROM argus_mails WHERE account_id = '".$this -> accountId."' AND status = 'DELETED'") or die(mysql_error());
            
            // calculate the number of mails
            $savedMailsCount = mysql_num_rows($savedMailsQuery);
            $deletedMailsCount = mysql_num_rows($deletedMailsQuery);
            $totalMailsCount = $savedMailsCount + $deletedMailsCount;
            
            // display the MAIL information
            echo "
            <p>Mail Information</p>
            <p id='box'>
                Saved Mails: ".$savedMailsCount."<br />
                Trash Mails: ".$deletedMailsCount."<br /><br>
                Total Mails: ".$totalMailsCount."<br />
            </p>";
            
            return;
        }
        
        /**
         * Display Article Information method: displays the information of articles of an account
         * Return Type: string
         */
        function displayArticleInformation()
        {
            // query saved articles of the account
            $savedArticlesQuery = mysql_query("SELECT saved_article_id FROM argus_saved_articles WHERE account_id = '".$this -> accountId."' AND status = 'SAVED'") or die(mysql_error());
        
            // query deleted articles of the account
            $deletedArticlesQuery = mysql_query("SELECT saved_article_id FROM argus_saved_articles WHERE account_id = '".$this -> accountId."' AND status = 'DELETED'") or die(mysql_error());
            
            // calculate the number of articles
            $savedArticlesCount = mysql_num_rows($savedArticlesQuery);
            $deletedArticlesCount = mysql_num_rows($deletedArticlesQuery);
            $totalArticlesCount = $savedArticlesCount + $deletedArticlesCount;
            
            // display ARTICLE information
            echo "
            <p>Article Information</p>
            <p id='box'>
                Saved Articles: ".$savedArticlesCount."<br />
                Trash Articles: ".$deletedArticlesCount."<br /><br>
                Total Articles: ".$totalArticlesCount."<br />
            </p>";
            
            return;
        }
        
        /**
         * Display Image Information method: displays the information of images of an account
         * Return Type: string
         */
        function displayImageInformation()
        {
            // query the number of saved images of the account
            $savedImagesQuery = mysql_query("SELECT image_id, path FROM argus_images WHERE account_id = '".$this -> accountId."' AND status = 'SAVED'") or die(mysql_error());
            
            // query the number of deleted images of the account
            $deletedImagesQuery = mysql_query("SELECT image_id, path FROM argus_images WHERE account_id = '".$this -> accountId."' AND status = 'DELETED'") or die(mysql_error());
            
            // calculate the number of images
            $savedImagesCount = mysql_num_rows($savedImagesQuery);
            $deletedImagesCount = mysql_num_rows($deletedImagesQuery);
            $totalImagesCount = $savedImagesCount + $deletedImagesCount;
            
            // calculate the hard disk space the saved image has used
            $savedImagesFileSize = 0;
            
            for($i=0; $i<$savedImagesCount ; $i++)
            {
                // incrementally compute the file sizes of each saved images in Kilobytes
                // 1 KB = 1024 bytes
                $savedImagePath = mysql_result($savedImagesQuery,$i,"path");
                $savedImagesFileSize = $savedImagesFileSize + ((filesize($savedImagePath)) / 1024);
            }
            
            // calculate the hard disk space the deleted image has used
            $deletedImagesFileSize = 0;
            
            for($i=0; $i<$deletedImagesCount; $i++)
            {
                // incrementally compute the file sizes of each deleted images in kilobytes
                // 1 KB = 1024 bytes
                $deletedImagePath = mysql_result($deletedImagesQuery,$i,"path");
                $deletedImagesFileSize = $deletedImagesFileSize + ((filesize($deletedImagePath)) / 1024);
            }
            
            // compute the total disk space that is being used
            $totalImagesFileSize = $savedImagesFileSize + $deletedImagesFileSize;
            
            // display the images information
            echo "
            <p>Image Information</p>
            <p id='box'>
            Saved Images : ".$savedImagesCount." (".round($savedImagesFileSize,2)." KB)<br>
            Deleted Images : ".$deletedImagesCount." (".round($deletedImagesFileSize,2)." KB)<br><br>
            Total Images : ".$totalImagesCount." (".round($totalImagesFileSize,2)." KB)<br>
            </p>";
    
            return;
        }
	}
?>