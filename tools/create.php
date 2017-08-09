#!/usr/bin/env php
<?php

require_once( __DIR__ . "/../vendor/autoload.php");
require_once( __DIR__ . "/../src/CDId.php");



use dekuan\dedid\CDId;



/**
 * Created by PhpStorm.
 * User: xing
 * Date: 09/08/2017
 * Time: 2:34 PM
 */
function create_main()
{
	$cDId		= CDId::getInstance();

	//	...
	$arrParam	= create_get_parameter();
	if ( is_array( $arrParam ) )
	{
		$arrD	= [];
		$nNewId	= $cDId->createId
		(
			$arrParam['center'],
			$arrParam['node'],
			$arrParam['source'],
			$arrD
		);

		echo PHP_EOL . "new id = " . $nNewId . PHP_EOL;
		print_r( $arrD );
	}
	else
	{
		create_usage();
	}
}

function create_get_parameter()
{
	global $argc;
	global $argv;

	if ( $argc < 3 )
	{
		return null;
	}
	if ( ! is_array( $argv ) || count( $argv ) < 3 )
	{
		return null;
	}

	$nCenter	= $argv[ 1 ];
	$nNode		= $argv[ 2 ];
	$sSource	= $argc > 3 ? $argv[ 3 ] : null;

	return
	[
		'center'	=> $nCenter,
		'node'		=> $nNode,
		'source'	=> $sSource
	];
}

function create_usage()
{
	$sUsage	= "dekuan/dedid commander:" . PHP_EOL .
		PHP_EOL .
		"Usage:" . PHP_EOL .
		"php create.php CENTER NODE [SOURCE STRING]" . PHP_EOL .
		PHP_EOL .
		PHP_EOL .
		PHP_EOL
	;
	echo $sUsage;
}



//
//	begin here
//
create_main();




