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
#  Filename:   helper.php
#  Created : 24/7/19
#
*/

defined('_JEXEC') or die;

/**
 * Class ModTemperatureHelper
 *
 *   Γενικές λειτουργίες τουν ενθέματος
 */
class ModTemperatureHelper
{
	static $mod_Ver=1;
	static $limitRecs=4;
	static $tblWorking='#__sensorsESP8266';


	//  Λειτουργίες EndPOINT

	/**
	 *    Εκτέλεση remote call as API Endpoint
	 *    χρήση option=com_ajax
	 *
	 *    κλήση : index.php?option=com_ajax&module=temperature&format=raw
	 *
	 *    Περιγραφή :
	 *          εισαγωγή δεδομένων στη βάση μέσω esp8266
	 *
	 * @return bool
	 */
	public static function getAjax ()
	{
		$app   =JFactory::getApplication('site');
		$jinput=JFactory::getApplication()->input;
		$ret   ="OK";

		$collectedData=array('Temperature'=>$jinput->get('Temperature', NULL, 'FLOAT'),
		                     'Humidity'   =>$jinput->get('Humidity', NULL, 'FLOAT'),
		                     'ClientIP'   =>$_SERVER['REMOTE_ADDR'],
		                     'Date'       =>$jinput->get('Date', NULL, 'STRINGs'),
		                     'Description'=>$jinput->get('Description', NULL, 'STRING'),
		                     'Name'       =>$jinput->get('sensorName', NULL, 'STRING'),
		                     'Version'    =>$jinput->get('Version', NULL, 'FLOAT')
		);


		$db   =JFactory::getDbo();
		$query=$db->getQuery(TRUE);

		$columns=array('temperature',
		               'humidity',
		               'description',
		               'clientIP',
		               'name'
		);

		if ( $collectedData['Temperature'] != "NaN" && $collectedData['Temperature'] < 100 && $collectedData['Temperature'] > -60 && $collectedData['Humidity'] > -1 && $collectedData['Humidity'] <= 100 )
		{
			$values=array($collectedData['Temperature'],
			              $collectedData['Humidity'],
			              $db->quote($collectedData['Description']),
			              $db->quote($collectedData['ClientIP']),
			              $db->quote($collectedData['Name'])
			);

			$query->insert($db->quoteName(self::$tblWorking))->columns($db->quoteName($columns))->values(implode(',', $values));

			$db->setQuery($query);
			$db->execute();

		}
		else
		{
			$ret="NOP";
		}

		return $ret;
	}


	/**
	 *
	 *  γενικές πληροφορίες
	 *
	 *   κλήση :   index.php?option=com_ajax&module=temperature&format=raw&method=info
	 *
	 *
	 * @return string
	 */
	public static function infoAjax ()
	{

		$ret="Joomla 3.x Module: mod_sensors ESP8266 , Version " . self::$mod_Ver . " (c) dsphinx@plug.gr <br/> ";
		$ret.=" <br/> Σύνολο εγγραφών : " . self::getDBCount();
		$ret.=" <br/> Σύνολο ενεργών : " . self::getDBCount("deleted = 0");
		$ret.=" <br/> Σύνολο διεγραμένων : " . self::getDBCount("deleted = 1");

		foreach ( self::getSensors() as $k )
		{
			$ret.="<br/> <br/> Αισθητήρας [ " . $k . " ] εγγραφές " . self::getDBCount("name = '$k'");
			$ret.="<br/> Θερμοκρασία min : " . self::getMinMax($k);
			$ret.="  max : " . self::getMinMax($k, "Temperature", "MAX");
			$ret.=" -- Υγρασία min : " . self::getMinMax($k, "Humidity");
			$ret.="  max : " . self::getMinMax($k, "Humidity", "MAX");
		}

		return $ret;
	}


	//EOB  ----------


	/**
	 * @param $params
	 *
	 *    Ανάκληση των τελευταίων εγγραφών δεδομέων
	 *
	 * @return mixed
	 */
	public static function getTemperature ($params)
	{
		$db   =JFactory::getDbo();
		$query=$db->getQuery(TRUE)->select('id, temperature, humidity, description,name , dateEntry')->from(self::$tblWorking)->where('deleted=0')->order('id DESC')->setLimit(self::$limitRecs);;
		$db->setQuery($query);
		$results=$db->loadObjectList();

		return $results;
	}


	/**
	 * @param      $params
	 * @param null $configField
	 *
	 *   Ανάγνωση ρυθμίσεων απο ρυθμίσεις Χρήστη
	 *   στη βασική δομή
	 *    απο αρχείο xml
	 *
	 */
	public static function getConfiguration ($params, $configField=NULL)
	{

		$myConfigurationFromXML=array('config_show_description',
		                              'config_mode',
		                              'config_show_time',
		                              'layout',
		                              'cache',
		                              'cache_time'
		);

		if ( $configField )
		{
			$ret=$params->get($configField);
		}
		else
		{
			$ret=array();
			foreach ( $myConfigurationFromXML as $cfg )
			{
				$ret[$cfg]=$params->get($cfg);
			}
		}

		return $ret;
	}


	/**
	 * @param null $whereCondition
	 *
	 *    Επιστροφή συνόλου εγγραφών με κριτήρια
	 *
	 * @return mixed
	 */
	public static function getDBCount ($whereCondition=NULL)
	{

		$db   =JFactory::getDbo();
		$query=$db->getQuery(TRUE);
		$query->select('COUNT(*)');
		$query->from($db->quoteName(self::$tblWorking));
		if ( $whereCondition )
		{
			$query->where($whereCondition);
		}

		$db->setQuery($query);
		$results=$db->loadResult();

		return $results;
	}


	/**
	 * @return mixed
	 *
	 *              get all sensors name
	 */
	public static function getSensors ()
	{

		$db   =JFactory::getDbo();
		$query=$db->getQuery(TRUE)->select('DISTINCT name')->from(self::$tblWorking);
		$db->setQuery($query);

		$options=$db->loadColumn();
		// Check for a database error.
		if ( $db->getErrorNum() )
		{
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		return $options;
	}


	/**
	 * @param string $sensor
	 * @param string $field
	 * @param string $what
	 *
	 *    Ανάκληση MIN και MAX τιμών στα δεδομένα του εκάστοτε αισθητήρα
	 *
	 * @return mixed
	 */
	public static function getMinMax ($sensor="", $field="Temperature", $what="MIN")
	{

		$db   =JFactory::getDbo();
		$query=$db->getQuery(TRUE);
		$query->select("$what(" . $db->quoteName($field) . ')');
		$query->from($db->quoteName(self::$tblWorking));
		//	$query->where($db->quoteName("name = '$sensor'"));

		$db->setQuery($query);
		$results=$db->loadResult();

		return sprintf("%2.2f", floatval($results));
	}
}

?>