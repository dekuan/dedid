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
function parse_main()
{
	$cDId		= CDId::getInstance();

	//	...
	$arrParam	= parse_get_parameter();
	if ( is_array( $arrParam ) )
	{
		$arrD	= $cDId->parseId
		(
			$arrParam['id']
		);

		echo PHP_EOL . "parse id " . $arrParam['id'] . PHP_EOL;
		print_r( $arrD );
	}
	else
	{
		parse_usage();
	}
}

function parse_get_parameter()
{
	global $argc;
	global $argv;

	if ( $argc < 2 )
	{
		return null;
	}
	if ( ! is_array( $argv ) || count( $argv ) < 2 )
	{
		return null;
	}

	$nId	= $argv[ 1 ];

	return
	[
		'id'	=> $nId
	];
}

function parse_usage()
{
	$sUsage	= "dekuan/dedid commander:" . PHP_EOL .
		PHP_EOL .
		"Usage:" . PHP_EOL .
		"php parse.php did" . PHP_EOL .
		PHP_EOL .
		PHP_EOL .
		PHP_EOL
	;
	echo $sUsage;
}



//
//	begin here
//
parse_main();




