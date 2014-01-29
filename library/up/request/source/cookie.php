<?php
namespace up\request\source;

use up\request\data;

class cookie extends data
{
	private $expire = 0;
	private $init   = false;

	public function __construct( array &$items )
	{
		parent::__construct( $items );

		$this->init = true;
	}

	public function set( $index, $value = null, $expire = 0 )
	{
		if ( is_array( $index ) || is_object( $index ) ) {
			foreach ( $index as $item => $value ) {
				$this->expire = $expire;

				parent::set( $item, $value );
			}

			return $this;
		}

		$this->expire = $expire;

		return parent::set( $index, $value );
	}

	public function __set( $index, $value = null )
	{
		parent::__set( $index, $value );

		if ( $this->init ) {
			setcookie( $index, $value, $this->expire, '/' );

			$this->expire = 0;
		}
	}
}