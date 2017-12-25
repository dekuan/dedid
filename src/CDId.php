<?php

namespace dekuan\dedid;

//
//	STRUCTURE
//	================================================================================
//
//
//	0 xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx x xxxxx xxxxx xxxx xxxxxxxx
//	- ---------------------------------------------- ----- ----- -----------
//	  Escaped Time (in millisecond)                  Center Node Random
//	1 41 bits                                        5     5     12 bits
//	  0~69 (years)                                   0~32  0~32  0~4095
//	
//	
//	Center
//	0 00000000 00000000 00000000 00000000 00000000 0 11111 00000 0000 00000000
//	00000000 00000000 00000000 00000000 00000000 00111110 00000000 00000000
//	00       00       00       00       00       3E       00       00
//	
//	Node
//	0 00000000 00000000 00000000 00000000 00000000 0 00000 11111 0000 00000000
//	00000000 00000000 00000000 00000000 00000000 00000001 11110000 00000000
//	00       00       00       00       00       01       F0       00
//	
//	Escaped Time
//	0 11111111 11111111 11111111 11111111 11111111 1 00000 00000 0000 00000000
//	01111111 11111111 11111111 11111111 11111111 11000000 00000000 00000000
//	7F       FF       FF       FF       FF       C0       00       00
//
//	Random
//	0 00000000 00000000 00000000 00000000 00000000 0 00000 00000 1111 11111111
//	00000000 00000000 00000000 00000000 00000000 00000000 00001111 11111111
//	00       00       00       00       00       00       0F       FF
//
//
//
//	php -r 'echo dechex( 0b00111111 );'
//


/**
 *	An unique id generator for primary key of distributed database
 *	class CDId 
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
	 *	@param $nCenter int	data center id ( 0 ~ 31 )
	 *	@param $nNode int	data node id ( 0 ~ 31 )
	 *	@param $sSource string	source string for calculating crc32 hash value
	 *	@param $arrData &array	details about the id
	 *	@return int(64)	id
	 */
	public function createId( $nCenter, $nNode, $sSource = null, & $arrData = null )
	{
		if ( ! $this->isValidCenterId( $nCenter ) )
		{
			return null;
		}
		if ( ! $this->isValidNodeId( $nNode ) )
		{
			return null;
		}

		//	...
		$nRet		= 0;
		$nTime		= $this->getEscapedTime();
		$nCenter	= intval( $nCenter );
		$nNode		= intval( $nNode );

		if ( is_string( $sSource ) && strlen( $sSource ) > 0 )
		{
			//	use crc32 hash value instead of rand
			$nRand	= crc32( $sSource );
		}
		else
		{
			//	0 ~ 4095
			$nRand	= rand( 0, 0xFFF );
		}

		//	...
		$nCenterV	= ( ( $nCenter << 17 ) & 0x00000000003E0000 );
		$nNodeV		= ( ( $nNode   << 12 ) & 0x000000000001F000 );
		$nTimeV		= ( ( $nTime   << 22 ) & 0x7FFFFFFFFFC00000 );
		$nRandV		= ( ( $nRand   << 0  ) & 0x0000000000000FFF );

		$nRet		= ( $nCenterV + $nNodeV + $nTimeV + $nRandV );

		//	...
		if ( ! is_null( $arrData ) )
		{
			$arrData =
			[
				'center'	=> $nCenter,
				'node'		=> $nNode,
				'time'		=> $nTime,
				'rand'		=> $nRandV,
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
		if ( ! is_numeric( $nId ) || $nId <= 0 )
		{
			return null;
		}

		//	...
		$nId		= intval( $nId );
		$nCenter	= ( ( $nId & 0x00000000003E0000 ) >> 17 );
		$nNode		= ( ( $nId & 0x000000000001F000 ) >> 12 );
		$nTime		= ( ( $nId & 0x7FFFFFFFFFC00000 ) >> 22 );
		$nRand		= ( ( $nId & 0x0000000000000FFF ) >> 0  );

		return
		[
			'center'	=> $nCenter,
			'node'		=> $nNode,
			'time'		=> $nTime,
			'rand'		=> $nRand,
		];
	}

	/**
	 *	Verify whether the id is valid
	 *
	 *	@param $nVal int	64 bits unique id
	 *	@return boolean		true or false
	 */
	public function isValidId( $nVal )
	{
		$bRet	= false;
		$arrD	= $this->parseId( $nVal );
		if ( is_array( $arrD ) &&
			array_key_exists( 'center', $arrD ) &&
			array_key_exists( 'node', $arrD ) &&
			array_key_exists( 'time', $arrD ) &&
			array_key_exists( 'rand', $arrD ) )
		{
			if ( $this->isValidCenterId( $arrD[ 'center' ] ) &&
				$this->isValidNodeId( $arrD[ 'node' ] ) &&
				$this->isValidTime( $arrD[ 'time' ] ) &&
				$this->isValidRand( $arrD[ 'rand' ] ) )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}

	/**
	 *	@param $nVal int	64 bits unique id
	 *	@return boolean		true or false
	 */
	public function isValidCenterId( $nVal )
	{
		return is_numeric( $nVal ) && ( $nVal >= 0 ) && ( $nVal <= 31 );
	}

	/**
	 *	@param $nVal int	64 bits unique id
	 *	@return boolean		true or false
	 */
	public function isValidNodeId( $nVal )
	{
		return is_numeric( $nVal ) && ( $nVal >= 0 ) && ( $nVal <= 31 );
	}

	/**
	 *	@param $nVal int	64 bits unique id
	 *	@return boolean		true or false
	 */
	public function isValidTime( $nVal )
	{
		return is_numeric( $nVal ) && ( $nVal >= 0 );
	}

	/**
	 *	@param $nVal int	64 bits unique id
	 *	@return boolean		true or false
	 */
	public function isValidRand( $nVal )
	{
		return is_numeric( $nVal ) && ( $nVal >= 0 ) && ( $nVal <= 0xFFF );
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