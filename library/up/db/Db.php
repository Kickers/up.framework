<?php
namespace up;

class db
{
	const EVENT_NAMESPACE  = __CLASS__;
	const EVENT_INIT       = 'init';
	
	const PRIORITY_LOW     = 'LOW_PRIORITY';
	const PRIORITY_QUICK   = 'QUICK';
	const PRIORITY_DELAYED = 'DELAYED';
	
	const INSERT_IGNORE    = 'IGNORE';
	
	private static $init = false;
	private static $PDO;
	
	private $tableName, $query, $order, $limit, $keys, $where;
	
	
	public function __construct( $tableName )
	{
		self::init();
		
		$this->setTableName( $tableName );
	}
	
	public static function connect( $dsn, $user = null, $password = null, array $driverOptions = array() )
	{
		try {
			self::$PDO = new \PDO( $dsn, $user, $password, $driverOptions );
		} catch ( \PDOException $e ) {
			echo 'Connection failed: ' . $e->getMessage();
		}
	}
	
	public static function PDO()
	{
		return self::$PDO;
	}
	
	public function exec( $query, array $params )
	{
		$time = microtime( true );
		
		$query   = $this->replaceTableName( $query );
		$prepare = $this->prepare( $query );
			
		foreach ( $params as $key => $value )
		{
			if ( is_int( $value ) ) $bindType = \PDO::PARAM_INT;
			else $bindType = \PDO::PARAM_STR;
			
			$prepare->bindValue( $key, $value, $bindType );
		}
		
		if ( $prepare->execute() !== true ) self::error( $query, $prepare->errorInfo() );
		
		//var_dump( $query );
		//var_dump( sprintf('%.10f sec', microtime(true) - $time ) );
		unset( $this->query, $this->keys, $this->limit, $this->order, $this->where );

		return $prepare;
	}
	
	public function prepare( $query )
	{
		$prepare = self::$PDO->prepare( $query );
		
		if ( !$prepare ) self::error( $query );
		
		return $prepare;
	}
	
	public function beginTransaction()
	{
		return self::$PDO->beginTransaction();
	}
	
	public function rollBack()
	{
		return self::$PDO->rollBack();
	}
	
	public function commit()
	{
		return self::$PDO->commit();
	}
	
	public function insert( array $keys = array(), array $queryParams = array() )
	{
		$query = array( 'INSERT' );
		
		if ( in_array( self::PRIORITY_LOW, $queryParams ) )  $query[] = self::PRIORITY_LOW;
		if ( in_array( self::INSERT_IGNORE, $queryParams ) ) $query[] = self::INSERT_IGNORE;
			
		$query[] = 'INTO ' . $this->getTableName();
		$query[] = 'SET';
		
		$query[] = self::prepareValues( $keys );
		
		if ( isset( $queryParams['duplicate'] ) )
			$query[] = self::onDuplicate( $queryParams['duplicate'], $keys );
			
		$query = implode( ' ', $query );
		
		$keys  = self::prepareParams( $keys );
		
		return $this->exec( $query, $keys )->rowCount();
	}

	public function insertUpdate( array $insert = array(), array $update = array(), array $queryParams = array())
	{
		$queryParams['duplicate'] = $update;

		return $this->insert( $insert, $queryParams );
	}
	
	public function update( array $keys = array(), array $where = array(), array $queryParams = array() )
	{
		$query = array( 'UPDATE' );
		
		if ( in_array( self::PRIORITY_LOW, $queryParams ) )  $query[] = self::PRIORITY_LOW;
		if ( in_array( self::INSERT_IGNORE, $queryParams ) ) $query[] = self::INSERT_IGNORE;
		
		$query[] = $this->getTableName();
		$query[] = 'SET';
		$query[] = self::prepareValues( $keys );
		
		if ( !empty( $where ) ) $query[] = $this->where( $where );
		
		if ( isset( $this->order ) ) $query[] = $this->order;
		if ( isset( $this->limit ) ) $query[] = $this->limit;
		
		$query = implode( ' ', $query );
		$keys  = array_merge( $keys, $this->keys );
		
		return $this->exec( $query, $keys )->rowCount();
	}
	
	public function delete( array $where = array(), array $queryParams = array() )
	{
		$query = array( 'DELETE' );
		
		if ( in_array( self::PRIORITY_LOW, $queryParams ) )   $query[] = self::PRIORITY_LOW;
		if ( in_array( self::PRIORITY_QUICK, $queryParams ) ) $query[] = self::PRIORITY_QUICK;
		
		$query[] = 'FROM ' . $this->getTableName();
		
		if ( !empty( $where ) ) $query[] = $this->where( $where );
		
		if ( isset( $this->order ) ) $query[] = $this->order;
		if ( isset( $this->limit ) ) $query[] = $this->limit;
		
		$query = implode( ' ', $query );
		
		return $this->exec( $query, $this->keys )->rowCount();
	}
	
	public function order( $key, $part = null )
	{
		$orders = array();
		
		if ( is_array( $key ) )
		{
			foreach ( $key as $keyName => $part )
				$orders[] = self::escape( $keyName ) . ' ' . strtoupper( $part );
		}
		else
		{
			$orders[] = self::escape( $key ) . ' ' . strtoupper( $part );
		}
		
		$orders = implode( ',', $orders );
		
		$this->order = 'ORDER BY ' . $orders;
		
		return $this;
	}
	
