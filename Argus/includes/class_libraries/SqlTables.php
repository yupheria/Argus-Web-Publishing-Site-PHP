<?php
	/**
	 * Filename : SqlTables.php
	 * Description : Mysql Tables needed to run the application
	 * Date Created : February 2,2008
	 * Author : Arugs Team
	 */
	
	/**
	 * METHODS SUMMARY:
     *  getCreateTablesScripts
	 */
	
	class SqlTables
	{
        /** 
         * Get SQL table names method: returns the table names
         */
        function getCreateTablesScripts()
        {
            // create table for accounts
            $tablesQuery .= "CREATE TABLE `argus_accounts` (
                        `account_id` bigint(20) NOT NULL auto_increment,
                        `id_number` varchar(10) NOT NULL,
                        `username` varchar(255) NOT NULL,
                        `password` varchar(255) NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `position` varchar(255) NOT NULL,
                        `email` varchar(255) NOT NULL,
                        `last_login_date` varchar(10) NOT NULL,
                        `date_registered` varchar(10) NOT NULL,
                        `photo_path` varchar(255) NOT NULL,
                        `status` varchar(255) NOT NULL,
                        PRIMARY KEY  (`account_id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;\n";
            
            // create table for archives
            $tablesQuery .= "CREATE TABLE `argus_archives` (
                        `archive_id` bigint(20) NOT NULL auto_increment,
                        `article_id` bigint(20) NOT NULL,
                        `title` varchar(255) NOT NULL,
                        `issue` varchar(255) NOT NULL,
                        `year` varchar(10) NOT NULL,
                        `date_archived` varchar(10) NOT NULL,
                        `status` varchar(10) NOT NULL,
                        `path` varchar(255) NOT NULL,
                        PRIMARY KEY  (`archive_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;\n";
            
            // create table for article hits
            $tablesQuery .= "CREATE TABLE `argus_article_hits` (
                        `article_id` bigint(20) NOT NULL,
                        `hits` bigint(20) NOT NULL,
                        `used_ips` longtext NOT NULL,
                        PRIMARY KEY  (`article_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;\n";
                        
            // create table for article ratings
            $tablesQuery .= "CREATE TABLE `argus_article_ratings` (
                        `article_id` bigint(20) NOT NULL,
                        `total_value` bigint(11) NOT NULL,
                        `total_votes` bigint(20) NOT NULL,
                        `used_ips` longtext NOT NULL,
                        PRIMARY KEY  (`article_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;\n";
                        
            // create table for articles
            $tablesQuery .= "CREATE TABLE `argus_articles` (
                        `article_id` bigint(20) NOT NULL auto_increment,
                        `account_id` bigint(20) NOT NULL,
                        `category_id` bigint(20) NOT NULL,
                        `issue_id` bigint(20) NOT NULL,
                        `title` varchar(255) NOT NULL,
                        `intro` mediumtext NOT NULL,
                        `content` longtext NOT NULL,
                        `date_submitted` varchar(10) NOT NULL,
                        `date_modified` varchar(10) NOT NULL,
                        `date_published` varchar(10) NOT NULL,
                        `status` varchar(10) NOT NULL,
                        `position` varchar(10) NOT NULL,
                        `publish_type` varchar(10) NOT NULL,
                        `publish_position` varchar(10) NOT NULL,
                        PRIMARY KEY  (`article_id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;\n";
                        
            // create table for categories
            $tablesQuery .= "CREATE TABLE `argus_categories` (
                        `category_id` bigint(20) NOT NULL auto_increment,
                        `name` varchar(255) NOT NULL,
                        `description` varchar(255) NOT NULL,
                        `position` varchar(255) NOT NULL,
                        `date_created` varchar(10) NOT NULL,
                        `status` varchar(255) NOT NULL,
                        PRIMARY KEY  (`category_id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;\n";
            
            // create table for comments
            $tablesQuery .= "CREATE TABLE `argus_comments` (
                        `comment_id` bigint(20) NOT NULL auto_increment,
                        `account_id` bigint(20) NOT NULL,
                        `article_id` bigint(20) NOT NULL,
                        `comment` mediumtext NOT NULL,
                        `date_commented` varchar(255) NOT NULL,
                        `status` varchar(10) NOT NULL,
                        PRIMARY KEY  (`comment_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;\n";
            
            // create table for events
            $tablesQuery .= "CREATE TABLE `argus_events` (
                        `event_id` bigint(20) NOT NULL auto_increment,
                        `title` varchar(255) NOT NULL,
                        `content` mediumtext NOT NULL,
                        `month` int(10) NOT NULL,
                        `day` int(10) NOT NULL,
                        `year` int(10) NOT NULL,
                        `date_added` varchar(10) NOT NULL,
                        `status` varchar(10) NOT NULL,
                        PRIMARY KEY  (`event_id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;\n";
        
            // create table for images
            $tablesQuery .= "CREATE TABLE `argus_images` (
                        `image_id` bigint(20) NOT NULL,
                        `account_id` bigint(20) NOT NULL,
                        `name` varchar(255) NOT NULL,
                        `path` varchar(255) NOT NULL,
                        `description` varchar(255) NOT NULL,
                        `date_uploaded` varchar(10) NOT NULL,
                        `status` varchar(10) NOT NULL,
                        PRIMARY KEY  (`image_id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1;\n";
        
            // create table for infos
            $tablesQuery .= "CREATE TABLE `argus_infos` (
                        `name` varchar(255) NOT NULL,
                        `date_modified` varchar(10) NOT NULL,
                        `content` longtext NOT NULL,
                        PRIMARY KEY  (`name`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;\n";
        
            // create table for issues
            $tablesQuery .= "CREATE TABLE `argus_issues` (
                        `issue_id` bigint(20) NOT NULL auto_increment,
                        `name` varchar(255) NOT NULL,
                        `description` varchar(255) NOT NULL,
                        `date_created` varchar(10) NOT NULL,
                        `date_publish` varchar(10) NOT NULL,
                        `status` varchar(10) NOT NULL,
                        PRIMARY KEY  (`issue_id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;\n";
        
            // create table for mails
            $tablesQuery .= "CREATE TABLE `argus_mails` (
                        `mail_id` bigint(20) NOT NULL auto_increment,
                        `account_id` bigint(20) NOT NULL,
                        `sender_account_id` bigint(20) NOT NULL,
                        `subject` varchar(255) NOT NULL,
                        `content` longtext NOT NULL,
                        `date_received` varchar(10) NOT NULL,
                        `status` varchar(10) NOT NULL,
                        `type` varchar(10) NOT NULL,
                        PRIMARY KEY  (`mail_id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;\n";
        
            // create table for saved articles
            $tablesQuery .= "CREATE TABLE `argus_saved_articles` (
                        `saved_article_id` bigint(20) NOT NULL,
                        `account_id` bigint(20) NOT NULL,
                        `category_id` bigint(20) NOT NULL,
                        `title` varchar(255) NOT NULL,
                        `content` longtext NOT NULL,
                        `date_created` varchar(10) NOT NULL,
                        `date_modified` varchar(10) NOT NULL,
                        `status` varchar(10) NOT NULL,
                        `times_submitted` int(3) NOT NULL,
                        PRIMARY KEY  (`saved_article_id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1;\n";
        
            // create tables for slu students
            $tablesQuery .= "CREATE TABLE `argus_slu_students` (
                        `id_number` varchar(10) NOT NULL,
                        `first_name` varchar(255) NOT NULL,
                        `last_name` varchar(255) NOT NULL,
                        `middle_initial` varchar(1) NOT NULL,
                        `status` varchar(15) NOT NULL,
                        PRIMARY KEY  (`id_number`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;\n";
        
            // create tables for themes
            $tablesQuery .= "CREATE TABLE `argus_themes` (
                        `theme_id` bigint(20) NOT NULL auto_increment,
                        `name` varchar(255) NOT NULL,
                        `path` varchar(255) NOT NULL,
                        `status` varchar(10) NOT NULL,
                        PRIMARY KEY  (`theme_id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;\n";
                        
            return $tablesQuery;
        }
	}
?>