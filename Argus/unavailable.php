<?php
	/**
	 * Filename : unavailable.php
	 * Description : this page is shown whenever the site has been set to offline
	 * Date Created : January 21,2007
	 * Author : Argus Team
	 */
	
    // include the database connector
    include("includes/class_libraries/DatabaseConnector.php");
    $databaseConnector = new DatabaseConnector();
    
    // query the publication title and subtitle
    $publicationNameQuery = mysql_query("SELECT content FROM argus_infos WHERE name='publication_name'") or die(mysql_error());
    $publicationName = explode(";",mysql_result($publicationNameQuery,0,"content"));
    
    // query the title and the content, reason why the site is unavailable
    $siteOnlineQuery = mysql_query("SELECT content FROM argus_infos WHERE name='site_online'") or die(mysql_error());
    $siteOnline = explode(";",mysql_result($siteOnlineQuery,0,"content"));
    
    // arrange the content
    $title = $siteOnline[0];
    $content = $siteOnline[1];
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<title><?php echo $publicationName[0]." | ".$publicationName[1] ?></title>
<link href='miscs/css/default.css' rel='stylesheet' type='text/css'>
</head>
<div id='header'>
    <h1><?php echo $publicationName[0]; ?></h1>
    <h2><?php echo $publicationName[1]; ?></h2>
</div>
<!-- page content -->
<div id='content'>
    <div id='colTwo' style='width:892px'>
        <div class='bg2'>
            <?php
                // display the title
                echo "<h2><em>".$title."</em></h2>";
            ?>
        </div>
        <h3>Site Offline</h3>
        <div class='bg1'>
            <p>
            <?php
                // display the content
                echo $content;
            ?>
            </p>
        </div>
    </div>
</div>
<div id='footer'>
    <p>powered by argus</p>
</div>
</html>