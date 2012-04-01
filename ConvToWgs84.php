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

$xin = Null;
$yin = Null;
$hin = 0.;
if (isset($argc) and $argc >= 3)
{
	$xin = (float)$argv[1];
	$yin = (float)$argv[2];
}
if (isset($argc) and $argc >= 4) $hin = (float)$argv[3];

if(isset($_GET['e'])) $xin = (float)$_GET['e'];
if(isset($_GET['n'])) $yin = (float)$_GET['n'];
if(isset($_GET['h'])) $hin = (float)$_GET['h'];

if(is_null($xin) or is_null($yin))
{
	echo "At least two arguments must be supplied";
	exit(0);
}

header('Content-Type: text/xml, charset=utf-8');
echo'<?xml version="1.0"?>'."\n";
echo '<xml version="1.0" creator="OSTN02 for PHP">'."\n";

print "<in><easting>".$xin."</easting>\n<northing>".$yin."</northing>\n<height>".$hin."</height></in>\n";

list($x,$y,$h) = OSGB36_to_ETRS89 ($xin, $yin, $hin);

print "<intermediate><easting>".$x."</easting>\n<northing>".$y."</northing>\n<height>".$h."</height>\n</intermediate>\n";

list($gla, $glo) = grid_to_ll($x, $y);

print "<out><latitude>".$gla."</latitude>\n<longitude>".$glo."</longitude><height>".$h."</height></out>\n";

echo "</xml>\n";

?>
