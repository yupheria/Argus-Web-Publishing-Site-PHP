<?php
    /**
     * Filname : ToolTip.php
     * Description : class file that contains the javascript code for tooltips
     * Date Created : December 13,2007
     */
    
    /**
     * METHODS SUMMARY:
     *  string setupForm();
     */
    
    class ToolTip
    {
        /**
         * Set Up Form method: set's up the javascript on the form
         * Return type: string
         */
        function setupForm()
        {
            // load the tool tip java script
            echo "
            <link href='../miscs/js/tool_tip/css/tooltip.css' rel='stylesheet'>
            <script type='text/javascript' src='../miscs/js/tool_tip/BubbleToolTip.js'></script>
            <script type='text/javascript'>
                window.onload=function(){enableTooltips()};
            </script>";
            
            return;
        }
    }
?>