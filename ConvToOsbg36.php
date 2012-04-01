<?php
#OSTN02 for PHP
#==============

#This is a port of the perl module Geo::Coordinates::OSTN02 by Toby Thurston 
#Toby kindly allowed his code to be used for any purpose.
#The python port is (c) 2010 Tim Sheerman-Chase
#The OSTN02 transform is Crown Copyright (C) 2002
#See COPYING for redistribution terms

require_once('OSGB.php');
require_once('OSTN02.php');

$gla = Null;
$glo = Null;
$hin = 0.0;

if (isset($argc) and $argc >= 3)
{
	$gla = (float)$argv[1];
	$glo = (float)$argv[2];
}
if (isset($argc) and $argc >= 4) $hin = (float)$argv[2];

if(isset($_GET['lat'])) $gla = (float)$_GET['lat'];
if(isset($_GET['lon'])) $glo = (float)$_GET['lon'];
if(isset($_GET['h'])) $hin = (float)$_GET['h'];

if(is_null($gla) or is_null($glo))
{
	echo "At least two arguments must be supplied";
	exit(0);
}

header('Content-Type: text/xml, charset=utf-8');
echo'<?xml version="1.0"?>'."\n";
echo '<xml version="1.0" creator="OSTN02 for PHP">'."\n";

print "<in><latitude>".$gla."</latitude>\n<longitude>".$glo."</longitude>\n<height>".$hin."</height></in>\n";

list($x2,$y2) = ll_to_grid($gla, $glo);

print "<intermediate><easting>".$x2."</easting>\n<northing>".$y2."</northing>\n</intermediate>\n";

$grid = ETRS89_to_OSGB36($x2,$y2,$hin); 

print "<out><easting>".$grid[0]."</easting>\n<northing>".$grid[1]."</northing>\n<height>".$grid[2]."</height></out>\n";

echo "</xml>\n";

?>
