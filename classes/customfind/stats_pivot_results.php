<?php

class StatsPivotSearchResult extends OCCustomSearchResult
{
    public function fromArrayResult(array $resultArray)
    {
        $data = parent::fromArrayResult($resultArray);
        $data['stats'] = isset( $resultArray['stats'] ) ? $resultArray['stats'] : [];
        $data['pivot'] = isset( $resultArray['facet_counts']['facet_pivot'] ) ? $resultArray['facet_counts']['facet_pivot'] : [];

        return $data;
    }
}