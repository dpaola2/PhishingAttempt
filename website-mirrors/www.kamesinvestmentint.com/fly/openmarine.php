<?php

$filename = RL_UPLOAD . $feed['Feed'].".xml";

copy($this -> xml_file, $filename);

$xml_content = file_get_contents($filename);
//$xml_content = preg_replace('#<item name="([^"]*)" unit="([^"]*)">([^<]*)</item>#', '<item_$1>$3</item_$1><item_$1_unit>$2</item_$1_unit>', $xml_content);

$xml_content = preg_replace('#<item name="([^"]*)" unit="([^"]*)">([^<]*)</item>#', '<item_$1>$3</item_$1><item_$1_unit>$2</item_$1_unit>', $xml_content);
$xml_content = preg_replace('#<item name="([^"]*)" unit="([^"]*)" />#', '<item_$1 />', $xml_content);
$xml_content = preg_replace('#<item name="([^"]*)">([^<]*)</item>#', '<item_$1>$2</item_$1>', $xml_content);
$xml_content = preg_replace('#<item name="([^"]*)" />#', '<item_$1 />', $xml_content);

file_put_contents($filename, $xml_content);

$this -> xml_file = $filename;