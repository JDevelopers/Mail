<?php

	/**
	 * Original data taken from:
	 * ftp://ftp.unicode.org/Public/MAPPINGS/ISO8859/8859-5.TXT
	 * @param string $string
	 * @return string
	 */
	function charset_encode_iso_8859_5($string)
	{
		$mapping = array(
					"\xC2\x80" => "\x80",
					"\xC2\x81" => "\x81",
					"\xC2\x82" => "\x82",
					"\xC2\x83" => "\x83",
					"\xC2\x84" => "\x84",
					"\xC2\x85" => "\x85",
					"\xC2\x86" => "\x86",
					"\xC2\x87" => "\x87",
					"\xC2\x88" => "\x88",
					"\xC2\x89" => "\x89",
					"\xC2\x8A" => "\x8A",
					"\xC2\x8B" => "\x8B",
					"\xC2\x8C" => "\x8C",
					"\xC2\x8D" => "\x8D",
					"\xC2\x8E" => "\x8E",
					"\xC2\x8F" => "\x8F",
					"\xC2\x90" => "\x90",
					"\xC2\x91" => "\x91",
					"\xC2\x92" => "\x92",
					"\xC2\x93" => "\x93",
					"\xC2\x94" => "\x94",
					"\xC2\x95" => "\x95",
					"\xC2\x96" => "\x96",
					"\xC2\x97" => "\x97",
					"\xC2\x98" => "\x98",
					"\xC2\x99" => "\x99",
					"\xC2\x9A" => "\x9A",
					"\xC2\x9B" => "\x9B",
					"\xC2\x9C" => "\x9C",
					"\xC2\x9D" => "\x9D",
					"\xC2\x9E" => "\x9E",
					"\xC2\x9F" => "\x9F",
					"\xC2\xA0" => "\xA0",
					"\xD0\x81" => "\xA1",
					"\xD0\x82" => "\xA2",
					"\xD0\x83" => "\xA3",
					"\xD0\x84" => "\xA4",
					"\xD0\x85" => "\xA5",
					"\xD0\x86" => "\xA6",
					"\xD0\x87" => "\xA7",
					"\xD0\x88" => "\xA8",
					"\xD0\x89" => "\xA9",
					"\xD0\x8A" => "\xAA",
					"\xD0\x8B" => "\xAB",
					"\xD0\x8C" => "\xAC",
					"\xC2\xAD" => "\xAD",
					"\xD0\x8E" => "\xAE",
					"\xD0\x8F" => "\xAF",
					"\xD0\x90" => "\xB0",
					"\xD0\x91" => "\xB1",
					"\xD0\x92" => "\xB2",
					"\xD0\x93" => "\xB3",
					"\xD0\x94" => "\xB4",
					"\xD0\x95" => "\xB5",
					"\xD0\x96" => "\xB6",
					"\xD0\x97" => "\xB7",
					"\xD0\x98" => "\xB8",
					"\xD0\x99" => "\xB9",
					"\xD0\x9A" => "\xBA",
					"\xD0\x9B" => "\xBB",
					"\xD0\x9C" => "\xBC",
					"\xD0\x9D" => "\xBD",
					"\xD0\x9E" => "\xBE",
					"\xD0\x9F" => "\xBF",
					"\xD0\xA0" => "\xC0",
					"\xD0\xA1" => "\xC1",
					"\xD0\xA2" => "\xC2",
					"\xD0\xA3" => "\xC3",
					"\xD0\xA4" => "\xC4",
					"\xD0\xA5" => "\xC5",
					"\xD0\xA6" => "\xC6",
					"\xD0\xA7" => "\xC7",
					"\xD0\xA8" => "\xC8",
					"\xD0\xA9" => "\xC9",
					"\xD0\xAA" => "\xCA",
					"\xD0\xAB" => "\xCB",
					"\xD0\xAC" => "\xCC",
					"\xD0\xAD" => "\xCD",
					"\xD0\xAE" => "\xCE",
					"\xD0\xAF" => "\xCF",
					"\xD0\xB0" => "\xD0",
					"\xD0\xB1" => "\xD1",
					"\xD0\xB2" => "\xD2",
					"\xD0\xB3" => "\xD3",
					"\xD0\xB4" => "\xD4",
					"\xD0\xB5" => "\xD5",
					"\xD0\xB6" => "\xD6",
					"\xD0\xB7" => "\xD7",
					"\xD0\xB8" => "\xD8",
					"\xD0\xB9" => "\xD9",
					"\xD0\xBA" => "\xDA",
					"\xD0\xBB" => "\xDB",
					"\xD0\xBC" => "\xDC",
					"\xD0\xBD" => "\xDD",
					"\xD0\xBE" => "\xDE",
					"\xD0\xBF" => "\xDF",
					"\xD1\x80" => "\xE0",
					"\xD1\x81" => "\xE1",
					"\xD1\x82" => "\xE2",
					"\xD1\x83" => "\xE3",
					"\xD1\x84" => "\xE4",
					"\xD1\x85" => "\xE5",
					"\xD1\x86" => "\xE6",
					"\xD1\x87" => "\xE7",
					"\xD1\x88" => "\xE8",
					"\xD1\x89" => "\xE9",
					"\xD1\x8A" => "\xEA",
					"\xD1\x8B" => "\xEB",
					"\xD1\x8C" => "\xEC",
					"\xD1\x8D" => "\xED",
					"\xD1\x8E" => "\xEE",
					"\xD1\x8F" => "\xEF",
					"\xE2\x84\x96" => "\xF0",
					"\xD1\x91" => "\xF1",
					"\xD1\x92" => "\xF2",
					"\xD1\x93" => "\xF3",
					"\xD1\x94" => "\xF4",
					"\xD1\x95" => "\xF5",
					"\xD1\x96" => "\xF6",
					"\xD1\x97" => "\xF7",
					"\xD1\x98" => "\xF8",
					"\xD1\x99" => "\xF9",
					"\xD1\x9A" => "\xFA",
					"\xD1\x9B" => "\xFB",
					"\xD1\x9C" => "\xFC",
					"\xC2\xA7" => "\xFD",
					"\xD1\x9E" => "\xFE",
					"\xD1\x9F" => "\xFF");

		return str_replace(array_keys($mapping), array_values($mapping), $string);
	}

