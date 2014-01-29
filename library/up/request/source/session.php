<?php
namespace up\request\source;

use up\object;

class session extends object
{
	private $init = false;

	public function __construct( $sessionName = 'PHPSESSID' )
	{
		$this->init( $sessionName );

		parent::__construct( $_SESSION );
	}

	public function init( $sessionName )
	{
		if( PHP_SAPI == 'cli' )
			return;

		if( $_REQUEST[ $sessionName ] )
			self::setId( $_REQUEST[ $sessionName ] );

		if( array_key_exists( $sessionName, $_COOKIE ) && !$_COOKIE[ $sessionName ] ) {
			unset( $_COOKIE[ $sessionName ] );
		}

		session_start();

		$this->init = true;
	}

	protected function setId( $sessionId )
	{
		session_id( $sessionId );
	}

	public function destroy()
	{
		if ( !$this->init )
			return;

		session_unset();
		session_destroy();

		$this->init = false;
	}

	public function __set( $index, $value = null )
	{
		$_SESSION[$index] = $value;

		parent::__set( $index, $value );
	}
}