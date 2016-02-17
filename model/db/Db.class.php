<?php
class Db {
  /**
   * Instance de la classe PDO
   *
   * @var PDO
   * @access private
   */
  private $PDOInstance = null;
  
  /**
   * Instance de la classe Db
   *
   * @var Db
   * @access private
   * @static
   *
   */
  private static $instance = null;

  /**
   * Constructeur
   *
   * @param
   *          void
   * @return void
   * @see PDO::__construct()
   * @access private
   */
  private function __construct() {
    $this->PDOInstance = new PDO('mysql:dbname=' . SQL_DTB . ';host=' . SQL_HOST . ';charset=utf8', SQL_USER, SQL_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
    $this->PDOInstance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $this->PDOInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  /**
   * Crée et retourne l'objet Db
   *
   * @access public
   * @static
   *
   * @param
   *          void
   * @return Db $instance
   */
  public static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new Db();
    }
    return self::$instance;
  }

  /**
   * Exécute une requête SQL avec PDO
   *
   * @param string $query
   *          La requête SQL
   * @return PDOStatement Retourne l'objet PDOStatement
   */
  public function query($query) {
    return $this->PDOInstance->query($query);
  }

  /**
   * Prepares a statement for execution and returns a statement object
   *
   * @param string $statement
   *          A valid SQL statement for the target database server
   * @param array $driver_options
   *          Array of one or more key=>value pairs to set attribute values for the PDOStatement obj
   *          returned
   * @return PDOStatement
   */
  public function prepare($statement, $driver_options = false) {
    if (! $driver_options) {
      $driver_options = array();
    }
    return $this->PDOInstance->prepare($statement, $driver_options);
  }
  
  /**
   * Execute an SQL statement and return the number of affected rows
   *
   * @param string $statement
   */
  public function exec($statement) {
    return $this->PDOInstance->exec($statement);
  }
}