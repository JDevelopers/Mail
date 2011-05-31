<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

/*
 * Project:     AfterLogic Ajax Spellchecker
 * File:        class_spelldictionary.php
 *
 * @link http://afterlogic.com
 * @author  Penkin Vladimir aka wired_mugen
 * @package AfterLogic Ajax Spellchecker
 * @version 0.7.0
 */

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/../'));
		
$spell_err = array();
$spell_err[1] = "File not found";
$spell_err[2] = "Cannot read the file, check premissions on this file";
$spell_err[3] = "Not enought sections in dictionary file?";
$spell_err[21] = "Word not found";
$spell_err[22] = "Prefix not found";
$spell_err[23] = "Suffix not found";

include_once(WM_ROOTPATH.'common/utf8utils/class_convert.php');

$conv = new CConvertUtf8();

function StripSpaces($text)
{
	return preg_replace('[\s+]', ' ', $text);		
}


class SpellcheckerDictionary {

	var $copyright = Array();
	var $dicFileName;
	var $possibleBaseWords = Array();
	var $replaceCharacters = Array();
	var $prefixRules = Array();
	var $suffixRules = Array();
	var $baseWords = Array();
	var $tryCharacters;
	var $wordsCount;
	var $_error = '';
	
	/* saved for the future */
	var $dictionaryFolder;
	var $enableUserFile = false;
	var $userFile = '';
	var $photenicCharacters = Array();
	
	/**
	 * Class constructor
	 * @param string $fileName
	 */
	function SpellcheckerDictionary($fileName)
	{
		$this->tryCharacters = '';
		$this->wordsCount = 0;
		$this->dicFileName = $fileName;
		$this->Init();
	}
	
	/**
	 * Parse a Affix file
	 * @return int
	 */
	function Init()
	{
		$tmp = '';
		$sectionsCrc = 0;
		$currentSection = '';
		$matchs = array(); 
		if (!@file_exists($this->dicFileName))
		{
			$this->_error = 'Spellcheck: Dictionary file not found';
			return false; 
		}
		else if (!@is_readable($this->dicFileName))
		{ 
				$this->_error = 'Spellcheck: Cannot read the file, check premissions on this file';
				return false;
		}
		else
		{
			$dicFile = @fopen($this->dicFileName, 'r');
			if ($dicFile)
			{
				while (!@feof($dicFile))
				{
						/* $this->wordsCount++; */
						$tmp = trim(@fgets($dicFile));
						if (preg_match('/(\[Copyright\])|(\[Try\])|(\[Replace\])|(\[Prefix\])|(\[Suffix\])|(\[Phonetic\])|(\[Words\])/', $tmp, $matchs))
						{
							$sectionsCrc++;
							$currentSection = $matchs[0];
							continue;
						}
						
						switch ($currentSection)
						{
							case '[Copyright]' :
								$this->copyright[] = $tmp;
								break;
							case '[Try]' : 
								$this->tryCharacters.= $tmp;
								break;
							case '[Replace]' : 
								if ($tmp != '')
								{
									$this->replaceCharacters[] = $tmp;
								}
								break;
							case '[Phonetic]' :
								$this->phoneticCharacters[] = $tmp;
								break;
							case '[Words]' : 
								$wparts = explode('/', $tmp);
								$wcount = count($wparts);
								if ($wcount >= 3)
								{
									$this->baseWords[$wparts[0]] = $wparts[1] . '/' .$wparts[2];
								} 
								else if ($wcount == 2)
								{
									$this->baseWords[$wparts[0]] = $wparts[1];
								} 
								else if ($wcount == 1)
								{
									$this->baseWords[$wparts[0]] = '';
								}
								break;
							
							case '[Suffix]' :
							case '[Prefix]' :
								$match = explode(' ', StripSpaces($tmp));
								$matchCount = count($match);
								if ($matchCount == 3)
								{
									$kk = $match[0];
									if ($currentSection == '[Prefix]')
									{
										$this->prefixRules[$kk][] = $match[1];
									}
									else if ($currentSection == '[Suffix]')
									{
										$this->suffixRules[$kk][] = $match[1];
									}
								}
								elseif ($matchCount == 4)
								{
									if ($currentSection == '[Prefix]')
									{
										$kkk = $match[0];
										/*  0 - name,	1 - stripchar,	2 - affix,	3 - conditions */
										$this->prefixRules[$kkk][] = Array($match[0], $match[1], $match[2], $match[3]);
									}
									else if ($currentSection == '[Suffix]')
									{
										$kkk = $match[0];
										$this->suffixRules[$kkk][] = Array($match[0], $match[1], $match[2], $match[3]);
									}
								}
							break;
						}
				}
			
				if ($sectionsCrc != 7)
				{
					$this->_error = 'Spellcheck: Dictoinary file is wrong or corrupted';
					return false;
				}
			
			}
			else
			{
				$this->_error = 'Spellcheck: Cannot read the file, check premissions on this file';
				return false; 
			}
		}
		return true;
	}
	
