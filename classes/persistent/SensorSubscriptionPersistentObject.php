<?php

class SensorSubscriptionPersistentObject extends eZPersistentObject
{
    public static function definition()
    {
        return [
            'fields' => [
                'id' => [
                    'name' => 'ID',
                    'datatype' => 'integer',
                    'default' => null,
                    'required' => true,
                ],
                'created_at' => [
                    'name' => 'created_at',
                    'datatype' => 'integer',
                    'default' => time(),
                    'required' => false,
                ],
                'post_id' => [
                    'name' => 'post_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
                'user_id' => [
                    'name' => 'user_id',
                    'datatype' => 'integer',
                    'default' => null,
                ],
            ],
            'keys' => ['post_id', 'user_id'],
            'class_name' => 'SensorSubscriptionPersistentObject',
            'name' => 'ocsensor_subscription',
        ];
    }

    public static function fetch($id)
    {
        return self::fetchObject(self::definition(), null, ['id' => (int)$id]);
    }

    public static function fetchByUserAndPost($userId, $postId)
    {
        return self::fetchObject(self::definition(), null, [
            'post_id' => (int)$postId,
            'user_id' => (int)$userId,
        ]);
    }
    public static function fetchByUser($userId)
    {
        return self::fetchObjectList(
            self::definition(),
            null,
            ['user_id' => (int)$userId,],
            ['created_at' => 'asc']
        );
    }

    public static function countByPost($postId)
    {
        return (int)self::count(self::definition(), [
            'post_id' => (int)$postId,
        ]);
    }

    public static function fetchByPost($postId)
    {
        return self::fetchObjectList(
            self::definition(),
            null,
            ['post_id' => (int)$postId,],
            ['created_at' => 'asc']
        );
    }
}