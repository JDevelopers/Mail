<?php

	/**
	 * Original data taken from:
	 * ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/MICSFT/WINDOWS/CP1253.TXT
	 * @param string $string
	 * @return string
	 */
	function charset_encode_windows_1253($string)
	{
		$mapping = array(
					"\xE2\x82\xAC" => "\x80",
					"\xE2\x80\x9A" => "\x82",
					"\xC6\x92" => "\x83",
					"\xE2\x80\x9E" => "\x84",
					"\xE2\x80\xA6" => "\x85",
					"\xE2\x80\xA0" => "\x86",
					"\xE2\x80\xA1" => "\x87",
					"\xE2\x80\xB0" => "\x89",
					"\xE2\x80\xB9" => "\x8B",
					"\xE2\x80\x98" => "\x91",
					"\xE2\x80\x99" => "\x92",
					"\xE2\x80\x9C" => "\x93",
					"\xE2\x80\x9D" => "\x94",
					"\xE2\x80\xA2" => "\x95",
					"\xE2\x80\x93" => "\x96",
					"\xE2\x80\x94" => "\x97",
					"\xE2\x84\xA2" => "\x99",
					"\xE2\x80\xBA" => "\x9B",
					"\xC2\xA0" => "\xA0",
					"\xCE\x85" => "\xA1",
					"\xCE\x86" => "\xA2",
					"\xC2\xA3" => "\xA3",
					"\xC2\xA4" => "\xA4",
					"\xC2\xA5" => "\xA5",
					"\xC2\xA6" => "\xA6",
					"\xC2\xA7" => "\xA7",
					"\xC2\xA8" => "\xA8",
					"\xC2\xA9" => "\xA9",
					"\xC2\xAB" => "\xAB",
					"\xC2\xAC" => "\xAC",
					"\xC2\xAD" => "\xAD",
					"\xC2\xAE" => "\xAE",
					"\xE2\x80\x95" => "\xAF",
					"\xC2\xB0" => "\xB0",
					"\xC2\xB1" => "\xB1",
					"\xC2\xB2" => "\xB2",
					"\xC2\xB3" => "\xB3",
					"\xCE\x84" => "\xB4",
					"\xC2\xB5" => "\xB5",
					"\xC2\xB6" => "\xB6",
					"\xC2\xB7" => "\xB7",
					"\xCE\x88" => "\xB8",
					"\xCE\x89" => "\xB9",
					"\xCE\x8A" => "\xBA",
					"\xC2\xBB" => "\xBB",
					"\xCE\x8C" => "\xBC",
					"\xC2\xBD" => "\xBD",
					"\xCE\x8E" => "\xBE",
					"\xCE\x8F" => "\xBF",
					"\xCE\x90" => "\xC0",
					"\xCE\x91" => "\xC1",
					"\xCE\x92" => "\xC2",
					"\xCE\x93" => "\xC3",
					"\xCE\x94" => "\xC4",
					"\xCE\x95" => "\xC5",
					"\xCE\x96" => "\xC6",
					"\xCE\x97" => "\xC7",
					"\xCE\x98" => "\xC8",
					"\xCE\x99" => "\xC9",
					"\xCE\x9A" => "\xCA",
					"\xCE\x9B" => "\xCB",
					"\xCE\x9C" => "\xCC",
					"\xCE\x9D" => "\xCD",
					"\xCE\x9E" => "\xCE",
					"\xCE\x9F" => "\xCF",
					"\xCE\xA0" => "\xD0",
					"\xCE\xA1" => "\xD1",
					"\xCE\xA3" => "\xD3",
					"\xCE\xA4" => "\xD4",
					"\xCE\xA5" => "\xD5",
					"\xCE\xA6" => "\xD6",
					"\xCE\xA7" => "\xD7",
					"\xCE\xA8" => "\xD8",
					"\xCE\xA9" => "\xD9",
					"\xCE\xAA" => "\xDA",
					"\xCE\xAB" => "\xDB",
					"\xCE\xAC" => "\xDC",
					"\xCE\xAD" => "\xDD",
					"\xCE\xAE" => "\xDE",
					"\xCE\xAF" => "\xDF",
					"\xCE\xB0" => "\xE0",
					"\xCE\xB1" => "\xE1",
					"\xCE\xB2" => "\xE2",
					"\xCE\xB3" => "\xE3",
					"\xCE\xB4" => "\xE4",
					"\xCE\xB5" => "\xE5",
					"\xCE\xB6" => "\xE6",
					"\xCE\xB7" => "\xE7",
					"\xCE\xB8" => "\xE8",
					"\xCE\xB9" => "\xE9",
					"\xCE\xBA" => "\xEA",
					"\xCE\xBB" => "\xEB",
					"\xCE\xBC" => "\xEC",
					"\xCE\xBD" => "\xED",
					"\xCE\xBE" => "\xEE",
					"\xCE\xBF" => "\xEF",
					"\xCF\x80" => "\xF0",
					"\xCF\x81" => "\xF1",
					"\xCF\x82" => "\xF2",
					"\xCF\x83" => "\xF3",
					"\xCF\x84" => "\xF4",
					"\xCF\x85" => "\xF5",
					"\xCF\x86" => "\xF6",
					"\xCF\x87" => "\xF7",
					"\xCF\x88" => "\xF8",
					"\xCF\x89" => "\xF9",
					"\xCF\x8A" => "\xFA",
					"\xCF\x8B" => "\xFB",
					"\xCF\x8C" => "\xFC",
					"\xCF\x8D" => "\xFD",
					"\xCF\x8E" => "\xFE");

		return str_replace(array_keys($mapping), array_values($mapping), $string);
	}
