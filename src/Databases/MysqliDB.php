<?php
namespace App\Databases;

use App\Config\ConfigDatabases\ConfigMysqliDB;
use Exception;
use mysqli;
use mysqli_result;


class MysqliDB {
    private static MysqliDB|null $instance = null;
    private mysqli|false $conn = false;

    private function __construct() {}

    private function connect() {
        if (!$this->conn) {
            // mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                $this->conn = mysqli_connect(
                    ConfigMysqliDB::HOST,
                    ConfigMysqliDB::DB_USER,
                    ConfigMysqliDB::PASSWORD,
                    ConfigMysqliDB::DB_NAME,
                    ConfigMysqliDB::PORT
                );
                
                
                if (!$this->conn) {
                    die("Connection failed: " . mysqli_connect_error());
                }
            } catch (Exception $e) {
                var_dump(($this->conn));
                die("Connection failed: " . mysqli_connect_error());
            }
        }
    }

    public function disconnect() {
        if ($this->conn) {
            mysqli_close($this->conn);
            $this->conn=false;
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self;
        }
        self::$instance->connect();
        return self::$instance;
    }

    public function query(string $sql): mysqli_result|bool {
        if ($this->conn) {
            try {
                return mysqli_query($this->conn, $sql);
            } catch (Exception $e) {
                die($e);
            }
        } else {
            return self::getInstance()->query($sql);
        }
    }

    public function get_last_inserted_id(): int {
        return mysqli_insert_id($this->conn);
    }

    public function getError() {
        return mysqli_error($this->conn);
    }

    public function getMany(mysqli_result $data) {
        return mysqli_fetch_all($data, MYSQLI_ASSOC);
    }

    public function getOne(mysqli_result $data) {
        return mysqli_fetch_assoc($data);
    }

    public function isEntities(mysqli_result $data): bool {
        return !!$data->num_rows;
    }

}
