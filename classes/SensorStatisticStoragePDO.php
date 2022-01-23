<?php

class SensorStatisticStoragePDO implements SensorStatisticStorageInterface
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct($connectionString)
    {
        $this->pdo = new PDO($connectionString);
    }

    public function upsert(SensorStatisticPost $statisticPost)
    {
        $data = (array)$statisticPost;
        $keys = array_keys($data);
        $sql = "INSERT INTO statistic_post (" . implode(',', $keys) . ") VALUES (:"  . implode(', :', $keys) .  ")";
        $result = $this->pdo->prepare($sql)->execute($data);
        if (!$result){
            $updateString = [];
            foreach ($keys as $key){
                $updateString[] = "{$key}=:{$key}";
            }
            $sql = "UPDATE statistic_post SET " . implode(',', $updateString);
            $result = $this->pdo->prepare($sql)->execute($data);
        }
        return $result;
    }

    public function delete($statisticPostId)
    {
        $sql = "DELETE FROM statistic_post WHERE id = " . (int)$statisticPostId;
        return $this->pdo->prepare($sql)->execute();
    }
}
