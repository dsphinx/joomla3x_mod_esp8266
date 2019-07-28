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
#  Created : 4/7/19
#
*/


defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHTML::_('behavior.tooltip');
?>

	<div class='mod-temperature '>
	<h3>༗ <?php echo JText::_('LABEL TITLE'); ?> </h3> <!--  (θερμοκρασίας και υγρασίας) -->
<?php
//	var_dump($vars_Config);

$symbol=$vars_Config['config_mode'] ? "℃" : "F";

if ( empty($vars_to_view) )
{
	printf('<div class="rowInfo"> ' . JText::_('SENSOR DATA NOT') . '  !</div>');

}
else
{
	foreach ( $vars_to_view as $k=>$v ):

		$v->temperature=$vars_Config['config_mode'] ? $v->temperature : ( ( $v->temperature * 1.8 ) + 32 );  // (C * 1.8) + 32 = F.
		$v->name       =$vars_Config['config_show_description'] ? $v->name . " " . $v->description : $v->name;
		$showTime      =$vars_Config['config_show_time'] ? $v->dateEntry . "<br/>" : NULL;

		printf('<div class="rowInfo text-right"><small>%s</small></sma><span class="hasTip"  title="' . JText::_('SENSOR NAME').' - %s:: % s"> %s &#x24D8;  </span> : &#x1f321; <b> %2.2f </b> %s &nbsp; ❄ <b> %2.2f </b> &#x25;  </div>', $showTime, $v->description, $v->dateEntry, $v->name, $v->temperature, $symbol, $v->humidity);
		endforeach;
	}
	?>
</div>