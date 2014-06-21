OSTN02 for PHP
==============

OSTN02 for PHP is a software library for doing the OSTN02 conversion. This is useful for accurately converting UK map references to WGS84/ETRS89 latitudes and longitudes. It is a partial port of the Perl library Geo-Coordinates-OSGB.

OSTN02 uses a large look up table. This can be handled in three ways (see ostn02config.php for details):

* The table file is parsed every time it is accessed. This is the default, and slow.
* The table can be copied into an SQLite database (~21Mb hard drive space). Once generated, it is moderately quick.
* The table can be loaded into memory on initialization. This takes more memory than PHP is usually allowed on apache.

This software is released under the Simplified BSD license, see COPYING file for details.

Web interface and command line
------------------------------

The script can be called from the command line and a web interface:

* http://timsc.dev.openstreetmap.org/dev/ostn02/ostn02php/ConvToOsbg36.php?lat=51.29831006&lon=1.07337394&h=44.621
* http://timsc.dev.openstreetmap.org/dev/ostn02/ostn02php/ConvToWgs84.php?e=614350&n=159950&h=0

or

* php ConvToOsbg36.php 51.29831006 1.07337394 44.621
* php ConvToWgs84.php 614350 159950 0

The result is XML formatted. You can check the accuracy on the OS website. http://gps.ordnancesurvey.co.uk/etrs89geo_natgrid.asp
