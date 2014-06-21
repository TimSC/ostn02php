<?php
require_once('dbutils.php');

#OSTN02 for PHP
#==============

#This is a port of the perl module Geo::Coordinates::OSTN02 by Toby Thurston (c) 2008
#Toby kindly allowed his code to be used for any purpose.
#The python port is (c) 2010-2011 Tim Sheerman-Chase
#The OSTN02 transform is Crown Copyright (C) 2002
#See COPYING for redistribution terms

function GenerateTransformFunc()
{
	if(file_exists(dirname ( __FILE__ )."/ostn02.db"))
		return;

	require_once('dbutils.php');
	$ostn02db = new GenericSqliteTable(dirname ( __FILE__ )."/ostn02.db","ostn02");

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
		$ne = substr($line,0,6);
		//$offset = array(hexdec(substr($line,6,4)),hexdec(substr($line,10,4)),hexdec(substr($line,14,4)));
		$ostn02db[$ne] = array("data"=>substr($line,6));
	}

}

function CheckOstn02Sqlite()
{
	if(!file_exists(dirname ( __FILE__ )."/ostn02.db"))
		return 0;
	$ostn02db = new GenericSqliteTable(dirname ( __FILE__ )."/ostn02.db","ostn02");

	$keys = $ostn02db->GetKeys();

	if(count($keys) != 305179) return -1;
	return 1;
}

?>
