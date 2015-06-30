<?php
    /**
     * Filename : ImageNameValidator.php
     * Description: Validates an uploaded file if it is an image or not
     * Date Created : December 4, 2007
     * Author : Argus Team
     */
    
    /**
     * METHODS SUMMARY:
     *  boolean validateImage($image)
     *  string getErrors()
     */
    
    class ImageNameValidator
    {
        var $errors;
        
        /**
         * Validate Image method: validates the extension of the image if it's an image or not
         * Parameters: $image
         * Return Type: boolean
         */
        function validateImage($name)
        {
            // check if the image path is empty
            if($name == null)
            {
                // set an error that the image path is empty
                $this -> errors = "Please select the image you want to upload";
                
                // return unsuccessful validation
                return false;
            }
            else
            {
                // get the extension of the image path
                // this splits the name into an array where there is a "." sign and then gets the very end part of the
                // name where obviously most file extensions are located at the end then set the extension to UPPER CASE
                $extension = strtoupper(end(explode(".", $name)));
                
                
                // validate the extension if valid
                if($extension == "JPG" || $extension == "JPEG" || $extension == "GIF" || $extension == "PNG")
                {
                    // return a successful validation
                    return true;
                }
                else
                {
                    // set an error
                    $this -> errors = "Please upload a valid image file";
                    
                    // return an unsuccessful validation
                    return false;
                }
            }
        
            return;
        }
    
        /**
         * Get Errors method: returns the errors that was committed during the validation of image
         * Return type: string
         */
        function getErrors()
        {
            // return the errors
            return $this -> errors;
        }
    }
?>
