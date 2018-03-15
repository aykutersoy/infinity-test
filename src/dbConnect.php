<?php

namespace root\src;

use \PDO;
use \PDOException;
use \Exception;

class DbConnect
{

    /**
     * @var pdo connection holder
     */
    protected static $instance;
    protected $pdo;
    protected $table = 'csvData';
    public $fileds = [
        "eventDatetime" => "eventDatetime",
        "eventAction" => "eventAction",
        "callRef" => "callRef",
        "eventValue" => "eventValue",
        "eventCurrencyCode" => "eventCurrencyCode",
        "fileName" => "fileName",
    ];

    /**
     * @return DbConnect|pdo
     * @throws \Exception
     */
    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }


    /**
     * Connects to MySQL
     * @throws \Exception
     */
    private function __construct()
    {
        $dbFile = ROOT_DIR . 'config/db.json';

        if (!file_exists($dbFile))
        {
            throw new Exception("$dbFile does not exists, please run 'make init' to complete the initial setup", 1);
        }

        $db = json_decode(file_get_contents(ROOT_DIR . 'config/db.json'));

        $dsn = "mysql:host=$db->host;port=$db->port;dbname=$db->name;charset=utf8";
        $opt = [
            PDO::ATTR_DEFAULT_FETCH_MODE        => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT                => true,
            PDO::MYSQL_ATTR_INIT_COMMAND        => "SET NAMES utf8"
        ];

        try {
            $this->pdo = new PDO($dsn, $db->user, $db->pass, $opt);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e) {
            error_log('Connection failed: ' . $e->getMessage());
            exit;
        }
    }

    /**
     * Magic method clone is empty to prevent duplication of connection
     */
    private function __clone() {

    }

    /**
     * @param $data
     * @return string
     */
    public function dbInsert($data)
    {

        $stmt = $this->pdo->prepare("
            INSERT INTO `$this->table`(`eventDatetime`, `eventAction`, `callRef`, `eventValue`, `eventCurrencyCode`, `fileName`)
            VALUES(:eventDatetime, :eventAction, :callRef, :eventValue, :eventCurrencyCode, :fileName)");

        try {

            $this->pdo->beginTransaction();

            $stmt->execute($data);

            $this->pdo->commit();

        } catch(PDOExecption $e) {

            $this->pdo->rollback();

        }

        return $this->pdo->lastInsertId();

    }

    /**
     * @param $fileName
     * @return int
     */
    public function dbDuplicationCheck($fileName)
    {

        $stmt = $this->pdo->prepare("
            SELECT COUNT(ID) AS `count` FROM `$this->table`
            WHERE `fileName` = :filename AND DATE_FORMAT(`importedDate`, '%Y-%m-%d') = :importedDate");
        $stmt->bindParam(':filename', $fileName, PDO::PARAM_STR);
        $stmt->bindParam(':importedDate', date('Y-m-d'), PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return intval($result['count']);

    }

    /**
     * @return void
     */
    public function closeConnection() {

        $this->pdo = null;
    }


}
