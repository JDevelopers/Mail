<?php
/*
* SAXY Lite is a non-validating, but lightweight and fast SAX parser for PHP, modelled on the Expat parser
* @package saxy-xmlparser
* @subpackage saxy-xmlparser-lite
* @version 1.0
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/saxy/ SAXY Home Page
* SAXY is Free Software
*/

/** current version of SAXY Lite */
define ('SAXY_LITE_VERSION', '1.0');

/** initial saxy lite parse state, before anything is encountered */
define('SAXY_STATE_NONE', 0);
/** saxy lite parse state, processing main document */
define('SAXY_STATE_PARSING', 1);

/*
* SAXY_Parser_Base is a base class for SAXY and SAXY Lite
* @package saxy-xmlparser
* @version 1.0
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/saxy/ SAXY Home Page
* SAXY is Free Software
*/

/** the initial characters of a cdata section */
define('SAXY_SEARCH_CDATA', '![CDATA[');
/** the length of the initial characters of a cdata section */
define('SAXY_CDATA_LEN', 8);
/** the initial characters of a notation */
define('SAXY_SEARCH_NOTATION', '!NOTATION');
/** the initial characters of a doctype */
define('SAXY_SEARCH_DOCTYPE', '!DOCTYPE');
/** saxy parse state, just before parsing an attribute */
define('SAXY_STATE_ATTR_NONE', 0);
/** saxy parse state, parsing an attribute key */
define('SAXY_STATE_ATTR_KEY', 1);
/** saxy parse state, parsing an attribute value */
define('SAXY_STATE_ATTR_VALUE', 2);

