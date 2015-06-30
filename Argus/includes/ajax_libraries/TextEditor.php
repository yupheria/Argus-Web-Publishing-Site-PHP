<?php
    /**
     * Filname : TextEditor.php
     * Description : displays the text editor script
     * Date Created : February 2,2008
     */
    
    /**
     * METHODS SUMMARY:
     *  string setupTextEditor($editorType);
     */
    
    class TextEditor
    {   
        /**
         * Set Up Form method: set's up the javascript on the form
         * Parameter: $editorType
         * Return type: string
         */
        function setupTextEditor($editorType)
        {
            // query the current theme of the text editors
            $editorThemeQuery = mysql_query("SELECT content FROM argus_infos WHERE name='editor_theme'") or die(mysql_error());
            $editorTheme = mysql_result($editorThemeQuery,0,"content");
            
            echo "<script src='../miscs/js/tiny_mce/tiny_mce.js' type='text/javascript'></script>";
            
            // check the editor type
            if($editorType == "SIMPLE")
            {
                // check the theme and set the javascript code
                if($editorTheme == "none")
                {
                    echo "<script src='../miscs/js/tiny_mce/configuration/tiny_mce_simple.js' type='text/javascript'></script>";
                }
                else if($editorTheme == "word")
                {
                    echo "<script src='../miscs/js/tiny_mce/configuration/tiny_mce_simple_word.js' type='text/javascript'></script>";
                }
                else if($editorTheme == "silver")
                {
                    echo "<script src='../miscs/js/tiny_mce/configuration/tiny_mce_simple_silver.js' type='text/javascript'></script>";
                }
            }
            else if($editorType == "ADVANCED")
            {
                // check the theme and set the javascript code
                if($editorTheme == "none")
                {
                    echo "<script src='../miscs/js/tiny_mce/configuration/tiny_mce_full.js' type='text/javascript'></script>";
                }
                else if($editorTheme == "word")
                {
                    echo "<script src='../miscs/js/tiny_mce/configuration/tiny_mce_full_word.js' type='text/javascript'></script>";
                }
                else if($editorTheme == "silver")
                {
                    echo "<script src='../miscs/js/tiny_mce/configuration/tiny_mce_full_silver.js' type='text/javascript'></script>";
                }
                
            }
            
            return;
        }
    }
?>