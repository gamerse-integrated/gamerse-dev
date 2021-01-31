<?php
class Player
{
    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function findAll()
    {
        $statement = "
            SELECT
                userName,nickName
            FROM
                players;
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
                players
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

    public function insert(array $input)
    {
        $statement = "
            INSERT INTO players
                (UID,userName,nickName,mail,dob,gender,accountStatus)
            VALUES
                (:UID,:userName,:nickName,:mail,:dob,:gender,:accountStatus);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(['userName'=>$input['userName'],'UID'=>$input['UID'],'nickName'=>$input['nickName'],'mail'=>$input['mail'],'dob'=>$input['dob'],'gender'=>$input['gender'],'accountStatus'=>$input['accountStatus']]);
            return $statement->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function update($userName, array $input)
    {
        $statement = "
            
            UPDATE `players` SET `name`=:name WHERE `userName` = :userName
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
            DELETE FROM players
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