	/**
	* Verifies the base word has the affix key
	* Return true if word contains affix key
	* @param string $word
	* @param string $affixKey
	* @return boolean
	*/
	function VerifyAffixKey($word, $affixKey)
	{
		global $conv;
		
		$cword = $conv->StrToLower($word);
		if (isset($this->baseWords[$word]) || isset($this->baseWords[$cword]))
		{
			$key = (isset($this->baseWords[$word])) ? $this->baseWords[$word] : $this->baseWords[$cword];
			if ($key == '')
			{ 
				return false; 
			}
			else
			{
				if ($conv->StrPos($key, '/') !== false)
				{ 
					$piece = explode('/', $key);
					$key = $piece[0];
				}
				
				return ($conv->StrPos($key, $affixKey) !== false);
			}
		} 
		else
		{
			return 21;
		}
	}
	
	
	/**
	* Check that word matches prefix-rule
	* Return true if word matches rule
	* @param string $word
	* @param string $rule
	* @return boolean
	*/
	function CheckPrefixRule($word, $rule)
	{
		if ($rule == '.')
		{	
			return true;
		}
		else
		{
			$rule = '/^'.preg_quote($rule).'.*/ui';
			return (preg_match($rule, $word));
		}
	}
	
	
	/**
	* Checks that word matches suffix-rule
	* Return true if word matches rule
	* @param string $word
	* @param string $rule
	* @return boolean
	*/
	function CheckSuffixRule($word, $rule) {
		if ($rule == '.') 
		{	
			return true;
		}
		else 
		{
			$rule = '/'.preg_quote($rule).'$/ui';
			return (preg_match($rule, $word)) ;
		}
	}
	
	
	/**
	* Adds prefix to word
	* @param string $word
	* @param string $rule
	* @param boolean $combinerule
	* @return string 
	*/
	function AddPrefix($word, $rule, $combinerule = false)
	{
		global $conv;
		$neword = array();
		$cword = $conv->StrToLower($word);
		if (!$combinerule) 
		{
			if (!isset($this->baseWords[$cword]))
			{
				return 21;
			}
		}
		if (isset($this->prefixRules[$rule]))
		{
			$trule = $this->prefixRules[$rule];
		}
		else
		{
			return 22;
		}
		
		foreach ($trule as $rul)
		{
			if (is_array($rul))
			{
				if (is_array($word))
				{
					foreach ($word as $tw)
					{
						if ($this->CheckPrefixRule($tw, $rul[3]))
						{
							if ($rul[1] != '0')
							{
								if ($conv->StrPos($tw, $rul[1]) === 0)
								{
									$neword[] = $rul[2] . $conv->SubStr($conv->StrToLower($tw), $conv->StrLen($rul[1]));
								}
							} 
							else
							{
								$neword[] = $rul[2] . $conv->StrToLower($tw); 
							}
						}
					}
				} 
				else
				{
					if ($this->CheckPrefixRule($word, $rul[3]))
					{
						if ($rul[1] != '0')
						{
							if ($conv->StrPos($word, $rul[1]) === 0)
							{
								$neword[] = $rul[2] . $conv->SubStr($conv->StrToLower($word), $conv->StrLen($rul[1]));
							}
						}							
						else
						{
							$neword[] = $rul[2] . $word;
						}
					}
				}
			}
			
		}
		return $neword;
	}
	
	
	/**
	* Adds suffix to word
	* @param string $word
	* @param string $rule
	* @param boolean $combinerule
	* @return string 
	*/
	function AddSuffix($word, $rule, $combinerule = false)
	{
		global $conv;
		$neword = array();
		$cword = $conv->StrToLower($word);
		if (!$combinerule)
		{
			if (!isset($this->baseWords[$cword]))
			{
				return 21;
			}
		}
		if (isset($this->suffixRules[$rule]))
		{ 
			$trule = $this->suffixRules[$rule];
		}
		else
		{
			return 23;
		}
	
		foreach ($trule as $rul)
		{
			if (is_array($rul))
			{
				if (is_array($word))
				{
					foreach ($word as $tw)
					{
						if ($this->CheckSuffixRule($tw, $rul[3]))
						{
							if ($rul[1] == '0')
							{
								$neword[] = $tw . $rul[2];
							}
							else
							{
								if ($conv->strrpos($tw, $rul[1]) === ($conv->strlen($tw) - $conv->strlen($rul[1])))
								{
									$neword[] = $conv->SubStr($conv->StrToLower($tw), 0, -$conv->StrLen($rul[1])) .  $rul[2];
								}
							}
						}
					}
				}
				else
				{
					if ($this->CheckSuffixRule($word, $rul[3]))
					{
						if ($rul[1] == '0')
						{
							$neword[] = $word . $rul[2];
						}
						else
						{
							if ($conv->strrpos($word, $rul[1]) === ($conv->strlen($word) - $conv->strlen($rul[1])))
							{
								$neword[] = $conv->SubStr($conv->StrToLower($word), 0, -$conv->StrLen($rul[1])) .  $rul[2];
							}
						}
					}
				}
			}
		}
		return $neword;
	}
	
	
	/**
	* Remove prefix from word
	* @param string $word
	* @param string $rule
	* @param boolean $raw
	* @return string 
	*/
	function RemovePrefix($word, $rule, $raw = false)
	{
		if (isset($raw))
		{
			if (!$raw)
			{
				if (isset($this->prefixRules[$rule]))
				{ 
					$trule = $this->prefixRules[$rule];
				}
				else
				{
					return 22;
				}
			
				foreach ($trule as $rul)
				{
					if (is_array($rul))
					{
						$tmpWord = preg_replace('/^'.preg_quote($rul[2]).'/ui', '', $word);
						if ($rul[1] != '0')
						{
							$tmpWord = $rul[1] . $tmpWord;
						}
						if ($word !== $tmpWord)
						{
							return $tmpWord;
						}
					}
				}
			}
			else
			{
				$tmpWord = preg_replace('/^'.preg_quote($rule[2]).'/ui', '', $word);
				if ($rule[1] != '0')
				{
					$tmpWord = $rule[1].$tmpWord;
				}
				if ($word !== $tmpWord)
				{
					return $tmpWord;
				}
			}
		}
		return $word;
	}
	
	
	/**
	* Remove suffix from word
	* @param string $word
	* @param string $rule
	* @param boolean $raw
	* @return string 
	*/
	function RemoveSuffix($word, $rule, $raw = false)
	{
		if (!$raw)
		{
			if (isset($this->suffixRules[$rule]))
			{ 
				$trule = $this->suffixRules[$rule];
			}
			else
			{
				return 22;
			}
			
			foreach ($trule as $rul)
			{
				if (is_array($rul))
				{
					$tmpWord = preg_replace('/'.preg_quote($rul[2]).'$/ui', '', $word);
					if ($tmpWord != $word)
					{
						if ($rul[1] != '0')
						{
							$tmpWord = $tmpWord . $rul[1];
						}
						if ($this->CheckSuffixRule($tmpWord, $rul[3]))
						{
							return $tmpWord;
						}
					}
				}
			}
		}
		else
		{
			$tmpWord = preg_replace('/'.preg_quote($rule[2]).'$/ui', '', $word);
			if ($tmpWord != $word)
			{
				if ($rule[1] != '0')
				{
					$tmpWord = $tmpWord . $rule[1];
				}
				if ($this->CheckSuffixRule($tmpWord, $rule[3]))
				{
					return $tmpWord;
				}
			}
		}
		return $word;
	}
	
	
	/**
	 * Check that word contains in dictionary
	 * @param string $word
	 * @return boolean
	 */
	function Contains($word)
	{
		global $conv;	
		$possibleWords = Array();
		$suffixWords = Array();
		$suffixWords[] = $word;
		$allowCombine = false;
		
		// Step 1 Checks if raw word already exsists in dictionary
		//$fp = fopen("cword.txt", "a+");
		$cword = $conv->StrToLower($word);
		//fwrite($fp, "_".$cword."\n");
		if (isset($this->baseWords[$word]) || isset($this->baseWords[$cword]))
		{
			return true;
		}
		//fclose($fp);
		
		// Step 2 Remove suffix, Search BaseWords
		foreach (array_keys($this->suffixRules) As $Key)
		{
			$rule = $this->suffixRules[$Key];
			if ($rule[0] == 'Y')
			{
				$allowCombine = true;
			}
			
			foreach (array_keys($rule) As $Key2)
			{	
				$rul = $rule[$Key2];
				if (is_array($rul)) 
				{
					$tmpWord = $this->RemoveSuffix($word, $rul, true);
					if ($tmpWord != $word)
					{
						$ctmpWord = $conv->StrToLower($tmpWord);
						if (isset($this->baseWords[$tmpWord]) || isset($this->baseWords[$ctmpWord]))
						{
							if ($this->VerifyAffixKey($tmpWord, $rul[0]))
							{
								return true;
							}
						}
						if ($allowCombine)
						{
							$suffixWords[] = $tmpWord;
						}
						else
						{
							$possibleWords[] = $tmpWord;
						}
					}
				}				
			}
		}
		
		// Step 4 Remove Prefix, Search BaseWords
		foreach (array_keys($this->prefixRules) As $Keys)
		{
			$rule = $this->prefixRules[$Keys];
				
			foreach (array_keys($rule) As $Keys2)
			{
				$rul = $rule[$Keys2];
				if (is_array($rul))
				{
					foreach (array_keys($suffixWords) As $Keys3)		
					{
						$sww = $suffixWords[$Keys3];
						$tmpWord = $this->RemovePrefix($sww, $rul, true);
						if ($tmpWord != $sww)
						{
							$ctmpWord = $conv->StrToLower($tmpWord);
							if (isset($this->baseWords[$tmpWord]) || isset($this->baseWords[$ctmpWord]))
							{
								if ($this->VerifyAffixKey($tmpWord, $rul[0]))
								{	
									return true;
								}
							}
						}						
					}
				}				
			}
		}
		return false;
	}
	
	
	/**
	 * Expands an affix compressed base word
	 * @param string $word
	 * @return Array
	 */
	function ExpandWord($word)
	{
		global $conv;
		
		$adds = array();
		$words = array();
		$words[] = $word;
		if (isset($this->baseWords[$word]))
		{
				
			$key = $this->baseWords[$conv->StrToLower($word)];
			if ($key == '')
			{ 
				return false; 
			}
			else
			{
				if ($conv->StrPos($key, '/') !== false)
				{ 
					$piece = explode('/', $key);
					$affix = $piece[0];
				}
				else
				{
					$affix = $key;
				}
				
				for($i = 0; $i < $conv->StrLen($affix); $i++)
				{
					if (isset($this->suffixRules[$affix[$i]]))
					{
						$adds = array();
						$adds = $this->AddSuffix($word, $affix[$i]);
						$words = array_merge($words, $adds);
					}
					elseif (isset($this->prefixRules[$affix[$i]]))
					{
						$adds = array();
						$adds = $this->AddPrefix($word, $affix[$i]);
						$words = array_merge($words, $adds);
						if ($this->prefixRules[$affix[$i]][0] == 'Y' ) /* if allowCombine */
						{
							for($j = 0; $j < $conv->StrLen($affix); $j++)
							{
								if (isset($this->suffixRules[$affix[$j]]) && $this->suffixRules[$affix[$j]][0] == 'Y')
								{									
									$adds = array();
									$lala = $this->AddSuffix($word, $affix[$j], true);
									$adds = $this->AddPrefix($lala, $affix[$i], true);
									$words = array_merge($words, $adds);
									
								}
							}
						}
					}
				}
			}
			sort($words);
			return $words;
		}
		else
		{
			return 21;
		}
	} 
}