<?php
namespace up;

use up;
use up\cache\exception;

class cache
{
	private static $init = false;
	private static $engine;

	const TTL_MINUTE  = 60;
	const TTL_5_MIN   = 300;
	const TTL_10_MIN  = 600;
	const TTL_HOUR    = 3600;
	const TTL_DAY     = 86400;
	const TTL_WEEK    = 604800;
	const TTL_MONTH   = 2592000;
	const TTL_FOREVER = 0;

	const EVENT_NAMESPACE = __CLASS__;
	const EVENT_INIT      = 'init';


	/**
	 * @param null $engine
	 * @return  \Up\Cache\Interfaces
	 * @throws Exception
	 */
	public static function engine( $engine = null )
	{
		if ( $engine === null ) {
			self::init();

			if ( !self::$engine )
				throw new exception( 'Not set cache engine!' );

			return self::$engine;
		}

		self::$engine = $engine;
	}

	public static function get( $key, $flag = null )
	{
		return self::engine()->get( $key, $flag );
	}

	public static function set( $key, $value, $expire = self::TTL_FOREVER, $flag = null )
	{
		return self::engine()->set( $key, $value, $expire, $flag );
	}

	public static function add( $key, $value, $expire = self::TTL_FOREVER, $flag = null )
	{
		return self::engine()->add( $key, $value, $expire, $flag );
	}

	public static function del( $key, $timeout = 0 )
	{
		return self::engine()->del( $key, $timeout );
	}

	public static function inc( $key, $value = 1 )
	{
		return self::engine()->inc( $key, $value );
	}

	public static function dec( $key, $value = 1 )
	{
		return self::engine()->dec( $key, $value );
	}

	public static function replace( $key, $value, $expire = self::TTL_FOREVER, $flag = null )
	{
		return self::engine()->replace( $key, $value, $expire, $flag );
	}

	public static function prefix( $prefix = null )
	{
		return self::engine()->prefix( $prefix );
	}

	private static function init()
	{
		if ( self::$init === true ) return ;

		self::$init = true;

		up::notify( self::EVENT_NAMESPACE, self::EVENT_INIT );
	}
}