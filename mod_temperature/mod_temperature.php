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
#  Filename:  mod_temperature.php
#  Created : 24/7/19
#
*/

 defined('_JEXEC') or die;
require_once dirname(__FILE__) . '/helper.php';

$document = JFactory::getDocument();
$rel_path = 'modules/mod_temperature';
$document->addStyleSheet($rel_path . '/main.css');
$document->addScript($rel_path. '/main.js');

$vars_to_view = ModTemperatureHelper::getTemperature($params);
$vars_Config = ModTemperatureHelper::getConfiguration($params);     // array με ρυθμίσεις απο xml

require JModuleHelper::getLayoutPath('mod_temperature',$params->get('layout', 'default'));