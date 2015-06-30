<?php
    /**
     * Filename: DirectoryDelete.php
     * Description: deletes a directory and it's contents completely
     * Author: Argus Team
     * Date Created : January 21,2008
     */

    class DirectoryDelete
    {
        /**
         * delete directory method: deletes the content of the directory and the directory itself
         * Parameter: $dir, $deleteMe
         */
        function deleteDirectory($dir, $DeleteMe)
        {
            // open the directory and check whether it exist or not
            if(!$dh = @opendir($dir))
            {
                // if it doesn't exist, return
                return;
            }
            
            // delete all the objects inside the directory
            while (($obj = readdir($dh)))
            {
                if($obj=='.' || $obj=='..')
                {
                    continue;
                }
                
                if (!@unlink($dir.'/'.$obj))
                {
                    deleteDirectory($dir.'/'.$obj, true);
                }
            }
    
            // delete the directory
            if (!$DeleteMe)
            {
                closedir($dh);
                @rmdir($dir);
            }
            
            return;
        }
    }
?>