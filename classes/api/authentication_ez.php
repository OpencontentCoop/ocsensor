<?php

class SensorApiAuthenticationEzFilter extends ezcAuthenticationFilter
{
    const STATUS_KO = 100;

    /**
     * @param ezcAuthenticationPasswordCredentials $credentials
     * @return int
     */
    public function run($credentials)
    {
        //echo '<pre>';var_dump(SensorApiAuthUser::authUser($credentials->id, $credentials->password));die();
        if (SensorApiAuthUser::authUser($credentials->id, $credentials->password)){
            return self::STATUS_OK;
        }

        return self::STATUS_KO;
    }

}

class SensorApiAuthUser extends eZUser
{
    public static function authUser($login, $password, $authenticationMatch = false)
    {
        return self::_loginUser($login, $password, $authenticationMatch) instanceof eZUser;
    }
}