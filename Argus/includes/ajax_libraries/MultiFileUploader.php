<?php
    /**
     * Filname : MultiFileUploader.php
     * Description : class file that contains the javascript code of the multiple file uploader
     * Date Created : December 5,2007
     * Author : By www.stickman.org Modified by Argus Team
     */
    
    /**
     * METHODS SUMMARY:
     *  MultiFileUploader($maximumImage)
     *  string displayScript()
     *  string displayFileQueueBox()
     */
    
    class MultiFileUploader
    {
        var $maximumImage;
        
        /**
         * Constructor Method
         * Parameter: $maximumImage
         */
        function MultiFileUploader($maximumImage)
        {
            // set the number of maximum image that is only allowed to be uploaded
            $this -> maximumImage = $maximumImage;
            
            return;
        }
        
        /**
         * Display Script Method: displays the java script which will be used for multi uploads
         * Return type: string
         */
        function displayScript()
        {
            // display the script with the maximum number of image
            echo "<script>";
            echo "
            function MultiSelector( list_target, max )
            {
                this.list_target = list_target;
                this.count = 0;
                this.id = 0;
                
                if( max )
                {
                    this.max = max;
                } 
                else 
                {
                    this.max = -1;
                };
                
                this.addElement = function( element )
                {
                    if( element.tagName == 'INPUT' && element.type == 'file' )
                    {
                        element.name = 'file[]';
                        element.multi_selector = this;
                        element.onchange = function()
                        {
                            var new_element = document.createElement( 'input' );
                            new_element.type = 'file';
                            this.parentNode.insertBefore( new_element, this );
                            this.multi_selector.addElement( new_element );
                            this.multi_selector.addListRow( this );
                            this.style.position = 'absolute';this.style.left = '-1000px';
                        };
                        
                        if( this.max != -1 && this.count >= this.max )
                        {
                            element.disabled = true;
                        };
                        
                        this.count++;
                        this.current_element = element;
                    } else {alert( 'Error: not a file input element' );};};this.addListRow = function( element ){var new_row = document.createElement( 'div' );var new_row_button = document.createElement( 'input' );new_row_button.id = 'submit1';new_row_button.type = 'button';new_row_button.value = 'Delete';new_row.element = element;new_row_button.onclick= function(){this.parentNode.element.parentNode.removeChild( this.parentNode.element );this.parentNode.parentNode.removeChild( this.parentNode );this.parentNode.element.multi_selector.count--;this.parentNode.element.multi_selector.current_element.disabled = false;return false;};new_row.innerHTML = element.value;new_row.appendChild( new_row_button );this.list_target.appendChild( new_row );};};";
            echo "</script>";
            
            return;
        }
        
        /**
         * Display File Queue Box method: shows the box where the file upload queues are contained
         * Return type: string
         */
        function displayFileQueueBox()
        {
            echo "
            <div id='files_list'></div>
            <script>
                <!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
                var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), ".$this -> maximumImage." );
                <!-- Pass in the file element -->
                multi_selector.addElement( document.getElementById( 'my_file_element' ) );
            </script>";
            
            return;
        }
    }
?>