/**
* The base SAX Parser class
*
* @package saxy-xmlparser
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class SAXY_Parser_Base {
	/** @var int The current state of the parser */
	var $state;
	/** @var int A temporary container for parsed characters */
	var $charContainer;
	/** @var Object A reference to the start event handler */
	var $startElementHandler;
	/** @var Object A reference to the end event handler */
	var $endElementHandler;
	/** @var Object A reference to the data event handler */
	var $characterDataHandler;
	/** @var Object A reference to the CDATA Section event handler */
	var $cDataSectionHandler = null;
	/** @var boolean True if predefined entities are to be converted into characters */
	var $convertEntities = true;
	/** @var Array Translation table for predefined entities */
	var $predefinedEntities = array('&amp;' => '&', '&lt;' => '<', '&gt;' => '>',
							'&quot;' => '"', '&apos;' => "'"); 
	/** @var Array User defined translation table for entities */
	var $definedEntities = array();
	/** @var boolean True if whitespace is to be preserved during parsing. NOT YET IMPLEMENTED! */
	var $preserveWhitespace = false;
	
		
	/**
	* Constructor for SAX parser
	*/					
	function SAXY_Parser_Base() {
		$this->charContainer = '';
	} /* SAXY_Parser_Base */
	
	/**
	* Sets a reference to the handler for the start element event 
	* @param mixed A reference to the start element handler 
	*/
	function xml_set_element_handler($startHandler, $endHandler) {
		$this->startElementHandler = $startHandler;
		$this->endElementHandler = $endHandler;
	} /* xml_set_element_handler */
	
	/**
	* Sets a reference to the handler for the data event 
	* @param mixed A reference to the data handler 
	*/
	function xml_set_character_data_handler($handler) {
		$this->characterDataHandler = $handler;
	} /* xml_set_character_data_handler */
	
	/**
	* Sets a reference to the handler for the CDATA Section event 
	* @param mixed A reference to the CDATA Section handler 
	*/
	function xml_set_cdata_section_handler($handler) {
		$this->cDataSectionHandler = $handler;
	} /* xml_set_cdata_section_handler */
	
	/**
	* Sets whether predefined entites should be replaced with their equivalent characters during parsing
	* @param boolean True if entity replacement is to occur 
	*/
	function convertEntities($truthVal) {
		$this->convertEntities = $truthVal;
	} /* convertEntities */
	
	/**
	* Appends an array of entity mappings to the existing translation table
	* 
	* Intended mainly to facilitate the conversion of non-ASCII entities into equivalent characters 
	* 
	* @param array A list of entity mappings in the format: array('&amp;' => '&');
	*/
	function appendEntityTranslationTable($table) {
		$this->definedEntities = $table;
	} /* appendEntityTranslationTable */
	

	/**
	* Gets the nth character from the end of the string
	* @param string The text to be queried 
	* @param int The index from the end of the string
	* @return string The found character
	*/
	function getCharFromEnd($text, $index) {
		$len = strlen($text);
		$char = $text{($len - 1 - $index)};
		
		return $char;
	} /* getCharFromEnd */
	
	/**
	* Parses the attributes string into an array of key / value pairs
	* @param string The attribute text
	* @return Array An array of key / value pairs
	*/
	function parseAttributes($attrText) {
		$attrText = trim($attrText);	
		$attrArray = array();
		$maybeEntity = false;			
		
		$total = strlen($attrText);
		$keyDump = '';
		$valueDump = '';
		$currentState = SAXY_STATE_ATTR_NONE;
		$quoteType = '';
		
		for ($i = 0; $i < $total; $i++) {								
			$currentChar = $attrText{$i};
			
			if ($currentState == SAXY_STATE_ATTR_NONE) {
				if (trim($currentChar !== '')) {
					$currentState = SAXY_STATE_ATTR_KEY;
				}
			}
			
			switch ($currentChar) {
				case "\t":
					if ($currentState == SAXY_STATE_ATTR_VALUE) {
						$valueDump .= $currentChar;
					}
					else {
						$currentChar = '';
					}
					break;
				
				case "\x0B": /* vertical tab */
				case "\n":
				case "\r":
					$currentChar = '';
					break;
					
				case '=':
					if ($currentState == SAXY_STATE_ATTR_VALUE) {
						$valueDump .= $currentChar;
					}
					else {
						$currentState = SAXY_STATE_ATTR_VALUE;
						$quoteType = '';
						$maybeEntity = false;
					}
					break;
					
				case '"':
					if ($currentState == SAXY_STATE_ATTR_VALUE) {
						if ($quoteType == '') {
							$quoteType = '"';
						}
						else {
							if ($quoteType == $currentChar) {
								if ($this->convertEntities && $maybeEntity) {
								    $valueDump = strtr($valueDump, $this->predefinedEntities);
									$valueDump = strtr($valueDump, $this->definedEntities);
								}
								
								$keyDump = trim($keyDump);
								$attrArray[$keyDump] = $valueDump;
								$keyDump = $valueDump = $quoteType = '';
								$currentState = SAXY_STATE_ATTR_NONE;
							}
							else {
								$valueDump .= $currentChar;
							}
						}
					}
					break;
					
				case "'":
					if ($currentState == SAXY_STATE_ATTR_VALUE) {
						if ($quoteType == '') {
							$quoteType = "'";
						}
						else {
							if ($quoteType == $currentChar) {
								if ($this->convertEntities && $maybeEntity) {
								    $valueDump = strtr($valueDump, $this->predefinedEntities);
									$valueDump = strtr($valueDump, $this->definedEntities);
								}
								
								$keyDump = trim($keyDump);
								$attrArray[$keyDump] = $valueDump;
								$keyDump = $valueDump = $quoteType = '';
								$currentState = SAXY_STATE_ATTR_NONE;
							}
							else {
								$valueDump .= $currentChar;
							}
						}
					}
					break;
					
				case '&':
					/* might be an entity */
					$maybeEntity = true;
					$valueDump .= $currentChar;
					break;
					
				default:
					if ($currentState == SAXY_STATE_ATTR_KEY) {
						$keyDump .= $currentChar;
					}
					else {
						$valueDump .= $currentChar;
					}
			}
		}

		return $attrArray;
	} /* parseAttributes */
	
	/**
	* Parses character data
	* @param string The character data
	*/
	function parseBetweenTags($betweenTagText) {
		if (trim($betweenTagText) !== ''){
			$this->fireCharacterDataEvent($betweenTagText);
		}
	} /* parseBetweenTags */
	
	/**
	* Fires a start element event
	* @param string The start element tag name
	* @param Array The start element attributes
	*/
	function fireStartElementEvent($tagName, $attributes) {
		call_user_func($this->startElementHandler, $this, $tagName, $attributes);
	} /* fireStartElementEvent */
	
	/**
	* Fires an end element event
	* @param string The end element tag name
	*/
	function fireEndElementEvent($tagName) {
		call_user_func($this->endElementHandler, $this, $tagName);
	} /* fireEndElementEvent */
	
	/**
	* Fires a character data event
	* @param string The character data
	*/
	function fireCharacterDataEvent($data) {
		if ($this->convertEntities && ((strpos($data, "&") != -1))) {
			$data = strtr($data, $this->predefinedEntities);
			$data = strtr($data, $this->definedEntities);
		}
		
		call_user_func($this->characterDataHandler, $this, $data);
	} /* fireCharacterDataEvent */
	
	/**
	* Fires a CDATA Section event
	* @param string The CDATA Section data
	*/
	function fireCDataSectionEvent($data) {
		call_user_func($this->cDataSectionHandler, $this, $data);
	} /* fireCDataSectionEvent */
} /* SAXY_Parser_Base */

