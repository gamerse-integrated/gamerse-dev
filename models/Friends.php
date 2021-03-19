<?php
class Friends{
    private $db = null;

    public function __construct($db){
        $this->db = $db;
    }
    public function findAllFriends($id){
        $statement1 = "
            SELECT
                pid2 as friend, status
            FROM
                friends
            WHERE pid1=? 
            
            union

            SELECT
                pid1 as friend, status
            FROM
                friends
            WHERE  pid2=?;
            ";
        

        try {
            $statement = $this->db->prepare($statement1);
           
            $statement->execute(array($id, $id));
            
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            
            return $result;
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
        
    }
    // sendRequest, acceptRequest, removeFriend(reject same thing)
    
}