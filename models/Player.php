<?php
class Player
{
    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function setOnlineStatus($email){
        $statement1 = "
            UPDATE `player` SET `onlineStatus`=CURRENT_TIMESTAMP() WHERE `mail`=:email
        ";
        // $statement2 = "select userName from player where mail = :email";

        try {
            $statement1 = $this->db->prepare($statement1);
            $statement1->execute(array(
                'email' => $email
            ));
            // echo "here";
            // $statement2 = $this->db->prepare($statement2);
            // $statement2->execute(array(
            //     'email' => $email
            // ));
            // $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $statement1->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function findAll()
    {
        $statement = "
            SELECT
                userName,nickName,unix_timestamp(onlineStatus) * 1000 as onlineStatus
            FROM
                player;
        ";

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find($userName)
    {
        $statement = "
            SELECT
                userName,nickName
            FROM
                player
            WHERE userName = ?;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($userName));
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function getUserName($email)
    {
        $statement = "
            SELECT
                userName
            FROM
                player
            WHERE mail = ?;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($email));
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function insert(array $input)
    {
        $statement = "
            INSERT INTO player
                (UID,userName,nickName,mail,dob,gender,accountStatus)
            VALUES
                (:UID,:userName,:nickName,:mail,:dob,:gender,:accountStatus);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(['userName'=>$input['userName'],'UID'=>$input['UID'],'nickName'=>$input['nickName'],'mail'=>$input['mail'],'dob'=>$input['dob'],'gender'=>$input['gender'],'accountStatus'=>$input['accountStatus']]);
            return $statement->rowCount();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function update($userName, array $input)
    {
        $statement = "
            
            UPDATE `player` SET `name`=:name WHERE `userName` = :userName
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'userName' => (int) $userName,
                'name' => $input['name'],
                
            ));
            return $statement->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function delete($userName)
    {
        $statement = "
            DELETE FROM player
            WHERE userName = :userName;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('userName' => $userName));
            return $statement->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }
}