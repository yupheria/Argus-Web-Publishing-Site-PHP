<?php
    /**
     * Filename : ImagesForm.php
     * Description : class file that contains the components and properties for managing uploaded images
     * Date Created : December 4, 2007
     * Author : Arugs Team
     */
    
    /**
     * METHODS SUMMARY:
     *  ImagesForm($accountId)
     *  string displayBanner()
     *  string displayImages($status)
     *  string displayImageStatistics($imageId)
     *  boolean uploadImage($imageId, $name, $tmpName)
     *  string validateName($name)
     *  string getErrors();
     *  void removeImage($imageId)
     *  void restoreImage($imageId)
     *  void deleteImage($imageId)
     *  void deleteRemovedImages()
     *  boolean updateImage($imageId, $name, $description)
     *  string validateImageName($name)
     */
    
    class ImagesForm
    {
        var $accountId;
        var $errors;
         
        /**
         * Constructor method
         * Parameter: accountId
         */
        function ImagesForm($accountId)
        {
            // set the global accountId which will be used by other methods of this class when managing images
            $this -> accountId = $accountId;
            
            return;
        }
        
        /**
         * Display Banner method: displays the banner and menu for managing images
         * Return Type: string
         */
        function displayBanner()
        {
            echo "
            <div class='bg2'>
            <h2><em>Image Manager</em></h2>
            <p align='center'>";
            
            // menus
            echo "
            <a href='images.php'>Saved</a> . 
            <a href='images.php?event=deleted'>Deleted</a> . 
            <a href='imagesupload.php'>Upload</a>";
            
            echo "
            </p>
            </div>";
            
            return;
        }
        
        /**
         * Display Images method: displays images depending on what status of image is going to be displayed
         * Parameter: $status
         * Return type: string
         */
        function displayImages($status)
        {
            // query the images of the user given the status
            $imagesQuery = mysql_query("SELECT image_id, name, path, date_uploaded FROM argus_images WHERE status = '".$status."' AND account_id = '".$this -> accountId."' ORDER BY date_uploaded DESC") or die(mysql_error());
            
            // set the title of the form setting the status to lower case then capitalize the first letter of the status
            echo "
            <h3>".ucfirst(strtolower($status))."</h3>
            <div class='bg1' id='tablePanel'>";
            
            // check if there are images queried from the database
            if(mysql_num_rows($imagesQuery) == 0)
            {
                // notify the user that there are no images for the current account
                echo "
                <p><h3 align='center'>There are no ".$status." images</h3></p>";
            }
            else
            {
                // inlcude the TOOL TIP ajax and create a tool tip
                include("ajax_libraries/ToolTip.php");
                $toolTip = new ToolTip();
                $toolTip -> setupForm();
                
                // include the checkbox funtions where check box are allowed to be selected/unselected all
                echo "<script src='../miscs/js/checkbox_toggle/checkboxtoggler.js' type='text/javascript'></script>";
                
                // set the form of the page and then display the images
                echo "
                <form id='form_id' method='post' action='".$_SERVER['PHP_SELF']."?event=".strtolower($status)."'>
                <table width='100%'>
                <tr>
                <th><input type='checkbox' onClick='toggleCheckBoxes(\"imageIds\")'></th>
                <th>Name</th>
                <th>Size</th>
                <th>Date</th>
                <th>Action</th>
                </tr>";
                
                // display the images
                $color = true;
                
                for($i=0; $i < mysql_num_rows($imagesQuery); $i++)
                {
                    // display the images in an alternate color manner
                    if($color == true)
                    {
                        echo "
                        <tr class='bg1'>";
                        $color = false;
                    }
                    else
                    {
                        echo "
                        <tr>";
                        $color = true;
                    }
                    
                    // set the attributes
                    $imageId = mysql_result($imagesQuery,$i,"image_id");
                    $name = stripslashes(mysql_result($imagesQuery,$i,"name"));
                    $dateUploaded = date("m/d/y", mysql_result($imagesQuery,$i,"date_uploaded"));
                    
                    // compute the file size of the image in kilobytes
                    // 1024 bytes = 1 KB
                    $fileSize = round((filesize(mysql_result($imagesQuery,$i,"path"))) / 1024, 2)." kb";
                    
                    // display the attributes
                    echo "
                    <td><input type='checkbox' name='imageIds[]' value='".$imageId."'></td>
                    <td><a href='images.php?event=statistics&image=".$imageId."'>".$name."</a>
                    <td>".$fileSize."</td>
                    <td>".$dateUploaded."</td>
                    <td>
                    <a href='imageedit.php?event=edit&image=".$imageId."' title='Edit'><img src='../miscs/images/Default/image_edit.png'></a> ";
                    
                    // set the actions
                    if($status == "SAVED")
                    {
                        // actions for SAVED images
                        echo "
                        <a href='images.php?event=".strtolower($status)."&action=remove&image=".$imageId."' title='Remove'><img src='../miscs/images/Default/article_trash.png'></a>";
                    }
                    else
                    {
                        // actions for DELETED images
                        echo "
                        <a href='images.php?event=".strtolower($status)."&action=restore&image=".$imageId."' title='Restore'><img src='../miscs/images/Default/image_restore.png'></a> 
                        <a href='images.php?event=".strtolower($status)."&action=delete&image=".$imageId."' title='Delete'><img src='../miscs/images/Default/image_delete.png'></a>";
                    }
                    
                    echo "
                    </td>
                    </tr>";
                }
                
                echo "</table>";
                
                // set the buttons for managing the images
                echo "
                <table width='100%'>
                <tr><td>";
                
                if($status == "SAVED")
                {
                    // buttons for SAVED images
                    echo "
                    <input type='submit' id='submit1' value='remove' name='remove'>";
                }
                else
                {
                    // buttons for DELETED images
                    echo "
                    <input type='submit' id='submit1' value='restore' name='restore'> 
                    <input type='submit' id='submit1' value='delete' name='delete'> 
                    <input type='submit' id='submit1' value='delete all' name='deleteAll'>";
                }
                
                echo "
                </td></tr>
                </table>
                </form>";
            }
            
            echo "</div>";
            
            return;
        }
        
        /**
         * Display Image Statistics Method: displays the information about a particular image
         * Parameter: $imageId
         * Return type: string
         */
        function displayImageStatistics($imageId)
        {
            // query the information of the image from the database
            $imageQuery = mysql_query("SELECT name, path, description, date_uploaded, status FROM argus_images WHERE image_id = '".$imageId."' AND account_id = '".$this -> accountId."'") or die(mysql_error());
            
            // check if there is an information queried from the database
            if(mysql_num_rows($imageQuery) > 0)
            {
                // set the queried information
                $name = mysql_result($imageQuery,0,"name");
                $path = mysql_result($imageQuery,0,"path");
                $description = mysql_result($imageQuery,0,"description");
                $dateUploaded = date("F d, Y", mysql_result($imageQuery,0,"date_uploaded"));
                $status = mysql_result($imageQuery,0,"status");
                
                // display the title of the form and then display the image information
                echo "
                <h3><a href='images.php?event=".strtolower($status)."'>".ucfirst(strtolower($status))."</a> &raquo ".$name."</h3>
                <div class='bg1'>
                <p>Image Information</p>
                <p id='box'>
                    Name : ".$name."<br>
                    Description : ".$description."<br>
                    Date Uploaded : ".$dateUploaded."<br>
                    Status : ".$status."<br>
                </p>";
                
                // display the buttons for managing the image
                echo "
                <p align='right'>
                <a href='imageedit.php?event=edit&image=".$imageId."'><input type='button' value='edit' id='submit1'></a> ";
                
                if($status == "SAVED")
                {
                    // buttons for saved images
                    echo "
                    <a href='images.php?event=".strtolower($status)."&action=remove&image=".$imageId."'><input type='button' value='remove' id='submit1'></a>";
                }
                else
                {
                    // buttons for deleted images
                    echo "
                    <a href='images.php?event=".strtolower($status)."&action=restore&image=".$imageId."'><input type='button' value='restore' id='submit1'></a> 
                    <a href='images.php?event=".strtolower($status)."&action=delete&image=".$imageId."'><input type='button' value='delete' id='submit1'></a>";
                }
                
                echo "
                </p>";
                
                // display the image
                echo "
                <p align='center'>
                    <img src='".$path."'>
                </p>";
                
                echo "</div>";
            }
            
            return;
        }
        
        /**
         * Upload Image method: uploads an image to the server side
         * Parameter: $imageId, $imageName, $imageTmpName
         * Return type: boolean
         */
        function uploadImage($imageId, $name, $tmpName)
        {
            // validate the image name if the image is really an image file
            $nameError = $this -> validateName($name);
            
            // check for errors
            if($nameError == null)
            {
                // get the extension of the the image
                $extension = end(explode(".", $name));
                
                // set the path where to store the images then attach the image name with the extension
                $path = "../images/client/".$imageId.".".$extension;
                
                // upload the image to the server renaming the PHYSICAL image name to it's SAVED IMAGE ID
                move_uploaded_file($tmpName, $path);
                
                // update the database after moving the uploaded file to the server
                mysql_query("INSERT INTO argus_images(image_id, account_id, name, path, date_uploaded, status)
                             VALUES ('".$imageId."', '".$this -> accountId."', '".$name."', '".$path."', '".time()."','SAVED')") or die(mysql_error());
            
                // return successful upload of image
                return true;
            }
            else
            {
                // set the error
                $this -> errors = "The image '".$name."' is not a valid image file";
                
                // return unsuccessful validation
                return false;
            }
            
            return;
        }
        
        /**
         * Validate Image path method: validates an image if the file being uploaded is an image
         * Parameter: $clientImagePath
         * Return type: string
         */
        function validateName($name)
        {
            // include the image name validator class and create an image validator
            require_once("class_libraries/ImageNameValidator.php");
            $imageValidator = new ImageNameValidator();
            
            // validate the image
            $result = $imageValidator -> validateImage($name);
            
            // check the result
            if($result == false)
            {
                // if validation failed, get the error that was committed then return the error
                return $imageValidator -> getErrors();
            }
            
            return;
        }
        
        /**
         * Get errors method: returns the error that was committed during the upload of images
         * Return type: string
         */
        function getErrors()
        {
            // return the errors
            return $this -> errors;
        }
        
        /**
         * Remove Image method: removes an image and sends it to the trash section
         * Parameter: $accountId
         */
        function removeImage($imageId)
        {
            // remove the image
            mysql_query("UPDATE argus_images SET status = 'DELETED' WHERE image_id = '".$imageId."' AND account_id = '".$this -> accountId."' AND status = 'SAVED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Restore Image method: restores an image and sends it to the saved section
         * Parameter: $accountId
         */
        function restoreImage($imageId)
        {
            // restore the image
            mysql_query("UPDATE argus_images SET status = 'SAVED' WHERE image_id = '".$imageId."' AND account_id = '".$this -> accountId."' AND status = 'DELETED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Delete Image method: deletes an image permanently
         * Parameter: $imageId
         */
        function deleteImage($imageId)
        {
            // query the path of the image from the database
            $pathQuery = mysql_query("SELECT path FROM argus_images WHERE image_id = '".$imageId."' AND account_id = '".$this -> accountId."' AND status = 'DELETED'") or die(mysql_error());
            $path = mysql_result($pathQuery,0,"path");
            
            // delete the file from the file server using the path queried
            unlink($path);
            
            // delete the file information in the database
            mysql_query("DELETE FROM argus_images WHERE image_id = '".$imageId."' AND account_id = '".$this -> accountId."' AND status = 'DELETED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Delete Removed Images method: deletes all images in the trash
         */
        function deleteRemovedImages()
        {
            // query all the paths of the deleted images
            $pathQuery = mysql_query("SELECT path FROM argus_images WHERE account_id = '".$this -> accountId."' AND status = 'DELETED'") or die(mysql_error());
            
            // delete the images in the file server
            for($i=0; $i<mysql_num_rows($pathQuery); $i++)
            {
                $path = mysql_result($pathQuery,$i,"path");
                unlink($path);
            }
            
            // delete all the images in the database
            mysql_query("DELETE FROM argus_images WHERE account_id = '".$this -> accountId."' AND status = 'DELETED'") or die(mysql_error());
            
            return;
        }
        
        /**
         * Update Image Method: updates the properties of the image
         * Parameter: $imageId, $name, $description
         * Return Type: boolean
         */
        function updateImage($imageId, $name, $description)
        {
            // validate the name
            $nameError = $this -> validateImageName($name);
            
            // check the errors
            if($nameError == null)
            {
                // escape the strings of the name to avoid sql injection
                $name = mysql_escape_string($name);
                $description = mysql_escape_string($description);
                
                // update the image properties
                mysql_query("UPDATE argus_images SET name='".$name."', description = '".$description."' WHERE image_id = '".$imageId."'") or die(mysql_error());
                
                // return successful update of image
                return true;
            }
            else
            {
                // set the errors
                $this -> errors = array("name" => $nameError);
                
                // return unsuccessful update of image
                return false;
            }
            
            return;
        }
        
        /**
         * Validate Image Name Method: validates the syntax of the image
         * Parameter: $name
         * Return Type: string
         */
        function validateImageName($name)
        {
            // include the name validator class and validate the name minimum of 1 character and maximum of 15 characters
            include("class_libraries/TitleValidator.php");
            $titleValidator = new TitleValidator(5,100);
            
            // validate the image name
            $result = $titleValidator -> validateTitle($name);
            
            // check the result
            if($result == false)
            {
                // get the error that was committed and return it
                return $titleValidator -> getErrors();
            }
            
            return;
        }
    }
?>