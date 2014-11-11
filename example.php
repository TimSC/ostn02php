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

print "OSGB36_to_ETRS89\n";
print "================\n";
print "Take OS map reference: TR143599\n";
$xin = 614300;
$yin = 159900;
print "OS X (Eastings) ".$xin."\n";
print "OS Y (Northings) ".$yin."\n";

list($x,$y,$h) = OSGB36_to_ETRS89 ($xin, $yin);

print "Using the OSGB36_to_ETRS89 conversion gives us the grid position:\n";
print $x.",".$y.",".$h."\n";

list($gla, $glo) = grid_to_ll($x, $y);

print "The grid position converts to ETRS89 lat,lon (using grid_to_ll) of:\n";
print $gla.",".$glo."\n";

print "Actual answer: 51.297880, 1.072628\n";
print "ETRS89 is within a metre of WGS84 (as used by GPS receivers), at time of writing (2011).\n";

print "\nETRS89_to_OSGB36\n";
print "=================\n";

$gla = 51.297880;
$glo = 1.072628;
$h = 44.621;

print "To ETRS89 grid (using ll_to_grid):\n";
list($x2,$y2) = ll_to_grid($gla, $glo);
print $x2.",".$y2."\n";

//$x2 = 614249.519; $y2 = 160029.836; $h = 44.621;

print "To OS Eastings/Northings (using ETRS89_to_OSGB36):\n";
$grid = ETRS89_to_OSGB36($x2,$y2,$h); 
echo $grid[0].",".$grid[1].",".$grid[2]."\n";

print "Actual Answer: 614300, 159900, 0\n";

print "\nExceptions\n";
print "==========\n";
print "Some areas do not have OSTN02 coverage for example OS grid 622129,185038\n";
print "This causes an exception to be raised, but it can be caught:\n";
try
{
	list($x,$y,$h) = OSGB36_to_ETRS89 (622129,185038);
}
catch (Exception $e)
{
	print 'Exception occurred, value:'. $e->getMessage()."\n";
}
print "All done!\n";

?>
