<?php
    /**
     * Filename: ImageResizer
     * Description: resizes images
     * Author: Argus Team
     * Date Created : January 3,2008
     */

     /**
      * METHODS SUMMARY:
      * ImageResizer($imagePath)
      * void resizeImage($width, $height)
      */

    class ImageResizer
    {
        var $imagePath;
        
        /**
         * Constructor method
         * Parameter: $imagePath
         */
        function ImageResizer($imagePath)
        {
            // set the image path
            $this -> imagePath = $imagePath;
            
            return;
        }
        
        /**
         * Resize Image method: resizes the image
         * Parameter: $width, $height
         */
        function resizeImage($width, $height)
        {
            $ext = explode(".", $this -> imagePath);
            $ext = $ext[count($ext)-1];

            if($ext == "jpg" || $ext == "jpeg")
                $im = imagecreatefromjpeg($this -> imagePath);
            elseif($ext == "png")
                $im = imagecreatefrompng($this -> imagePath);
            elseif($ext == "gif")
                $im = imagecreatefromgif($this -> imagePath);
            
            $x = imagesx($im);
            $y = imagesy($im);
            
            if($x <= $width && $y <= $height)
                return $im;

            if($x >= $y) {
                $newx = $width;
                $newy = $newx * $y / $x;
            }
            else {
                $newy = $height;
                $newx = $x / $y * $newy;
            }
            
            $im2 = imagecreatetruecolor($newx, $newy);
            imagecopyresized($im2, $im, 0, 0, 0, 0, floor($newx), floor($newy), $x, $y);
            
            // over write the file
            if($ext == "jpg" || $ext == "jpeg")
            {
                imagejpeg($im2, $this -> imagePath,100);
            }
            else if($ext == "png")
            {
                imagepng($im2, $this -> imagePath,100);
            }
            else if($ext == "gif")
            {
                imagegif($im2, $this -> imagePath);
            }
                
        }
    }
?>