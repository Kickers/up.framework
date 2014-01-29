<?php
namespace up\request\source;

use up\object;

class get extends object
{
	protected $get = array();
	
	public function __construct()
	{
		//$this->get = $_GET;
		$_GET = $this->get;
		$_GET['test'] = 'hello';
		$_GET = null;
		unset($_GET);
		var_dump($_GET);
		$Object = new \Up\Object();
		$_GET = $Object;
		var_dump($_GET);
		$Object->test = 'hello';
		var_dump($_GET);
		
		$this->get['test'] = 'fuck';
		
		//parent::__construct( $_GET );
	}
	
	public function __set( $name, $value )
	{
		parent::__set( $name, $value );
		
		$_GET[$name] = $value;
	}
}