<?php
class Dbh{
  protected $servername;
  protected $dbPort;
  protected $dbName;
  protected $dbUsername;
  protected $dbPassword;

  protected $pdo;

  function __construct() {
    $this->servername = $_SERVER["RDS_HOSTNAME"];
    $this->dbUsername = $_SERVER["RDS_USERNAME"];
    $this->dbPassword = $_SERVER["RDS_PASSWORD"];
    $this->dbPort = $_SERVER["RDS_PORT"];

    $this->dbName = $_SERVER["RDS_DB_NAME"];

    try {
      $this->pdo = new PDO("mysql:host=$this->servername;port=$this->dbPort;dbname=$this->dbName", $this->dbUsername, $this->dbPassword);
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      // echo 'Connected successfully<br>';
    } catch(Exception $e) {
      $this->handleError($e);
    }
  }

  // Handle error by logging it and redirecting
  protected function handleError($exception) {
    error_log($exception->getMessage(), 0);
    header('Location: ../error.php');
    $pdo = null;
    exit();
  }

  function selectOne($statement, $parameters) {
    $results;
    try {
      $stmt = $this->pdo->prepare($statement);
      $stmt->execute($parameters);
      $results = $stmt->fetch();
    } catch(Exception $e) {
      $this->handleError($e);
    }
    return $results;
  }

  function selectAll($statement, $parameters) {
    error_log("hah", 0);
    $results;
    try {
      $stmt = $this->pdo->prepare($statement);
      $stmt->execute($parameters);
      $results = $stmt->fetchAll();
    } catch(Exception $e) {
      $this->handleError($e);
    }
    return $results;
  }

  function execute($statement, $parameters) {
    $results;
    try {
      $stmt = $this->pdo->prepare($statement);
      $stmt->execute($parameters);
    } catch(Exception $e) {
      $this->handleError($e);
    }
    return $results;
  }
}
?>
