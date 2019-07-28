<?php
/*
#  Copyright (c) 2019, dsphinx
#  All rights reserved.
#
#  Redistribution and use in source and binary forms, with or without
#  modification, are permitted provided that the following conditions are met:
#   1. Redistributions of source code must retain the above copyright
#      notice, this list of conditions and the following disclaimer.
#   2. Redistributions in binary form must reproduce the above copyright
#      notice, this list of conditions and the following disclaimer in the
#  ....
#
#  Author:  dsphinx@plug.gr
#  Filename:   api.php
#  Created : 24/7/19
#
#  URI  /modules/mod_temperature/api/api.php?
#  Testing:
 		[ pi pi ~/codePython ]  python getTempJoomlaCave.py
*/
 
define('_JEXEC', 1);
define('JPATH_BASE', '../../../');

require_once JPATH_BASE . 'includes/defines.php';
require_once JPATH_BASE . 'includes/framework.php';


$app = JFactory::getApplication('site');
$jinput = JFactory::getApplication()->input;

$collectedData=array('Temperature'=>$jinput->get('Temperature', NULL, 'FLOAT'),
                     'Humidity'   =>$jinput->get('Humidity', NULL, 'FLOAT'),
                     'ClientIP'   =>$_SERVER['REMOTE_ADDR'],
                     'Date'       =>$jinput->get('Date', NULL, 'STRINGs'),
                     'Description'=>$jinput->get('Description', NULL, 'STRING'),
                     'Name'=>$jinput->get('sensorName', NULL, 'STRING'),
                     'Version'    =>$jinput->get('Version', NULL, 'FLOAT')


);

// var_dump($collectedData);

$db = JFactory::getDbo();
$query = $db->getQuery(true);

$columns = array('temperature', 'humidity', 'description', 'clientIP','name');

// extra con
if ($collectedData['Temperature'] <100 && $collectedData['Temperature']> -60 && $collectedData['Humidity'] >=0 && $collectedData['Humidity'] <=100) {


$values = array($collectedData['Temperature'],$collectedData['Humidity'], $db->quote($collectedData['Description']),   $db->quote($collectedData['ClientIP']), $db->quote($collectedData['Name']) );

$query
    ->insert($db->quoteName('#__sensorsESP8266'))
    ->columns($db->quoteName($columns))
    ->values(implode(',', $values));

$db->setQuery($query);
$db->execute();

$ret = 1;
} else {
	$ret = 0;
}

echo $ret;

?>
