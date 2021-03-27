<?php
class Friends{
    private $db = null;

    public function __construct($db){
        $this->db = $db;
    }
    public function findAllFriends($id){
        $statement = "
            SELECT
                id,pid2 as friend, status
            FROM
                friends
            WHERE pid1=? 
            
            union

            SELECT
                id,pid1 as friend, status
            FROM
                friends
            WHERE  pid2=?;
            ";
        

        try {
            $statement = $this->db->prepare($statement);
           
            $statement->execute(array($id, $id));
            
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            
            return $result;
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
        
    }
    public function findFriendRequests($id){
        $statement = "
            SELECT
                id,pid1
            FROM
                friends
            WHERE pid2=? and status='P';
            ";
        

        try {
            $statement = $this->db->prepare($statement);
           
            $statement->execute(array($id));
            
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            print_r($result);
            return $result;
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
        
    }
    public function addFriend($id){
        $statement = "
            update friends set status=:status where id=:id
        ";
        

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'status' => 'F',
                'id' => $id,  
            ));
            return $statement->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
        
    }
    public function sendFriendReq($userName,$friendName){
        $statement = "
        INSERT INTO friends (pid1, pid2 , status)
        VALUES (:pid1 , :pid2, 'P');
        ";
        

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'pid1' => $userName,  

                'pid2' => $friendName,  
            ));
            return $statement->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
        
    }
    public function rFriend($id){
        $statement = "
            delete from friends where id=:id
        ";
        

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'id' => $id,  
            ));
            return $statement->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
        
    }
    
}