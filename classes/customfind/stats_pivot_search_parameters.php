<?php

class StatsPivotSearchParameters extends OCCustomSearchParameters
{
    /**
     * @var array
     */
    protected $stats = array();

    /**
     * @var array
     */
    protected $pivot = array();

    /**
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param array $stats
     * @return OCCustomSearchParameters
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
     * @return OCCustomSearchParameters
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