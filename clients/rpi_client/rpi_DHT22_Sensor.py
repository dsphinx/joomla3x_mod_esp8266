#!/usr/bin/python
# -*- coding: utf-8 -*-

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
#  Filename:   getCoord
#  Created : 4/12/19
#

import sys
import Adafruit_DHT
import requests

# api-endpoint
URL = "http://SERVER_IP_OR_DNS/index.php?option=com_ajax&module=temperature&format=raw"
sensor_args = { '11': Adafruit_DHT.DHT11,
                '22': Adafruit_DHT.DHT22,
                '2302': Adafruit_DHT.AM2302 }

sensor = 11
pin = 4

humidity, temperature = Adafruit_DHT.read_retry( sensor, pin )

# defining a params dict for the parameters to be sent to the API
PARAMS = { 'Temperature': temperature, 'Humidity': humidity, 'Description': "Office", "version": "2",
           "sensorName": "Office" }

# sending get request and saving the response as response object
if humidity is not None and temperature is not None:
	r = requests.get( url=URL, params=PARAMS )
	data = r.text

else:
    print('Failed to get reading. Try again!')
    sys.exit(1)
