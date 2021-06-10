<?php

class SensorDailySearchParameters extends OCCustomSearchParameters
{
    /**
     * @var array
     */
    private $stats = array();

    /**
     * @var array
     */
    private $pivot = array();

    /**
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param array $stats
     * @return SensorDailySearchParameters
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
        return $this;
    }

    /**
     * @return array
     */
    public function getPivot()
    {
        return $this->pivot;
    }

    /**
     * @param array $pivot
     * @return SensorDailySearchParameters
     */
    public function setPivot($pivot)
    {
        $this->pivot = $pivot;
        return $this;
    }

    public function jsonSerialize()
    {
        $vars = parent::jsonSerialize();
        $vars['stats'] = $this->stats;
        $vars['pivot'] = $this->pivot;

        return $vars;
    }
}