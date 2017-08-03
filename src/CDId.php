<?php

namespace dekuan\dedid;

//
//	STRUCTURE
//	================================================================================
//
//
//	0 xxxxxx xxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xx xxxxxxxx
//	- ------ ------- -------------------------------------------- -----------
//	  Host   Table   Escaped Time (in millisecond)                Random
//	1 6      7       40 bits                                      10 bits
//	  0~63   0~127   0~34 (years)                                 0~1023
//	
//	
//	host
//	01111110 00000000 00000000 00000000 00000000 00000000 00000000 00000000
//	7E       00       00       00       00       00       00       00
//	
//	table
//	00000001 11111100 00000000 00000000 00000000 00000000 00000000 00000000
//	01       FC       00       00       00       00       00       00
//	
//	escaped time
//	00000000 00000011 11111111 11111111 11111111 11111111 11111100 00000000
//	00       03       FF       FF       FF       FF       FC       00
//	
//	random
//	00000000 00000000 00000000 00000000 00000000 00000000 00000011 11111111
//	00       00       00       00       00       00       03       FF
//


/**
 *     CDId 
 */
class CDId
{
	protected static $g_cInstanceDId;

	/**
	 *	Offset from Unix Epoch
	 *
	 *	Unix Epoch :	January 1, 1970 00:00:00 GMT
	 *	Epoch Offset :	November 7, 2016 00:00:00 GMT
	 */
	CONST EPOCH_OFFSET = 1478476800000;



	

	public function __construct()
	{
	}
	public function __destruct()
	{
	}
	static function getInstance()
	{
		if ( is_null( self::$g_cInstanceDId ) || ! isset( self::$g_cInstanceDId ) )
		{
			self::$g_cInstanceDId = new self();
		}
		return self::$g_cInstanceDId;
	}


	/**
	 *	Generate an unique id
	 *
	 *	@param $nHost int	host id ( 0 ~ 63 )
	 *	@param $nTable int	table id ( 0 ~ 127 )
	 *	@param $arrData &array	details about the id
	 *	@return int(64)	id
	 */
	public function createId( $nHost, $nTable, & $arrData = null )
	{
		if ( ! $this->isValidHostId( $nHost ) )
		{
			return null;
		}
		if ( ! $this->isValidTableId( $nTable ) )
		{
			return null;
		}

		//	...
		$nRet	= 0;
		$nTime	= $this->getEscapedTime();
		$nRand	= rand( 0, 0x3FF );

		$nHostV	= ( ( $nHost  << 57 ) & 0x7E00000000000000 );
		$nTabV	= ( ( $nTable << 50 ) & 0x01FC000000000000 );
		$nTimeV	= ( ( $nTime  << 10 ) & 0x0003FFFFFFFFFC00 );
		$nRandV	= ( ( $nRand  << 0  ) & 0x00000000000003FF );

		$nRet	= ( $nHostV + $nTabV + $nTimeV + $nRandV );

		//	...
		if ( ! is_null( $arrData ) )
		{
			$arrData =
			[
				'host'	=> $nHost,
				'table'	=> $nTable,
				'time'	=> $nTime,
				'rand'	=> $nRand,
			];
		}

		return intval( $nRet );
	}

	/**
	 *	Parse an unique id
	 *
	 *	@param $nId int		64 bits unique id
	 *	@return array		details about the id
	 */
	public function parseId( $nId )
	{
		if ( ! is_numeric( $nId ) )
		{
			return null;
		}

		$nHost	= ( ( $nId & 0x7E00000000000000 ) >> 57 );
		$nTable	= ( ( $nId & 0x01FC000000000000 ) >> 50 );
		$nTime	= ( ( $nId & 0x0003FFFFFFFFFC00 ) >> 10 );
		$nRand	= ( ( $nId & 0x00000000000003FF ) >> 0  );

		return
		[
			'host'	=> $nHost,
			'table'	=> $nTable,
			'time'	=> $nTime,
			'rand'	=> $nRand,
		];
	}

	public function isValidHostId( $nVal )
	{
		return is_numeric( $nVal ) && ( $nVal >= 0 ) && ( $nVal <= 63 );
	}
	public function isValidTableId( $nVal )
	{
		return is_numeric( $nVal ) && ( $nVal >= 0 ) && ( $nVal <= 127 );
	}


	/**
	 *	Get UNIX timestamp in millisecond
	 *
	 *	@return int	Timestamp in millisecond, for example: 1501780592275
	 */
	public function getUnixTimestamp()
	{
		return floor( microtime( true ) * 1000 );
	}

	/**
	 *	Get escaped time in millisecond
	 *
	 *	@return int	time in millisecond
	 */
	public function getEscapedTime()
	{
		return intval( $this->getUnixTimestamp() - self::EPOCH_OFFSET );
	}
}