/**
* The SAX Parser class
*
* @package saxy-xmlparser
* @subpackage saxy-xmlparser-lite
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class SAXY_Lite_Parser extends SAXY_Parser_Base {
	/**
	* Constructor for SAX parser
	*/
	function SAXY_Lite_Parser() {
		$this->SAXY_Parser_Base();
		$this->state = SAXY_STATE_NONE;
	} /* SAXY_Lite_Parser */

	/**
	* Returns the current version of SAXY Lite
	* @return Object The current version of SAXY Lite
	*/
	function getVersion() {
		return SAXY_LITE_VERSION;
	} /* getVersion */

	/**
	* Processes the xml prolog, doctype, and any other nodes that exist outside of the main xml document
	* @param string The xml text to be processed
	* @return string The preprocessed xml text
	*/
	function preprocessXML($xmlText) {
		/* strip prolog */
		$xmlText = trim($xmlText);
		$total = strlen($xmlText);

		for ($i = 0; $i < $total; $i++) {
			if ($xmlText{$i} == '<') {
				switch ($xmlText{($i + 1)}) {
					case '?':
					case '!':
						break;
					default:
						$this->state = SAXY_STATE_PARSING;
						return (substr($xmlText, $i));
				}
			}
		}
		return $xmlText;
	} /* preprocessXML */

	/**
	* The controlling method for the parsing process
	* @param string The xml text to be processed
	* @return boolean True if parsing is successful
	*/
	function parse ($xmlText) {
		$xmlText = $this->preprocessXML($xmlText);
		$total = strlen($xmlText);

		for ($i = 0; $i < $total; $i++) {
			$currentChar = $xmlText{$i};

			switch ($this->state) {
				case SAXY_STATE_PARSING:

					switch ($currentChar) {
						case '<':
							if (substr($this->charContainer, 0, SAXY_CDATA_LEN) == SAXY_SEARCH_CDATA) {
								$this->charContainer .= $currentChar;
							}
							else {
								$this->parseBetweenTags($this->charContainer);
								$this->charContainer = '';
							}
							break;

						case '>':
							if ((substr($this->charContainer, 0, SAXY_CDATA_LEN) == SAXY_SEARCH_CDATA) &&
								!(($this->getCharFromEnd($this->charContainer, 0) == ']') &&
								($this->getCharFromEnd($this->charContainer, 1) == ']'))) {
								$this->charContainer .= $currentChar;
							}
							else {
								$this->parseTag($this->charContainer);
								$this->charContainer = '';
							}
							break;

						default:
							$this->charContainer .= $currentChar;
					}

					break;
			}
		}

		return true;
	} /* parse */

	/**
	* Parses an element tag
	* @param string The interior text of the element tag
	*/
	function parseTag($tagText) {
		$tagText = trim($tagText);
		$firstChar = $tagText{0};
		$myAttributes = array();

		switch ($firstChar) {
			case '/':
				$tagName = substr($tagText, 1);
				$this->fireEndElementEvent($tagName);
				break;

			case '!':
				$upperCaseTagText = strtoupper($tagText);

				if (strpos($upperCaseTagText, SAXY_SEARCH_CDATA) !== false) { /* CDATA Section */
					$total = strlen($tagText);
					$openBraceCount = 0;
					$textNodeText = '';

					for ($i = 0; $i < $total; $i++) {
						$currentChar = $tagText{$i};

						if (($currentChar == ']') && ($tagText{($i + 1)} == ']')) {
							break;
						}
						else if ($openBraceCount > 1) {
							$textNodeText .= $currentChar;
						}
						else if ($currentChar == '[') { /* this won't be reached after the first open brace is found */
							$openBraceCount ++;
						}
					}

					if ($this->cDataSectionHandler == null) {
						$this->fireCharacterDataEvent($textNodeText);
					}
					else {
						$this->fireCDataSectionEvent($textNodeText);
					}
				}
				else if (strpos($upperCaseTagText, SAXY_SEARCH_NOTATION) !== false) { /* NOTATION node, discard */
					return;
				}
				else if (substr($tagText, 0, 2) == '!-') { /* comment node, discard */
					return;
				}

				break;

			case '?':
				/* Processing Instruction node, discard */
				return;

			default:
				if ((strpos($tagText, '"') !== false) || (strpos($tagText, "'") !== false)) {
					$total = strlen($tagText);
					$tagName = '';

					for ($i = 0; $i < $total; $i++) {
						$currentChar = $tagText{$i};

						if (($currentChar == ' ') || ($currentChar == "\t") ||
							($currentChar == "\n") || ($currentChar == "\r") ||
							($currentChar == "\x0B")) {
							$myAttributes = $this->parseAttributes(substr($tagText, $i));
							break;
						}
						else {
							$tagName .= $currentChar;
						}
					}

					if (strrpos($tagText, '/') == (strlen($tagText) - 1)) { /* check $tagText, but send $tagName */
						$this->fireStartElementEvent($tagName, $myAttributes);
						$this->fireEndElementEvent($tagName);
					}
					else {
						$this->fireStartElementEvent($tagName, $myAttributes);
					}
				}
				else {
					if (strpos($tagText, '/') !== false) {
						$tagText = trim(substr($tagText, 0, (strrchr($tagText, '/') - 1)));
						$this->fireStartElementEvent($tagText, $myAttributes);
						$this->fireEndElementEvent($tagText);
					}
					else {
						$this->fireStartElementEvent($tagText, $myAttributes);
					}
				}
		}
	} /* parseTag */
	
	/**
	* Returns the current error code (non-functional for SAXY Lite)
	* @return int The current error code
	*/
	function xml_get_error_code() { return -1; } /* ml_get_error_code */
	
	/**
	* Returns a textual description of the error code (non-functional for SAXY Lite)
	* @param int The error code
	* @return string The error message
	*/
	function xml_error_string($code) { CAdminPanel::Log('XML : '.$code); } /* xml_error_string */
} /* SAXY_Lite_Parser */