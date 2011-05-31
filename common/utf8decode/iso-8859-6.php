<?php

	/**
	 * Original data taken from:
	 * ftp://ftp.unicode.org/Public/MAPPINGS/ISO8859/8859-6.TXT
	 * @param string $string
	 * @return string
	 */
	function charset_decode_iso_8859_6($string)
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
					"\xA4" => "\xC2\xA4",
					"\xAC" => "\xD8\x8C",
					"\xAD" => "\xC2\xAD",
					"\xBB" => "\xD8\x9B",
					"\xBF" => "\xD8\x9F",
					"\xC1" => "\xD8\xA1",
					"\xC2" => "\xD8\xA2",
					"\xC3" => "\xD8\xA3",
					"\xC4" => "\xD8\xA4",
					"\xC5" => "\xD8\xA5",
					"\xC6" => "\xD8\xA6",
					"\xC7" => "\xD8\xA7",
					"\xC8" => "\xD8\xA8",
					"\xC9" => "\xD8\xA9",
					"\xCA" => "\xD8\xAA",
					"\xCB" => "\xD8\xAB",
					"\xCC" => "\xD8\xAC",
					"\xCD" => "\xD8\xAD",
					"\xCE" => "\xD8\xAE",
					"\xCF" => "\xD8\xAF",
					"\xD0" => "\xD8\xB0",
					"\xD1" => "\xD8\xB1",
					"\xD2" => "\xD8\xB2",
					"\xD3" => "\xD8\xB3",
					"\xD4" => "\xD8\xB4",
					"\xD5" => "\xD8\xB5",
					"\xD6" => "\xD8\xB6",
					"\xD7" => "\xD8\xB7",
					"\xD8" => "\xD8\xB8",
					"\xD9" => "\xD8\xB9",
					"\xDA" => "\xD8\xBA",
					"\xE0" => "\xD9\x80",
					"\xE1" => "\xD9\x81",
					"\xE2" => "\xD9\x82",
					"\xE3" => "\xD9\x83",
					"\xE4" => "\xD9\x84",
					"\xE5" => "\xD9\x85",
					"\xE6" => "\xD9\x86",
					"\xE7" => "\xD9\x87",
					"\xE8" => "\xD9\x88",
					"\xE9" => "\xD9\x89",
					"\xEA" => "\xD9\x8A",
					"\xEB" => "\xD9\x8B",
					"\xEC" => "\xD9\x8C",
					"\xED" => "\xD9\x8D",
					"\xEE" => "\xD9\x8E",
					"\xEF" => "\xD9\x8F",
					"\xF0" => "\xD9\x90",
					"\xF1" => "\xD9\x91",
					"\xF2" => "\xD9\x92");

		$outStr = '';
    	for ($i = 0, $len = strlen($string); $i < $len; $i++)
    	{
    		$outStr .= (array_key_exists($string{$i}, $mapping))?$mapping[$string{$i}]:$string{$i};
		}
		
		return $outStr;
	}

