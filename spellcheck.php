<?php

/*
 * AfterLogic WebMail Pro PHP by AfterLogic Corp. <support@afterlogic.com>
 *
 * Copyright (C) 2002-2010  AfterLogic Corp. (www.afterlogic.com)
 * Distributed under the terms of the license described in COPYING
 * 
 */

defined('WM_ROOTPATH') || define('WM_ROOTPATH', (dirname(__FILE__).'/'));

require_once(WM_ROOTPATH.'common/inc_top.php');

header('Content-type: text/xml');

function disable_magic_quotes_gpc()
{
	if (@get_magic_quotes_gpc() == 1)
	{
		$_GET = array_map('stripslashes' , $_GET);
		$_POST = array_map('stripslashes' , $_POST);
	}
}

@disable_magic_quotes_gpc();

define('MEMORY_LIMIT', '20M');
define('SOCKET_TIMEOUT', 600);
define('MAX_SUGGEST_WORDS', 10);

require_once(WM_ROOTPATH.'common/spellchecker/class_spellchecker.php');
require_once(WM_ROOTPATH.'common/class_xmldocument.php');
require_once(WM_ROOTPATH.'common/inc_constants.php');
require_once(WM_ROOTPATH.'common/class_log.php');
require_once(WM_ROOTPATH.'common/class_settings.php');

$log =& CLog::CreateInstance();

if ($log->Enabled)
{
	@ob_start('obLogResponse');
}
else 
{
	@ob_start();
}
	
@ini_set('memory_limit', MEMORY_LIMIT);
@set_time_limit(SOCKET_TIMEOUT);

require WM_ROOTPATH.'common/class_session.php';

$spell_lang = '';
$spell_dictionary = '';

$spell_lang = isset($_SESSION[SESSION_LANG]) ? $_SESSION[SESSION_LANG] : 'English';

switch ($spell_lang) 
{
	default:
		$spell_dictionary = 'en-US.dic';
		break;
	case 'French':
		$spell_dictionary = 'fr-FR.dic';
		break;
	case 'Russian':
		$spell_dictionary = 'ru-RU.dic';
		break;
	case 'German':
		$spell_dictionary = 'de-DE.dic';
		break;
	case 'Portuguese-Brazil':
		// $spell_dictionary = 'pt-PT.dic';
		$spell_dictionary = 'pt-BR.dic';
		break;
}

$sp = new Spellchecker(WM_ROOTPATH.'common/spellchecker/dictionary/'.$spell_dictionary);
if ($sp->_error === '' || $sp->_error === null) 
{
	$xmlStr = isset($_POST['xml']) ? $_POST['xml'] : '<?xml version="1.0" encoding="utf-8"?><webmail><param name="action" value="spellcheck"/><param name="request" value="spell"/><text><![CDATA[]]></text></webmail>';
	$log->WriteLine("[spellchecker] <<\r\n".$xmlStr);
	
	$cxml = new  XmlDocument();
	$cxml->ParseFromString($xmlStr);
	$response = new XmlDocument();
	$response->CreateElement('webmail');
	if ($cxml->GetParamValueByName('action') == 'spellcheck')
	{
		$req = $cxml->GetParamValueByName('request');
		switch ($req)
		{
			case 'spell':
				$text = str_replace('&#93;&#93;&gt;', ']]>', $cxml->XmlRoot->GetChildValueByTagName('text'));
				$sp->text = $text;
				$misspel = $sp->ParseText();
				$node = new XmlDomNode('spellcheck');
				foreach ($misspel as $misspelNode)
				{
					if (is_array($misspelNode) && count($misspelNode) > 1)
					{
						$misp = new XmlDomNode('misp', '');
						$misp->AppendAttribute('pos',  $misspelNode[0]);
						$misp->AppendAttribute('len',  $misspelNode[1]);
						$node->AppendChild($misp);
						unset($misp);
					}
				}
				
				$node->AppendAttribute('action', 'spellcheck');
				$response->XmlRoot->AppendChild($node);
				break;
			case 'suggest':
				$suggest = array();
				$suggestTmp = array();
				$wordNode = $cxml->XmlRoot->GetChildNodeByTagName('word');
				$word = '';
				if ($wordNode)
				{
					$word = str_replace('&amp;', '&', str_replace('&lt;', '<', str_replace('&gt;', '>', $wordNode->Value)));
				}

				if (strlen(trim($word)) == 0)
				{
					$node = new XmlDomNode('spellcheck');
					$node->AppendAttribute('action', 'suggest');
					$response->XmlRoot->AppendChild($node);
					break;
				}
				
				$sp->currentWord = $word;
				
				$sp->ReplaceChars($suggestTmp);
				$suggest = array_unique(array_merge($suggest, $suggestTmp));
				if (count($suggest) < MAX_SUGGEST_WORDS - 1)
				{
					$sp->SwapChar($suggestTmp);
					$suggest = array_unique(array_merge($suggest, $suggestTmp));
					if (count($suggest) < MAX_SUGGEST_WORDS - 1)
					{
						$sp->BadChar($suggestTmp);
						$suggest = array_unique(array_merge($suggest, $suggestTmp));
						if (count($suggest) < MAX_SUGGEST_WORDS - 1)
						{
							$sp->ForgotChar($suggestTmp);
							$suggest = array_unique(array_merge($suggest, $suggestTmp));
							if (count($suggest) < MAX_SUGGEST_WORDS - 1)
							{
								$sp->ExtraChar($suggestTmp);
								$suggest = array_unique(array_merge($suggest, $suggestTmp));
								if (count($suggest) < MAX_SUGGEST_WORDS - 1)
								{
									$sp->TwoWords($suggestTmp);
									$suggest = array_unique(array_merge($suggest, $suggestTmp));
								}
							}
						}
					}
				}
				$node = new XmlDomNode('spellcheck');
				foreach ($suggest as $suggestWord) 
				{
					$node->AppendChild(new XmlDomNode('word', $suggestWord, true));
				}
				
				$node->AppendAttribute('action', 'suggest');
				$response->XmlRoot->AppendChild($node);
				break;
		} 
	}
	
	print $response->ToString();
} 
else
{
	/* if error ocured */
	$response = new XmlDocument();
	$response->CreateElement('webmail');
	$node = new XmlDomNode('spellcheck');
	$node->AppendAttribute('action', 'error');
	$node->AppendAttribute('errorstr',  $sp->_error);
	$response->XmlRoot->AppendChild($node);
	print $response->ToString();
}
	
	/**
	 * @param string $string
	 * @return string
	 */
	function obLogResponse($string)
	{
		global $log;
		$log->WriteLine("[spellchecker] >>\r\n".$string);
		return $string;
	}
