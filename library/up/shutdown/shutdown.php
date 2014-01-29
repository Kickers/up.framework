<?php
namespace up;

use up\shutdown\exception;

/**
 * Shutdown function manager
 * 
 * up\shutdown
 * 
 * @package    $shutdown
 * @relation   $exception, $events
 * @copyright  Up framework (http://up.singleup.org). singleUp team (http://www.singleup.org)
 * @license    http://up.singleup.org/license/ (GNU)
 *
 */
class shutdown
{
	private static $callbacks  = array();
	private static $isInit     = false;
	
	const EVENT_NAMESPACE   = __CLASS__;
	
	const EVENT_BEFORE_CALL = 'before.call';
	const EVENT_AFTER_CALL  = 'after.call';
	
	/**
	 * Add shutdown callback
	 *
	 * @param string $namespace
	 * @param string $shutdownName
	 * @param callable $callback
	 * @param array $params
	 */
	public static function add( $namespace, $shutdownName, $callback, array $params = array() )
	{
		self::init();
		
		if ( !is_callable( $callback ) ) self::exception('is not callable shutdown var');
		
		self::$callbacks[$namespace . ':' . $shutdownName] = array(
			  'callback' => $callback
			, 'params'   => $params
		);
	}
	
	/**
	 * Call shutdown callbacks
	 *
	 */
	public static function call()
	{
		\up::notify(
			  self::EVENT_NAMESPACE
			, self::EVENT_BEFORE_CALL
			, array( self::$callbacks )
		);
		
		$result = array();
		foreach ( self::$callbacks as $shutdownName => $shutdown )
			$result[$shutdownName] = call_user_func_array( $shutdown['callback'], $shutdown['params'] );

		\up::notify(
			  self::EVENT_NAMESPACE
			, self::EVENT_AFTER_CALL
			, array( $result )
		);
	}
	
	/**
	 * Remove shutdown callback by namespace and callback name
	 *
	 * @param string $namespace
	 * @param string $shutdownName
	 */
	public static function remove( $namespace, $shutdownName )
	{
		unset( self::$callbacks[$namespace . ':' . $shutdownName] );
	}
	
	/**
	 * Clear all shutdown callback
	 *
	 * @param unknown_type $namespace
	 * @return unknown
	 */
	public static function clear( $namespace = null )
	{
		if ( $namespace === null ) self::$callbacks = array();
	}
	
	
	private static function init()
	{
		if ( self::$isInit === true ) return;

			\register_shutdown_function(array(
			  __CLASS__
			, 'call'
		));
		
		self::$isInit = true;
	}
	
	private static function exception( $msg, $code = 0 )
	{
		throw new exception( $msg, $code );
	}
}