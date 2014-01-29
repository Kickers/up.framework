<?php
namespace up;

/**
 * Autoload classes
 * 
 * @package    autoload
 * @relationUp exception, /events
 * @author     Kirill Kirhov
 * @copyright  Up framework (http://up.singleup.net). singleUp team (www.singleup.net)
 * @license    http://up.singleup.org/license/ (GNU)
 *
 */

class autoload
{
	const EVENT_NAMESPACE        = __CLASS__;
	
	const EVENT_BEFORE_AUTOLOAD  = 'beforeAutoload';
	const EVENT_AFTER_AUTOLOAD   = 'afterAutoload';
	
	/**
	 * autoload classes
	 *
	 * @param string $classname
	 * @return boolean
	 */
	public static function autoload( $classname )
	{
		$eventCall = \up\events::notify(
			  self::EVENT_NAMESPACE
			, self::EVENT_BEFORE_AUTOLOAD
			, array( $classname )
			, array( __CLASS__, 'callback' )
		);
			
		if ( $eventCall === true ) return true;
		
		$filename = str_replace( array( '_', '\\' ), '/', $classname );
		
		if ( !self::inc( $filename ) ) return false;
		
		return true;
	}

	public static function autoloads( array $classes )
	{
		foreach ( $classes as $class )
			if ( !self::autoload( $class ) ) self::exception( 'uknow startup file: ' . $class );
	}
	
	public static function inc($__filename, $__ext = 'php')
	{
		if ( !file_exists( $__filename . '.' . $__ext ) || !is_readable( $__filename . '.' . $__ext ) ) return false;
		
		$return = true;
		$time   = microtime( true );

		if ( !include_once( $__filename . '.' . $__ext ) ) $return = false;
		
		$loadTime = microtime( true ) - $time;
		
		\up\events::notify(
			  self::EVENT_NAMESPACE
			, self::EVENT_AFTER_AUTOLOAD
			, array( $__filename . '.' . $__ext, $loadTime )
		);
		
		return $return;
	}
	
	public static function callback( $result, $args )
	{
		if ( $result === true ) return false;
	}
	
	private static function exception( $msg, $code = 0 )
	{
		throw new exception( $msg, $code );
	}
}