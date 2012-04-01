<?php

require_once('ostn02config.php');

#OSTN02 for PHP
#==============

#This is a port of the perl module Geo::Coordinates::OSTN02 by Toby Thurston (c) 2008
#Toby kindly allowed his code to be used for any purpose.
#The python port is (c) 2010-2011 Tim Sheerman-Chase
#The OSTN02 transform is Crown Copyright (C) 2002
#See COPYING for redistribution terms

define('MAX_EASTING',700000);
define('MAX_NORTHING',1250000);

define('MIN_X_SHIFT',86.275);
define('MIN_Y_SHIFT',-81.603);
define('MIN_Z_SHIFT',43.982);

if(CREATE_SQLITE_OSTN02_ON_FIRST_RUN and !file_exists(dirname ( __FILE__ )."/ostn02.db"))
{
	require_once('GenerateTransformFunc.php');
	echo "Generating OSTN02 database on first run...\n";
	GenerateTransformFunc();
	echo "done\n";
}

if(USE_SQLITE_OSTN02_IF_AVAIL and file_exists(dirname ( __FILE__ )."/ostn02.db"))
{
	require_once('dbutils.php');
	$ostn02db = new GenericSqliteTable(dirname ( __FILE__ )."/ostn02.db","ostn02");
}
else $ostn02db = Null;

function ostn()
{
	$fi = bzopen(dirname ( __FILE__ )."/ostn02data.txt.bz2",'r');
	$fidata = "";
	while(!feof($fi))
	{
		$buff = bzread($fi);
		if($fidata!==FALSE) $fidata .= $buff;
	}($fidata !== FALSE);

	$lines = explode("\n",$fidata);
	#print_r ($lines);
	$out = array();

	foreach ($lines as $line)
	{
		$line = rtrim($line,'\r\n');
		//print $line."\n";
		$ne = substr($line,0,6);
		$offset = array(hexdec(substr($line,6,4)),hexdec(substr($line,10,4)),hexdec(substr($line,14,4)));
		$out[$ne] = $offset;
	}

	return $out;
}

if(LOAD_OSTN02_INTO_MEM)
	$ostn_data = ostn(); # load all the data from below
else 
	$ostn_data = Null;

$ostn_shift_for= array();

function ETRS89_to_OSGB36($x,$y,$z=0.0)
{
    if ( 0 <= $x and $x <= MAX_EASTING and 0 <= $y and $y <= MAX_NORTHING )
    {
        list($dx, $dy, $dz) = _find_OSTN02_shifts_at($x,$y);
        list($x, $y, $z) = _round_to_nearest_mm($x+$dx, $y+$dy, $z-$dz); # note $z sign differs
    }
    else
        throw new Exception('OSTN02 is not defined at '.$x.', '.$y.')');

    return array($x, $y, $z);
}

function OSGB36_to_ETRS89 ($x0, $y0, $z0 = 0.0)
{
    $epsilon = 0.00001;
    list($dx, $dy, $dz) = _find_OSTN02_shifts_at($x0,$y0);
    list($x,  $y,  $z ) = array($x0-$dx, $y0-$dy, $z0+$dz);
    list($last_dx, $last_dy) = array($dx, $dy);
    #APPROX:
    while (1)
    {
        list($dx, $dy, $dz) = _find_OSTN02_shifts_at($x,$y);
        list($x, $y) = array($x0-$dx, $y0-$dy);
        if (abs($dx-$last_dx)<$epsilon and abs($dy-$last_dy)<$epsilon) #last APPROX 
		break;
        list($last_dx, $last_dy) = array($dx, $dy);
    }
    list($x, $y, $z) = _round_to_nearest_mm($x0-$dx, $y0-$dy, $z0+$dz);

    return array($x, $y, $z);
}


function _round_to_nearest_mm($x,  $y,  $z)
{
    $x = (int)($x*1000+0.5)/1000;
    $y = (int)($y*1000+0.5)/1000;
    $z = (int)($z*1000+0.5)/1000;
    return array($x, $y, $z);
}

