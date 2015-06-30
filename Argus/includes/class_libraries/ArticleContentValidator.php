<?php
    /**
     * Filename: ArticleContentValidator.php
     * Description: validates the content of the article
     * Date Created: January 5,2008
     * Author: Argus Team
     */
    
    /**
     * METHODS SUMMARY:
     *  boolean validateContent($content)
     */
    
    class ArticleContentValidator
    {
        /**
         * Validate Content Method: validates the content of the article if empty or not
         * Parameter: $content
         * Return Type: boolean
         */
        function validateContent($content)
        {
            // check if the content is empty by stripping the HTML tags but remain only the IMAGE tag
            $content = strip_tags($content,"<img>");
            
            // after trimming, remove the &nbsp; which is a space inside the content
            $content = str_replace("&nbsp;","",$content);
            
            // after replacing, trim the content to remove REAL spaces
            $content = trim($content);
            
            // check if the content is blank or not
            if($content == "")
            {
                return false;
            }
            else
            {
                return true;
            }
            
            return;
        }
    }
?>
