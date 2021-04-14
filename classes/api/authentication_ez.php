<?php

class SensorApiAuthenticationEzFilter extends ezcAuthenticationFilter
{
    const STATUS_KO = 100;

    /**
     * @param ezcAuthenticationPasswordCredentials|ezcAuthenticationIdCredentials $credentials
     * @return int
     */
    public function run($credentials)
    {
        if ($credentials instanceof ezcAuthenticationIdCredentials) {
            SensorApiAuthUser::setLoggedUser(eZUser::instance($credentials->id));
            return self::STATUS_OK;
        }
        //echo '<pre>';var_dump(SensorApiAuthUser::authUser($credentials->id, $credentials->password));die();
        if (SensorApiAuthUser::authUser($credentials->id, $credentials->password) instanceof eZUser) {
            return self::STATUS_OK;
        }

        return self::STATUS_KO;
    }

}

class SensorApiAuthUser extends eZUser
{
    public static function setLoggedUser(eZUser $user)
    {
        if (eZUser::currentUserID() != $user->id()) {
            self::loginSucceeded($user);
        }
    }

    public static function authUser($login, $password, $authenticationMatch = false)
    {
        $user = self::_loginUser($login, $password, $authenticationMatch);
        if (!$user instanceof eZUser){
            self::loginFailed($user, $login);
        }

        return $user;
    }
}