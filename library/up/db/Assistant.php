<?php
namespace Up\Db;

use Up\Db;

class Assistant
{
	const T_INT       = 'INT';
	const T_TINYINT   = 'TINYINT';
	const T_SMALLINT  = 'SMALLINT';
	const T_MEDIUMINT = 'MEDIUMINT';
	const T_BIGINT    = 'BIGINT';

	const T_DECIMAL = 'DECIMAL';
	const T_FLOAT   = 'FLOAT';
	const T_DOUBLE  = 'DOUBLE';
	const T_REAL    = 'REAL';
	const T_BIT     = 'BIT';
	const T_BOOLEAN = 'BOOLEAN';
	const T_SERIAL  = 'SERIAL';

	const T_DATE      = 'DATE';
	const T_DATETIME  = 'DATETIME';
	const T_TIMESTAMP = 'TIMESTAMP';
	const T_TIME      = 'TIME';
	const T_YEAR      = 'YEAR';

	const T_VARCHAR    = 'VARCHAR';
	const T_CHAR       = 'CHAR';
	const T_TINYTEXT   = 'TINYTEXT';
	const T_TEXT       = 'TEXT';
	const T_MEDIUMTEXT = 'MEDIUMTEXT';
	const T_LONGTEXT   = 'LONGTEXT';
	const T_BINARY     = 'BINARY';
	const T_VARBINARY  = 'VARBINARY';
	const T_TINYBLOB   = 'TINYBLOB';
	const T_MEDIUMBLOB = 'MEDIUMBLOB';
	const T_BLOB       = 'BLOB';
	const T_LONGBLOB   = 'LONGBLOB';
	const T_ENUM       = 'ENUM';
	const T_SET        = 'SET';

	const A_BINARY            = 'BINARY';
	const A_UNSIGNED          = 'UNSIGNED';
	const A_CURRENT_TIMESTAMP = 'on update CURRENT_TIMESTAMP';

	const I_PRIMARY  = 'PRIMARY';
	const I_INDEX    = 'INDEX';
	const I_UNIQUE   = 'UNIQUE';
	const I_FULLTEXT = 'FULLTEXT';

	private static $__assistantData = array();
	private static $__assistantDataPattern = array(
		  'init'       => false
		, 'columns'    => array()
		, 'index'      => array()
		, 'connection' => null
		, 'primary'    => null
		, 'unique'     => null
	);

	private $__storage  = array();
	private $__calledClass;


	final public function __construct( array $data = array() )
	{
		$this->__init();
		$this->data( $data );
	}

	public function data( array $saveData = array() )
	{
		if ( empty( $saveData ) ) {
			return $this->__storage;
		}

		$columns = $this->__privateData( 'columns' );
		$data    = $this->__storage;

		foreach( $saveData as $column => $value ) {
			if ( !isset( $columns[$column] ) )
				$this->__error( 'unknown column "' . $column . '"' );

			$data[$column] = $value;
		}

		$this->__storage = $data;
	}

	public function delete( array $where = array() )
	{
		if ( empty( $where ) ) {
			$primary = $this->__privateData( 'primary' );

			if ( is_string( $primary ) )
				$primary = array( $primary );

			foreach( $primary as $column ) {
				$where[$column] = $this->$column;
			}
		}

		return $this->connection()->delete( $where );
	}

	public function save( array $data = array() )
	{
		$this->data( $data );

		$connection = $this->connection();
		$data       = $this->__storage;
		$primary    = $this->__privateData( 'primary' );
		$unique     = $this->__privateData( 'unique' );
		$columns    = $this->__privateData( 'columns' );

		if ( $primary === null && $unique === null ) {
			$result = $connection->insert( $data );
			var_dump( 'no primary && no unique' );

			return $result;
		}

		if ( $primary === null ) {
			$result = $connection->insertUpdate( $data, $data );
			var_dump( 'no primary' );

			return $result;
		}

		if ( is_string( $primary ) )
			$primary = array( $primary );

		$autoIncrement = false;
		foreach( $primary as $columnName ) {
			$column = $columns[$columnName];

			if ( isset( $column['auto_increment'] ) && $column['auto_increment'] === true ) {
				$autoIncrement = $columnName;

				break;
			}
		}

		if ( $autoIncrement ) {
			if ( isset( $data[$autoIncrement] ) ) {
				$update = $data;
				$where  = array( $autoIncrement => $data[$autoIncrement] );
				unset( $update[$autoIncrement] );

				$result = $connection->update( $update, $where );
			} else {
				$result = $connection->insert( $data );
				$lastId = $connection->getLastInserId();

				$this->$autoIncrement = $lastId;
			}

			return $result;
		}

		$result = $connection->insertUpdate( $data, $data );

		return $result;
	}

