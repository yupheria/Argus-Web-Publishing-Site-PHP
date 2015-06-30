<?php
	/**
	 * Filename : TextLimiter.php
	 * Description : Limits text to a number of strings
	 * Date Created : January 19,2008
	 * Author : Argus Team
	 */
	
    /**
	 * METHODS SUMMARY:
     *  string LimitText($text, $limit)
     */
	
	class TextLimiter
	{
        /**
         * Limit text method: limits the text
         * Paramater: $text, $limit
         * Return type: string
         */
        function limitText($text, $limit)
        {
            if (str_word_count($text) > $limit)
            {
                $words = str_word_count($text, 2);
                $pos = array_keys($words);
                $text = substr($text, 0, $pos[$limit]) . '...';
            }
            
            return $text;
        }
	}
?>