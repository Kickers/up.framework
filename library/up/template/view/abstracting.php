<?php
namespace up\template\view;

abstract class abstracting
{
	protected $registry = array();
	
	abstract public function render( $filename, $vars = null );

	abstract public function getVars();

	public function __construct( array $registry = array() )
	{
		$this->registry = $registry;
	}
}