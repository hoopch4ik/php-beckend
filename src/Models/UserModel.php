<?php
namespace App\Models;

use App\Config\ConfigKeys;
use App\Databases\MysqliDB;
use DateTime;
use mysqli_result;

class UserModel extends Model {
    public int $id;
    public string $role;
    public string $nice_name;
    public string $email;
    public string|null $image_url;
    public string $password_hash;
    public DateTime $created_at;
    public DateTime $updated_at;
    public DateTime|null $reset_password_at;


    public function __construct(
        int $id,
        string $role,
        string $nice_name,
        string $email,
        string $image_url,
        string $password_hash,
        DateTime $created_at,
        DateTime $updated_at,
        DateTime|null $reset_password_at,
    ) {
        $this->id=$id;
        $this->role=$role;
        $this->nice_name = $nice_name;
        $this->email = $email;
        $this->image_url=$image_url;
        $this->password_hash = $password_hash;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->reset_password_at = $reset_password_at;
    }

    public static function create(
        string $role,
        string $nice_name,
        string $email,
        string|null $image_url,
        string $password,
    ): self|false {
        $password_hash = md5(ConfigKeys::SECRET_KEY.$password);

        $str = "insert into `user` (role, nice_name, email, image_url, password_hash, created_at, updated_at)
        values ('%s','%s','%s','%s','%s', '%s', '%s')";
        $datetime = parent::getDateTime();
        $sql = sprintf($str,
            $role,
            $nice_name,
            $email,
            $image_url,
            $password_hash,
            $datetime,
            $datetime
        );
        $isInserted = MysqliDB::getInstance()->query($sql);
        $last_inserted_id = MysqliDB::getInstance()->get_last_inserted_id();

        if ($isInserted) {
            return self::getById($last_inserted_id);
        } else {
            self::$error_message = MysqliDB::getInstance()->getError();
            return false;
        }
    }

    public static function deleteById(int $id) {
        // delete user
    }

    public static function update(UserModel $user): bool|mysqli_result {
        $sql = "update user
        set nice_name={$user->nice_name}, email={$user->email}, image_url={$user->image_url}
        where id={$user->id}";
        return MysqliDB::getInstance()->query($sql);
    }
    
    public static function updateGlobal(UserModel $user): bool|mysqli_result {
        $sql = "update user
        set role={$user->role}, nice_name={$user->nice_name}, email={$user->email}, image_url={$user->image_url}
        where id={$user->id}";
        return MysqliDB::getInstance()->query($sql);
    }

    public static function getById(int $id): self|false {
        $data = MysqliDB::getInstance()->query("select * from user where id = $id");
        $entity = MysqliDB::getInstance()->getOne($data);

        if (!$entity) {
            self::$error_message = MysqliDB::getInstance()->getError();
            return false;
        }

        return new self(
            $entity["id"],
            $entity["role"],
            $entity["nice_name"],
            $entity["email"],
            $entity["image_url"],
            $entity["password_hash"],
            new DateTime($entity["created_at"]),
            new DateTime($entity["updated_at"]),
            new DateTime($entity["reset_password_at"])
        );
    }

    /**
     * @return UserModel[] 
     */
    public static function getAll() {
        $data = MysqliDB::getInstance()->query("select * from user");
        $users = MysqliDB::getInstance()->getMany($data);
        /**
         * @var UserModel[]
         */
        $user_models = [];

        foreach ($users as $entity) {
            $user_models[] = new self(
                $entity["id"],
                $entity["role"],
                $entity["nice_name"],
                $entity["email"],
                $entity["image_url"],
                $entity["password_hash"],
                new DateTime($entity["created_at"]),
                new DateTime($entity["updated_at"]),
                new DateTime($entity["reset_password_at"])
            );
        }

        return $user_models;
    }

    public static function getbyEmail(string $email): self|false {
        $data = MysqliDB::getInstance()->query("select * from user where email = '$email'");
        $entity = MysqliDB::getInstance()->getOne($data);
        
        if (!$entity) {
            self::$error_message = MysqliDB::getInstance()->getError();
            return false;
        }

        return new self(
            $entity["id"],
            $entity["role"],
            $entity["nice_name"],
            $entity["email"],
            $entity["image_url"],
            $entity["password_hash"],
            new DateTime($entity["created_at"]),
            new DateTime($entity["updated_at"]),
            new DateTime($entity["reset_password_at"])
        );
    }

    public static function isUserByEmail(string $email): bool {
        $data = MysqliDB::getInstance()->query("select * from user where email = '$email'");
        $isEntities = MysqliDB::getInstance()->isEntities($data);

        return $isEntities;
    }

    public static function isCorrectPasswords(string $password, string $password_hash) {
        return md5(ConfigKeys::SECRET_KEY.$password) == $password_hash;
    }
}