	public function limit( $skip, $limit = null )
	{
		$skip = (int) $skip;
		
		if ( is_null( $limit ) ) $limit = $skip;
		else $limit = $skip . ',' . (int) $limit;
		
		$this->limit = 'LIMIT ' . $limit;
		
		return $this;
	}
	
	public function getRow( $query, array $params = array(), $className = 'up\\object' )
	{
		$result = $this->selectQuery( $query, $params );
		$result->setFetchMode( \PDO::FETCH_ASSOC );
		
		$Obj = $result->fetch();

		if ( empty( $Obj ) ) $Obj = false;
		else {
			$Obj = new $className( $Obj );
		}
		
		return $Obj; 
	}
	
	public function getRows( $query, array $params = array(), $className = 'up\\object' )
	{
		$result = $this->selectQuery( $query, $params );
		
		$data = $result->fetchAll( \PDO::FETCH_ASSOC );

		foreach( $data as $num => $item ) {
			$data[$num] = new $className( $item );
		}

		return $data;
	}
	
	public function getAs( $rowName, $query, array $params = array(), $className = 'up\\object' )
	{
		$result = $this->getRow( $query, $params, $className );
		
		if ( !$result ) return null;
		
		return $result->$rowName;
	}
	
	private function selectQuery( $query, $params )
	{
		$query = array( $query );
		
		//if ( isset( $this->where ) ) $query[] = $this->where;
		if ( isset( $this->order ) ) $query[] = $this->order;
		if ( isset( $this->limit ) ) $query[] = $this->limit;
		
		$query = implode( ' ', $query );
		
		if ( !isset( $params['table'] ) )
		{
			$query = $this->replaceTableName( $query );
		}
		//$params = array_merge( (array) $this->keys, $params );
		$keys = $this->keys;
		if ( !$keys ) $keys = $params;

		$keys = (array) $keys;

		return $this->exec( $query, $keys );
	}
	
	private function replaceTableName( $query )
	{
		if ( strpos( $query, ':table' ) !== false )
			$query = str_replace( ':table', $this->getTableName(), $query );
				
		return $query;
	}
	
	public function getLastInserId()
	{
		return self::$PDO->lastInsertId();
	}
	
	public function getTableName()
	{
		return $this->tableName;
	}
	
	public function setTableName( $tableName )
	{
		if ( strpos( $tableName, '.' ) !== false )
		{
			$tableName = explode( '.', $tableName, 2 );
			$tableName = self::escape( $tableName[0] )  . '.' . self::escape( $tableName[1] );
		}
		else
		{
			$tableName = self::escape( $tableName );
		}
		
		$this->tableName = $tableName;
	}
	
	
	private static function init()
	{
		if ( self::$init === true ) return ;
		self::$init = true;
		
		if ( class_exists( '\Up\Events' ) )
		{
			$eventCall = \Up\Events::notify(
				  self::EVENT_NAMESPACE
				, self::EVENT_INIT
				, array(  )
			);
		}
	}
	
	private static function prepareParams( array $params, $keyPrefix = ':' )
	{
		foreach ( $params as $key => $value )
		{
			if ( is_int( $key ) ) continue;
			
			$params[$keyPrefix . $key] = $value;
			unset( $params[$key] );
		}
		
		return $params;
	}
	
	private static function prepareValues( array $keys , $implode = ',', $useValueAsKey = false )
	{
		return implode( $implode, self::prepareValuesAsArray( $keys, $useValueAsKey ) );
	}
	
	private static function prepareValuesAsArray( array $keys, $useValueAsKey = false )
	{
		$queryValue = array();
		foreach ( $keys as $key => $value ) {
			$secondKey = $key;
			if ( $useValueAsKey === true ) $secondKey = $value;
			
			$queryValue[] = self::escape( $key ) . '=:' . $secondKey;
		}
			
		return $queryValue;
	}
	
	private static function exception( $msg, $code )
	{
		throw new \Exception( $msg, $code );
	}
	
	private static function error( $query, $errorInfo = null )
	{
		if ( $errorInfo === null ) $errorInfo = self::$PDO->errorInfo();
		
		self::exception( $errorInfo[2] . "\n" . $query, $errorInfo[1] );
	}
	
	private static function escape( $value )
	{
		return '`' . $value . '`';
	}
	
	public function where( array $where )
	{
		if ( empty( $where ) ) return ;
		
		$this->keys = self::prepareParams( $where, '__' );

		$where = array_combine( array_keys( $where ), array_keys( $this->keys ) );

		return $this->where = 'WHERE ' . self::prepareValues( $where, ' AND ', true );
	}
	
	private static function onDuplicate( $duplicate, &$keys )
	{
		if ( is_array( $duplicate ) )
		{
			$duplicateParams = array();
			
			foreach ( $duplicate as $duplicateKey => $duplicateValue )
			{
				if ( is_int( $duplicateKey ) ) 
				{
					$duplicateParams[] = '`' . $duplicateValue . '`=VALUES(`' . $duplicateValue . '`)';
				}
				else
				{
					$duplicateParams[] = '`' . $duplicateKey . '`=' . $duplicateValue;
					//$keys['__' . $duplicateKey] = $duplicateValue;
				}
			}
			
			$duplicate = implode( ',', $duplicateParams );
		}
		
		$duplicate = 'ON DUPLICATE KEY UPDATE ' . $duplicate;
		
		return $duplicate;
	}
}