	public function getRowsBy( array $where = array() )
	{
		$whereSql = $this->connection()->where( $where );

		return $this->connection()->getRows(
			  'SELECT * FROM :table ' . $whereSql
			, $where
			, $this->__getCalledClass()
		);
	}

	public function getRowBy( array $where = array() )
	{
		$this->connection()->limit( 1 );
		$whereSql = $this->connection()->where( $where );

		return $this->connection()->getRow(
			  'SELECT * FROM :table ' . $whereSql
			, $where
			, $this->__getCalledClass()
		);
	}

	public function getAs( $column, array $where = array() )
	{
		$this->connection()->limit( 1 );
		$whereSql = $this->connection()->where( $where );

		return $this->connection()->getAs(
			  $column
			, 'SELECT '. $column .' FROM :table ' . $whereSql
			, $where
			, $this->__getCalledClass()
		);
	}

	public function __set( $column, $value )
	{
		if ( !$this->__columnIsset( $column ) )
			$this->__error( 'unknown column "' . $column . '"' );

		$this->__storage[$column] = $value;
	}

	public function __get( $column )
	{
		if ( isset( $this->__storage[$column] ) )
			return $this->__storage[$column];

		if ( !$this->__columnIsset( $column ) )
			$this->__error( 'unknown column "' . $column . '"' );

		return null;
	}

	private function __init()
	{
		$vars   = get_object_vars($this);
		$ignore = array( '__assistantData', '__storage', '__calledClass' );

		if ( $this->__privateData('init') === true ) {
			foreach( $vars as $column => $params ) {
				if ( in_array( $column, $ignore ) )
					continue;

				unset( $this->$column );
			}

			return true;
		}

		self::$__assistantData[$this->__getCalledClass()] = self::$__assistantDataPattern;

		$columns = $this->__privateData( 'columns' );

		foreach( $vars as $column => $params ) {
			if ( in_array( $column, $ignore ) )
				continue;

			if ( $column == '_index' ) {
				$this->__setIndex( $params );

				continue;
			}

			$columns[$column] = $params;

			unset( $this->$column );
		}

		$this->__privateData( 'columns', $columns );
		$this->__privateData( 'init', true );

		return true;
	}

	private function __privateData( $item = null, $value = null )
	{
		$calledClass = $this->__getCalledClass();

		if ( $item === null )
			return self::$__assistantData[$calledClass];

		if ( $value === null && isset( self::$__assistantData[$calledClass][$item] ) )
			return self::$__assistantData[$calledClass][$item];

		if ( $value !== null ) {
			self::$__assistantData[$calledClass][$item] = $value;

			return $this;
		}

		return null;
	}

	private function __getCalledClass()
	{
		if ( $this->__calledClass )
			return $this->__calledClass;

		return $this->__calledClass = get_called_class();
	}

	private function __columnIsset( $column )
	{
		$calledClass = $this->__getCalledClass();

		return isset( self::$__assistantData[$calledClass]['columns'][$column] );
	}

	private function __error( $msg, $code = 0 )
	{
		throw new \Exception( $msg, $code );
	}

	/**
	 * @return \Up\Db
	 */
	protected function connection()
	{
		$calledClass = $this->__getCalledClass();

		if ( !self::$__assistantData[$calledClass]['connection'] )
			self::$__assistantData[$calledClass]['connection'] = new Db( static::TABLE );

		return self::$__assistantData[$calledClass]['connection'];
	}

	private function __setIndex( array $indexes )
	{
		$this->__privateData( 'index', $indexes );
		unset( $this->_index );

		$primary = null;
		$unique  = array();
		foreach( $indexes as $index ) {
			if ( $index['type'] == self::I_PRIMARY )
				$primary = $index['column'];

			if ( $index['type'] == self::I_UNIQUE )
				$unique[] = $index['column'];
		}

		if ( empty( $unique ) )
			$unique = null;

		$this->__privateData( 'primary', $primary );
		$this->__privateData( 'unique', $unique );
	}
}