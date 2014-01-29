<?php
namespace up\request;

use up\object;

class data extends object
{
	private $getCallback;
	private $items;

	public function __construct( array &$items )
	{
		parent::__construct( $items );

		$this->items = &$items;
	}

	public function __set( $index, $value = null )
	{
		parent::__set( $index, $value );

		$this->items[$index] = $value;
	}

	public function __get($index)
	{
		$value = parent::offsetGet( $index );

		$value = $this->callback( $value );

		return $value;
	}

	public function get( $index = 0 )
	{
		return parent::__get( $index );
	}

	public function onGet( $callback )
	{
		$this->getCallback = $callback;

		return $this;
	}

	private function callback( $value )
	{
		if ( !$this->getCallback )
			return $value;

		return call_user_func( $this->getCallback, $value );
	}
}