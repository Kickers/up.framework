<?php
namespace up\template;

abstract class view
{
	protected $registry = array();
	
	abstract public function render( $filename, $vars = null );

	abstract public function getVars();

	public function __construct( array $registry = array() )
	{
		$this->registry = $registry;
	}
}