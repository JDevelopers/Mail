<?php
class BaseIcsFormatter
{

	/**
	 * rfc2445
	 * Lines of text SHOULD NOT be longer than 75 octets, excluding the line break.
	 */
	const SPLIT_LINE_LIMIT  = 75;

	/**
	 * @var CalendarContainer
	 */
	protected $_container;
	protected $_map;
	protected $_value;
	protected $_name = null;
	protected $_hasEnclosed = true;

	public function SetContainer($container)
	{
		$this->_value = null;
		$this->_container = $container;
	}

	public function GetValue()
	{
		return $this->_value;
	}

	public function Form()
	{
		$this->_value .= $this->_writeToken('BEGIN', $this->_name);
		$this->_formStatic();
		$this->_formTagsFromContainer();
		$this->_formSpecialTreatments(true);
		if ($this->_hasEnclosed)
		{
			$this->_formSpecialTreatments();
			$this->_writeToken('END', $this->_name);
		}
		else
		{
			$this->_writeToken('END', $this->_name);
			$this->_formSpecialTreatments();
		}
		return true;
	}

	protected function _formStatic()
	{
		if (array_key_exists('static', $this->_map))
		{
			$tags = $this->_map['static'];
			foreach ($tags as $token=>$value)
			{
				$this->_writeToken($token, $value);
			}
		}
	}

	protected function _formTagsFromContainer()
	{
		foreach ($this->_map['tokens'] as $token => $propName)
		{
			if ($this->_container->IsValueSet($propName))
			{
				if(array_key_exists('tokensWithSpecialTreatment', $this->_map))
				{
					if (array_key_exists($token, $this->_map['tokensWithSpecialTreatment']))
					{
						$funkName = $this->_map['tokensWithSpecialTreatment'][$token][0];
						$params = $this->_map['tokensWithSpecialTreatment'][$token];
						$params[0] = $token;
						$result = @call_user_func_array(array(&$this, $funkName), $params);
						$this->_writeLine($result);
						continue;
					}
				}
				$value = $this->_container->GetValue($propName, 'string');
				if (strlen($value)>0)
				{
					$this->_writeToken($token, $value);
				}
			}
		}
	}

	protected function _formSpecialTreatments($isInside = false)
	{
		$specialTreatments = ($isInside ? $this->_map['specialInsideTreatments'] : $this->_map['specialTreatments']);
		foreach ($specialTreatments as $propName => $treatmentClassName)
		{
			if ($this->_container->IsValueSet($propName) && class_exists($treatmentClassName))
			{
				$elements = $this->_container->GetValue($propName);
				$treatment = new $treatmentClassName;
				foreach ($elements as $container)
				{
					$treatment->SetContainer($container);
					if ($treatment->Form())
					{
						$this->_value .= $treatment->GetValue();
					}
				}
			}
		}
	}

	protected function _writeToken($token, $value, $sameLine = false)
	{
		$this->_value .= $this->_writeLine($token .':' . $value, $sameLine);
	}

	/**
	 * write line.
	 * @param string $line
	 */
	protected function _writeLine($line, $sameLine = false)
	{
		if (strlen($line) > 0)
		{
			// $text = str_replace("\n", ($sameLine ? '' : "\n "), $line); $sameLine not use in project?
			$text = $line;
			if (strlen($text) > self::SPLIT_LINE_LIMIT)
			{
				$text = $this->_utfArrayTostringWithLimitLine($this->_smartUtfStrInArray($text), self::SPLIT_LINE_LIMIT);
			}
			$this->_value .= $text."\r\n";
		}
	}

	private function _utfArrayTostringWithLimitLine($strArray, $len = 75)
	{
		$i = 0;
		$out = '';
		foreach ($strArray as $value)
		{
			if ($i > $len)
			{
				$out .= "\r\n\t".$value;
				$i = 0;
			}
			else
			{
				$out .= $value;
			}
			$i++;
		}
		return $out;
	}

	private function _smartUtfStrInArray($str)
	{
		$split = 1;
		$array = array();
		for ($i = 0; $i < strlen($str);)
		{
			$value = ord($str[$i]);
			if ($value > 127)
			{
				if ($value >= 192 && $value <= 223)
				{
					$split = 2;
				}
				else if ($value >= 224 && $value <= 239)
				{
					$split = 3;
				}
				else if ($value >= 240 && $value <= 247)
				{
					$split = 4;
				}
			}
			else
			{
				$split = 1;
			}

			$key = null;
			for ($j = 0; $j < $split; $j++, $i++)
			{
				$key .= $str[$i];
			}

			array_push($array, $key);
		}

		return $array;
	}

	protected function _escapeValue($value)
	{
		$text = str_replace('\\', '\\\\', $value);
		$text = str_replace(',', '\\,', $text);
		$text = str_replace(';', '\\;', $text);
		$text = str_replace(array("\r", "\n"), array('\r', '\n'), $text);
		return $text;
	}

	protected function dateParse($datetime)
	{
		if (function_exists('date_parse'))
		{
			return date_parse($datetime);
		}
		
		$return = false;
		$dt = explode(' ', $datetime, 2);
		if (count($dt) == 2)
		{
			$date = explode('-', trim($dt[0]), 3);
			$time = explode(':', trim($dt[1]), 3);

			if (count($date) == 3 && count($time) == 3)
			{
				$return = array(
					'year' => $date[0],
					'day' => $date[2],
					'month' => $date[1],

					'hour' => $time[0],
					'minute' => $time[1],
					'second' => $time[2]
				);
			}
		}
		return $return;
	}

	public function InitParameters($map)
    {
        $this->_map = $map;
    }
}
