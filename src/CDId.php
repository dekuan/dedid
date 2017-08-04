<?php

namespace dekuan\dedid;

//
//	STRUCTURE
//	================================================================================
//
//
//	0 xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx x xxxxxx xxxxxx xx xxxxxxxx
//	- ---------------------------------------------- ------ ------ -----------
//	  Escaped Time (in millisecond)                  Center Node   Random
//	1 41 bits                                        6      6      10 bits
//	  0~69 (years)                                   0~63   0~63   0~1023
//	
//	
//	Center
//	0 00000000 00000000 00000000 00000000 00000000 0 111111 000000 00 00000000
//	00000000 00000000 00000000 00000000 00000000 00111111 00000000 00000000
//	00       00       00       00       00       3F       00       00
//	
//	Node
//	0 00000000 00000000 00000000 00000000 00000000 0 000000 111111 00 00000000
//	00000000 00000000 00000000 00000000 00000000 00000000 11111100 00000000
//	00       00       00       00       00       00       FC       00
//	
//	Escaped Time
//	0 11111111 11111111 11111111 11111111 11111111 1 000000 000000 00 00000000
//	01111111 11111111 11111111 11111111 11111111 11000000 00000000 00000000
//	7F       FF       FF       FF       FF       C0       00       00
//
//	Random
//	0 00000000 00000000 00000000 00000000 00000000 0 000000 000000 11 11111111
//	00000000 00000000 00000000 00000000 00000000 00000000 00000011 11111111
//	00       00       00       00       00       00       03       FF
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
	 *	@param $nCenter int	data center id ( 0 ~ 63 )
	 *	@param $nNode int	data node id ( 0 ~ 63 )
	 *	@param $arrData &array	details about the id
	 *	@return int(64)	id
	 */
	public function createId( $nCenter, $nNode, & $arrData = null )
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
		$nRet	= 0;
		$nTime	= $this->getEscapedTime();
		$nRand	= rand( 0, 0x3FF );

		$nCenterV	= ( ( $nCenter << 16 ) & 0x00000000003F0000 );
		$nNodeV		= ( ( $nNode   << 10 ) & 0x000000000000FC00 );
		$nTimeV		= ( ( $nTime   << 22 ) & 0x7FFFFFFFFFC00000 );
		$nRandV		= ( ( $nRand   << 0  ) & 0x00000000000003FF );

		$nRet		= ( $nCenterV + $nNodeV + $nTimeV + $nRandV );

		//	...
		if ( ! is_null( $arrData ) )
		{
			$arrData =
			[
				'center'	=> $nCenter,
				'node'		=> $nNode,
				'time'		=> $nTime,
				'rand'		=> $nRand,
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

		//	...
		$nCenter	= ( ( $nId & 0x00000000003F0000 ) >> 16 );
		$nNode		= ( ( $nId & 0x000000000000FC00 ) >> 10 );
		$nTime		= ( ( $nId & 0x7FFFFFFFFFC00000 ) >> 22 );
		$nRand		= ( ( $nId & 0x00000000000003FF ) >> 0  );

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
	
	
	public function isValidCenterId( $nVal )
	{
		return is_numeric( $nVal ) && ( $nVal >= 0 ) && ( $nVal <= 63 );
	}
	public function isValidNodeId( $nVal )
	{
		return is_numeric( $nVal ) && ( $nVal >= 0 ) && ( $nVal <= 63 );
	}
	public function isValidTime( $nVal )
	{
		return is_numeric( $nVal ) && ( $nVal >= 0 );
	}
	public function isValidRand( $nVal )
	{
		return is_numeric( $nVal ) && ( $nVal >= 0 ) && ( $nVal <= 0x3FF );
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