/**
 *	An unique id generator for primary key of distributed database
 *	class CDId
 *
 * 	Created by Liu QiXing on August 10, 2017. 
 */
function CDId()
{
	var m_oThis	= this;

	this.EPOCH_OFFSET	= 1478476800000;


	/**
	 *	*** DOES NOT WORKS IN 32 BITS JavaScript ***
	 *
	 * 
	 *	Parse an unique id
	 *
	 *	@param nId int		64 bits unique id
	 *	@return array		details about the id
	 */
	this.parseId = function( nId )
	{
		var nCenter;
		var nNode;
		var nTime;
		var nRand;

		if ( 'number' !== typeof nId )
		{
			return null;
		}

		//	...
		nCenter	= ( ( nId & 0x00000000003E0000 ) >> 17 );
		nNode	= ( ( nId & 0x000000000001F000 ) >> 12 );
		nTime	= ( ( nId & 0x7FFFFFFFFFC00000 ) >> 22 );
		nRand	= ( ( nId & 0x0000000000000FFF ) >> 0  );

		return {
			'center'	: nCenter,
			'node'		: nNode,
			'time'		: nTime,
			'rand'		: nRand
		};
	};


	/**
	 *	*** DOES NOT WORKS IN 32 BITS JavaScript ***
	 *
	 *  
	 *	Verify whether the id is valid
	 *
	 *	@param nVal int		64 bits unique id
	 *	@return boolean		true or false
	 */
	this.isValidId = function( nVal )
	{
		var bRet;
		var arrD;

		if ( ! _isNumeric( nVal ) )
		{
			return false;
		}

		bRet	= false;
		arrD	= m_oThis.parseId( nVal );
		if ( 'object' == typeof( arrD ) &&
			arrD.hasOwnProperty( 'center' ) &&
			arrD.hasOwnProperty( 'node' ) &&
			arrD.hasOwnProperty( 'time' ) &&
			arrD.hasOwnProperty( 'rand' ) )
		{
			if ( m_oThis.isValidCenterId( arrD[ 'center' ] ) &&
				m_oThis.isValidNodeId( arrD[ 'node' ] ) &&
				m_oThis.isValidTime( arrD[ 'time' ] ) &&
				m_oThis.isValidRand( arrD[ 'rand' ] ) )
			{
				bRet = true;
			}
		}

		return bRet;
	};

	this.isValidCenterId = function( nVal )
	{
		return _isNumeric( nVal ) && ( nVal >= 0 ) && ( nVal <= 31 );
	};

	this.isValidNodeId = function( nVal )
	{
		return _isNumeric( nVal ) && ( nVal >= 0 ) && ( nVal <= 31 );
	};

	this.isValidTime = function( nVal )
	{
		return _isNumeric( nVal ) && ( nVal >= 0 );
	};

	this.isValidRand = function( nVal )
	{
		return _isNumeric( nVal ) && ( nVal >= 0 ) && ( nVal <= 0xFFF );
	};


	////////////////////////////////////////////////////////////////////////////////
	//	Private
	//
	
	function _isNumeric( vVal )
	{
		return ( 'number' === ( typeof vVal ) );
	}
}