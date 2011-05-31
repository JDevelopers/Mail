<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

class convertHtml
{
	var $html;
	var $text;
	var $width = 75;
	var $search = array(
		"/\r/",
		"/[\n\t]+/",                        
		'/<script[^>]*>.*?<\/script>/i',    
		'/<style[^>]*>.*?<\/style>/i',
		'/<title[^>]*>.*?<\/title>/i',
		'/<h[123][^>]*>(.+?)<\/h[123]>/i', 
		'/<h[456][^>]*>(.+?)<\/h[456]>/i', 
		'/<p[^>]*>/i',          
		'/<br[^>]*>/i',         
		'/<b[^>]*>(.+?)<\/b>/i',
		'/<i[^>]*>(.+?)<\/i>/i',
		'/(<ul[^>]*>|<\/ul>)/i',
		'/(<ol[^>]*>|<\/ol>)/i',
		'/<li[^>]*>/i', 
		'/<a[^>]*href="([^"]+)"[^>]*>(.+?)<\/a>/i', 
		'/<hr[^>]*>/i',               
		'/(<table[^>]*>|<\/table>)/i',
		'/(<tr[^>]*>|<\/tr>)/i',      
		'/<td[^>]*>(.+?)<\/td>/i',    
		'/<th[^>]*>(.+?)<\/th>/i',    
		'/&nbsp;/i',
		'/&quot;/i',
		'/&gt;/i',
		'/&lt;/i',
		'/&amp;/i',
		'/&copy;/i',
		'/&trade;/i',
		'/&#8220;/',
		'/&#8221;/',
		'/&#8211;/',
		'/&#8217;/',
		'/&#38;/',
		'/&#169;/',
		'/&#8482;/',
		'/&#151;/',
		'/&#147;/',
		'/&#148;/',
		'/&#149;/',
		'/&reg;/i',
		'/&bull;/i',
		'/&[&;]+;/i',
		'/&#39;/',
		'/&#160;/'
	);

	var $replace = array(
		'',								
		' ',							
		'',								
		'',
		'',
		"\n\n\\1\n\n",
		"\n\n\\1\n\n", 
		"\n\n\t",
		"\n",   
		'\\1',	
		'\\1',	
		"\n\n",  
		"\n\n",  
		"\n\t* ",
		'\\2 (\\1)',
		"\n------------------------------------\n",
		"\n",                     
		"\n",                     
		"\t\\1\n",                
		"\t\\1\n",
		' ',
		'"',
		'>',
		'<',
		'&',
		'(c)',
		'(tm)',
		'"',
		'"',
		'-',
		"'",
		'&',
		'(c)',
		'(tm)',
		'--',
		'"',
		'"',
		'*',
		'(R)',
		'*',
		'',
		'\'',
		''
	);

	var $allowed_tags = '';
	var $url;
	var $_isrep = false;
	var $_link_list;
	var $_withrefs = false;

	function convertHtml( $html = '', $withHrefs = false)
	{
		if (!empty($html))
		{
			$this->set_html($html);
		}
		$this->_withrefs = $withHrefs;
	}

	function set_html($source)
	{
		$this->html = str_replace('$', 'S', $source);
		$this->_isrep = false;
	}

	function get_text()
	{
		if (!$this->_isrep) 
		{
			$this->_convert();
		}
		return $this->text;
	}

	function print_text()
	{
		print $this->get_text();
	}

	function set_allowed_tags($allowed_tags = '')
	{
		if (!empty($allowed_tags))
		{
			$this->allowed_tags = $allowed_tags;
 		}
	}

	function _convert()
	{
		/* $link_count = 1; */
		$this->_link_list = '';

		$text = trim(stripslashes($this->html));

		$text = preg_replace($this->search, $this->replace, $text);
		
		$text = strip_tags($text, $this->allowed_tags);

		$text = preg_replace("/\n\\s+\n/", "\n", $text);
		$text = preg_replace("/[\n]{3,}/", "\n\n", $text);

		/*
		if (!empty($this->_link_list)) 
		{
			$text .= "\n\nLinks:\n------\n" . $this->_link_list;
		}
		*/

		if ($this->width > 0) 
		{
			$text = wordwrap($text, $this->width);
		}

		$this->text = $text;
		$this->_isrep = true;
	}
}
