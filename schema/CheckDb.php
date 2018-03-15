<?php

namespace root\schema ;

require_once __DIR__ . '/../autoload.php';

use root\src\DbConnect;
use \Exception;
use \PDO;
use \PDOException;

new CheckDb();

class CheckDb
{
    /**
     * @var \PDO|DbConnect
     *
     * TODO check if the user has rights to create schemas and tables
     *
     */

    protected $db;

    public function __construct()
    {
        $this->connect();
        $schema = $this->getSchemas();
        $this->getTables($schema);

    }
    private function getSchemas() {

        $res = $this->runQuery('SHOW DATABASES;');

        $schema = file_get_contents(ROOT_DIR . 'schema/CreateSchema.sql');

        if (count($res) == 0) {

            $this->runQuery($schema);

        } else {

            foreach ($res as $schemaList) {

                if ($schemaList[0] == 'infinity-test') {

                    return $schemaList[0];

                } else {

                    $this->runQuery($schema);

                }
            }
        }
    }
    private function getTables($schema) {

        $res = $this->runQuery("USE `$schema`;SHOW FULL TABLES WHERE Table_type='BASE TABLE';");

        $table = file_get_contents(ROOT_DIR . 'schema/CreateTable.sql');

        if (count($res) == 0) {

            $this->runQuery($table);

        } else {

            foreach($res as $tables) {

                var_dump($tables[0]);

                if ($tables[0] != 'csvData') {
                    $this->runQuery($table);

                }
            }
        }
    }

    /**
     *
     * create seperate connection without dbname to be able to check if schema exists
     *
     * @throws Exception
     */
    private function connect()
    {
        $dbFile = ROOT_DIR . 'config/db.json';

        if (!file_exists($dbFile))
        {
            throw new Exception("$dbFile does not exists, please run 'make init' to complete the initial setup", 1);
        }

        $db = json_decode(file_get_contents(ROOT_DIR . 'config/db.json'));

        $dsn = "mysql:host=$db->host;port=$db->port;charset=utf8";
        $opt = [
            PDO::ATTR_DEFAULT_FETCH_MODE        => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT                => true,
            PDO::MYSQL_ATTR_INIT_COMMAND        => "SET NAMES utf8"
        ];

        try {
            $pdo = new PDO($dsn, $db->user, $db->pass, $opt);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->db = $pdo;

        }
        catch (PDOException $e) {
            error_log('Connection failed: ' . $e->getMessage());
            exit;
        }
    }

    /**
     * @param $sql
     * @return array
     */
    private function runQuery($sql) {

        try {
            $result = $this->db->query($sql);

            $rs = isset($result) && !empty($result->rowCount()) ? $result->fetchAll(PDO::FETCH_NUM) : array();

        } catch (PDOException $e) {
            error_log("\nError Code:: " . $e->getCode() . "\nError Message:: " . $e->getMessage());
            $rs = array();
        }
        return $rs;

    }
}
