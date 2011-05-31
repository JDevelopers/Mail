<?php

class FunambolSyncBase
{
	public function ConvertFNtoWMTimestamp($fnTimestamp, $inGMT = FALSE)
	{
		$fnTimestamp = "".$fnTimestamp;
		$fnTimestamp = substr($fnTimestamp, 0, strlen($fnTimestamp) - 3);
		if($inGMT)
		{
			return ((int)$fnTimestamp) - date('Z');
		}
		else
		{
			return (int)$fnTimestamp;
		}
	}
}
