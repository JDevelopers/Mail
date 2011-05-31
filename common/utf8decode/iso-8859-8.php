<?php

	/**
	 * Original data taken from:
	 * ftp://ftp.unicode.org/Public/MAPPINGS/ISO8859/8859-8.TXT
	 * @param string $string
	 * @return string
	 */
	function charset_decode_iso_8859_8($string)
	{
		$mapping = array(
					"\x80" => "\xC2\x80",
					"\x81" => "\xC2\x81",
					"\x82" => "\xC2\x82",
					"\x83" => "\xC2\x83",
					"\x84" => "\xC2\x84",
					"\x85" => "\xC2\x85",
					"\x86" => "\xC2\x86",
					"\x87" => "\xC2\x87",
					"\x88" => "\xC2\x88",
					"\x89" => "\xC2\x89",
					"\x8A" => "\xC2\x8A",
					"\x8B" => "\xC2\x8B",
					"\x8C" => "\xC2\x8C",
					"\x8D" => "\xC2\x8D",
					"\x8E" => "\xC2\x8E",
					"\x8F" => "\xC2\x8F",
					"\x90" => "\xC2\x90",
					"\x91" => "\xC2\x91",
					"\x92" => "\xC2\x92",
					"\x93" => "\xC2\x93",
					"\x94" => "\xC2\x94",
					"\x95" => "\xC2\x95",
					"\x96" => "\xC2\x96",
					"\x97" => "\xC2\x97",
					"\x98" => "\xC2\x98",
					"\x99" => "\xC2\x99",
					"\x9A" => "\xC2\x9A",
					"\x9B" => "\xC2\x9B",
					"\x9C" => "\xC2\x9C",
					"\x9D" => "\xC2\x9D",
					"\x9E" => "\xC2\x9E",
					"\x9F" => "\xC2\x9F",
					"\xA0" => "\xC2\xA0",
					"\xA2" => "\xC2\xA2",
					"\xA3" => "\xC2\xA3",
					"\xA4" => "\xC2\xA4",
					"\xA5" => "\xC2\xA5",
					"\xA6" => "\xC2\xA6",
					"\xA7" => "\xC2\xA7",
					"\xA8" => "\xC2\xA8",
					"\xA9" => "\xC2\xA9",
					"\xAA" => "\xC3\x97",
					"\xAB" => "\xC2\xAB",
					"\xAC" => "\xC2\xAC",
					"\xAD" => "\xC2\xAD",
					"\xAE" => "\xC2\xAE",
					"\xAF" => "\xC2\xAF",
					"\xB0" => "\xC2\xB0",
					"\xB1" => "\xC2\xB1",
					"\xB2" => "\xC2\xB2",
					"\xB3" => "\xC2\xB3",
					"\xB4" => "\xC2\xB4",
					"\xB5" => "\xC2\xB5",
					"\xB6" => "\xC2\xB6",
					"\xB7" => "\xC2\xB7",
					"\xB8" => "\xC2\xB8",
					"\xB9" => "\xC2\xB9",
					"\xBA" => "\xC3\xB7",
					"\xBB" => "\xC2\xBB",
					"\xBC" => "\xC2\xBC",
					"\xBD" => "\xC2\xBD",
					"\xBE" => "\xC2\xBE",
					"\xDF" => "\xE2\x80\x97",
					"\xE0" => "\xD7\x90",
					"\xE1" => "\xD7\x91",
					"\xE2" => "\xD7\x92",
					"\xE3" => "\xD7\x93",
					"\xE4" => "\xD7\x94",
					"\xE5" => "\xD7\x95",
					"\xE6" => "\xD7\x96",
					"\xE7" => "\xD7\x97",
					"\xE8" => "\xD7\x98",
					"\xE9" => "\xD7\x99",
					"\xEA" => "\xD7\x9A",
					"\xEB" => "\xD7\x9B",
					"\xEC" => "\xD7\x9C",
					"\xED" => "\xD7\x9D",
					"\xEE" => "\xD7\x9E",
					"\xEF" => "\xD7\x9F",
					"\xF0" => "\xD7\xA0",
					"\xF1" => "\xD7\xA1",
					"\xF2" => "\xD7\xA2",
					"\xF3" => "\xD7\xA3",
					"\xF4" => "\xD7\xA4",
					"\xF5" => "\xD7\xA5",
					"\xF6" => "\xD7\xA6",
					"\xF7" => "\xD7\xA7",
					"\xF8" => "\xD7\xA8",
					"\xF9" => "\xD7\xA9",
					"\xFA" => "\xD7\xAA",
					"\xFD" => "\xE2\x80\x8E",
					"\xFE" => "\xE2\x80\x8F");

		$outStr = '';
    	for ($i = 0, $len = strlen($string); $i < $len; $i++)
    	{
    		$outStr .= (array_key_exists($string{$i}, $mapping))?$mapping[$string{$i}]:$string{$i};
		}
		
		return $outStr;
	}