function _find_OSTN02_shifts_at($x,$y)
{
    $e_index = (int)($x/1000);
    $n_index = (int)($y/1000);

    $s0_ref = _get_ostn_ref($e_index+0, $n_index+0);
    $s1_ref = _get_ostn_ref($e_index+1, $n_index+0);
    $s2_ref = _get_ostn_ref($e_index+0, $n_index+1);
    $s3_ref = _get_ostn_ref($e_index+1, $n_index+1);

    if ($s0_ref === Null or $s1_ref === Null or $s2_ref === Null or $s3_ref === Null)
	throw new Exception("[OSTN02 not defined at (".$x.",".$y.")]");

    $x0 = $e_index * 1000;
    $y0 = $n_index * 1000;

    $dx = $x - $x0; # offset within square
    $dy = $y - $y0;

    $t = $dx/1000;
    $u = $dy/1000;

    $f0 = (1-$t)*(1-$u);
    $f1 =    $t *(1-$u);
    $f2 = (1-$t)*   $u;
    $f3 =    $t *   $u;

 

    $se = $f0*$s0_ref[0] + $f1*$s1_ref[0] + $f2*$s2_ref[0] + $f3*$s3_ref[0];
    $sn = $f0*$s0_ref[1] + $f1*$s1_ref[1] + $f2*$s2_ref[1] + $f3*$s3_ref[1];
    $sg = $f0*$s0_ref[2] + $f1*$s1_ref[2] + $f2*$s2_ref[2] + $f3*$s3_ref[2];

    #if se*sn*sg==0.:
    #    print("[OSTN02 defined as zeros at ($x, $y), coordinates unchanged]")

    return array($se, $sn, $sg);
}


function _get_ostn_ref($x,$y)
{
    global $ostn_data, $ostn_shift_for;

    //echo $x.",".$y."\n";
    $key = sprintf("%03x%03x",$y, $x);
    //print_r($key);echo"\n";
    if (isset($ostn_shift_for[$key]))
	return $ostn_shift_for[$key];

    if ($ostn_data !== Null and isset($ostn_data[$key]))
    {
	$data = $ostn_data[$key];
	$data2 = array($data[0]/1000.0 + MIN_X_SHIFT,$data[1]/1000.0 +MIN_Y_SHIFT,$data[2]/1000.0 + MIN_Z_SHIFT);
	if(USE_OSTN02_TABLE_CACHE)
		$ostn_shift_for[$key] = $data2;
	if(count($ostn_shift_for)>MAX_OSTN02_CACHE)
		$ostn_shift_for = array();

        return $data2;
    }

    global $ostn02db;
    if ($ostn02db !== Null and isset($ostn02db[$key]))
    {
	$line = $ostn02db[$key]['data'];
	//print_r($ostn02db[$key]);
	$data = array(hexdec(substr($line,0,4)),hexdec(substr($line,4,4)),hexdec(substr($line,8,4)));
	$data2 = array($data[0]/1000.0 + MIN_X_SHIFT,$data[1]/1000.0 +MIN_Y_SHIFT,$data[2]/1000.0 + MIN_Z_SHIFT);
	if(USE_OSTN02_TABLE_CACHE)
		$ostn_shift_for[$key] = $data2;
	if(count($ostn_shift_for)>MAX_OSTN02_CACHE)
		$ostn_shift_for = array();

	return $data2;
    }

	//Use bzip2 stream to find the offset
	//This is very slow
	$fi = bzopen(dirname ( __FILE__ )."/ostn02data.txt.bz2",'r');
	do{
		$data = bzread($fi,20);
		//echo rtrim($data)."\n";
		if($data===FALSE) continue;
		//$x = $data;
		//echo rtrim($data).",".$key."\n";
		$ne = substr($data,0,6);
		if($ne == $key)
		{
			$offset = array(hexdec(substr($data,6,4)),hexdec(substr($data,10,4)),hexdec(substr($data,14,4)));
			$data2 = array($offset[0]/1000.0 + MIN_X_SHIFT,$offset[1]/1000.0 +MIN_Y_SHIFT,$offset[2]/1000.0 + MIN_Z_SHIFT);
			if(USE_OSTN02_TABLE_CACHE)
				$ostn_shift_for[$key] = $data2;
			if(count($ostn_shift_for)>MAX_OSTN02_CACHE)
				$ostn_shift_for = array();
			return $data2;
		}

	} while (!feof($fi));

    return Null;
}

?>
