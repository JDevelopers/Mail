<?php

	/**
	 * Original data taken from:
	 * ftp://ftp.unicode.org/Public/MAPPINGS/ISO8859/8859-4.TXT
	 * @param string $string
	 * @return string
	 */
	function charset_encode_iso_8859_4($string)
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
					"\xC4\x84" => "\xA1",
					"\xC4\xB8" => "\xA2",
					"\xC5\x96" => "\xA3",
					"\xC2\xA4" => "\xA4",
					"\xC4\xA8" => "\xA5",
					"\xC4\xBB" => "\xA6",
					"\xC2\xA7" => "\xA7",
					"\xC2\xA8" => "\xA8",
					"\xC5\xA0" => "\xA9",
					"\xC4\x92" => "\xAA",
					"\xC4\xA2" => "\xAB",
					"\xC5\xA6" => "\xAC",
					"\xC2\xAD" => "\xAD",
					"\xC5\xBD" => "\xAE",
					"\xC2\xAF" => "\xAF",
					"\xC2\xB0" => "\xB0",
					"\xC4\x85" => "\xB1",
					"\xCB\x9B" => "\xB2",
					"\xC5\x97" => "\xB3",
					"\xC2\xB4" => "\xB4",
					"\xC4\xA9" => "\xB5",
					"\xC4\xBC" => "\xB6",
					"\xCB\x87" => "\xB7",
					"\xC2\xB8" => "\xB8",
					"\xC5\xA1" => "\xB9",
					"\xC4\x93" => "\xBA",
					"\xC4\xA3" => "\xBB",
					"\xC5\xA7" => "\xBC",
					"\xC5\x8A" => "\xBD",
					"\xC5\xBE" => "\xBE",
					"\xC5\x8B" => "\xBF",
					"\xC4\x80" => "\xC0",
					"\xC3\x81" => "\xC1",
					"\xC3\x82" => "\xC2",
					"\xC3\x83" => "\xC3",
					"\xC3\x84" => "\xC4",
					"\xC3\x85" => "\xC5",
					"\xC3\x86" => "\xC6",
					"\xC4\xAE" => "\xC7",
					"\xC4\x8C" => "\xC8",
					"\xC3\x89" => "\xC9",
					"\xC4\x98" => "\xCA",
					"\xC3\x8B" => "\xCB",
					"\xC4\x96" => "\xCC",
					"\xC3\x8D" => "\xCD",
					"\xC3\x8E" => "\xCE",
					"\xC4\xAA" => "\xCF",
					"\xC4\x90" => "\xD0",
					"\xC5\x85" => "\xD1",
					"\xC5\x8C" => "\xD2",
					"\xC4\xB6" => "\xD3",
					"\xC3\x94" => "\xD4",
					"\xC3\x95" => "\xD5",
					"\xC3\x96" => "\xD6",
					"\xC3\x97" => "\xD7",
					"\xC3\x98" => "\xD8",
					"\xC5\xB2" => "\xD9",
					"\xC3\x9A" => "\xDA",
					"\xC3\x9B" => "\xDB",
					"\xC3\x9C" => "\xDC",
					"\xC5\xA8" => "\xDD",
					"\xC5\xAA" => "\xDE",
					"\xC3\x9F" => "\xDF",
					"\xC4\x81" => "\xE0",
					"\xC3\xA1" => "\xE1",
					"\xC3\xA2" => "\xE2",
					"\xC3\xA3" => "\xE3",
					"\xC3\xA4" => "\xE4",
					"\xC3\xA5" => "\xE5",
					"\xC3\xA6" => "\xE6",
					"\xC4\xAF" => "\xE7",
					"\xC4\x8D" => "\xE8",
					"\xC3\xA9" => "\xE9",
					"\xC4\x99" => "\xEA",
					"\xC3\xAB" => "\xEB",
					"\xC4\x97" => "\xEC",
					"\xC3\xAD" => "\xED",
					"\xC3\xAE" => "\xEE",
					"\xC4\xAB" => "\xEF",
					"\xC4\x91" => "\xF0",
					"\xC5\x86" => "\xF1",
					"\xC5\x8D" => "\xF2",
					"\xC4\xB7" => "\xF3",
					"\xC3\xB4" => "\xF4",
					"\xC3\xB5" => "\xF5",
					"\xC3\xB6" => "\xF6",
					"\xC3\xB7" => "\xF7",
					"\xC3\xB8" => "\xF8",
					"\xC5\xB3" => "\xF9",
					"\xC3\xBA" => "\xFA",
					"\xC3\xBB" => "\xFB",
					"\xC3\xBC" => "\xFC",
					"\xC5\xA9" => "\xFD",
					"\xC5\xAB" => "\xFE",
					"\xCB\x99" => "\xFF");

		return str_replace(array_keys($mapping), array_values($mapping), $string);
	}

