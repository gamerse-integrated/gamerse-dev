<?php
class Snake
{
    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function setHighScore($score, $userName)
    {
        $statement1 = "
            UPDATE `snakes` SET `highscore`=:score WHERE `username`=:uname";

        try {
            $statement1 = $this->db->prepare($statement1);
            $statement1->execute(array(
                'score' => $score,
                'uname' => $userName,
            ));
            return $statement1->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function setLastGame($score, $userName)
    {
        echo $score;
        echo $userName;
        $statement1 = "
            UPDATE `snakes` SET `lastgame`=:score WHERE `username`=:uname";

        try {
            $statement1 = $this->db->prepare($statement1);
            $statement1->execute(array(
                'score' => $score,
                'uname' => $userName,
            ));
            return $statement1->rowCount();
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }
    public function getLastGame($userName)
    {
        $statement = "
            SELECT
                lastgame
            FROM
                snakes
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

    public function getHighScore($userName)
    {
        $statement = "
            SELECT
                highscore
            FROM
                snakes
            WHERE username = ?;
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
}
