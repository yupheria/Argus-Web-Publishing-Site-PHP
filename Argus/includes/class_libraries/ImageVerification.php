<?php
    /**
     * Filename: ImageVerification.php
     * Description: Creates an image for verification
     * Date Created: January 11,2008
     * Author: Argus Team
     */
     
    /**
     * METHODS SUMMARY:
     *  ImageVerification()
     *  image createImage()
     *  string getImageValue()
     */
    
    class ImageVerification
    {
        var $rand;
        
        /**
         * Constructor method
         */
        function ImageVerification()
        {
            // send several headers to make sure the image is not cached    
            // taken directly from the PHP Manual
            
            // Date in the past 
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            
            // always modified 
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            
            // HTTP/1.1 
            header("Cache-Control: no-store, no-cache, must-revalidate"); 
            header("Cache-Control: post-check=0, pre-check=0", false); 
            
            // HTTP/1.0 
            header("Pragma: no-cache");
            
            // send the content type header so the image is displayed properly
            // header('Content-type: image/jpeg');

            return;
        }
        
        /**
         * Create Image Method: creates a random image
         * Return Type: image
         */
        function createImage()
        {
            
            // make a string with all the characters that we 
            // want to use as the verification code
            $alphanum  = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            
            // generate the verication code 
            $this -> rand = substr(str_shuffle($alphanum), 0, 5);
            
            // create an image object using the chosen background
            $image = imagecreatefromjpeg("miscs/images/Default/imageverificationbground.jpg");
            
            
            $textColor = imagecolorallocate ($image, 0, 0, 0); 
            
            // write the code on the background image
            imagestring ($image, 5, 5, 8,  $this -> rand, $textColor); 
                        
            // send the image to the browser
            imagejpeg($image, "miscs/images/Default/imageverification.jpg");

            // destroy the image to free up the memory
            imagedestroy($image);
            
            return;
        }
        
        /**
         * Get Image Value Method: gets the value of the image
         * Return Type: string
         */
        function getImageValue()
        {
            // return the image value
            return $this -> rand;
        }
    }
?>