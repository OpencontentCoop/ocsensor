<?php

class SensorPostCreateStruct
{
    public $contentObjectId;

    public $authorUserId;

    public $approverUserIdArray = array();

    public $observerUserIdArray = array();

    public $configParams = array();

    public $privacy;

    public $moderation;
}