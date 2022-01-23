<?php

interface SensorStatisticStorageInterface
{
    public function upsert(SensorStatisticPost $statisticPost);

    public function delete($statisticPostId);
}
