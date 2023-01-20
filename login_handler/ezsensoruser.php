<?php

class ezsensoruser extends eZUser
{
    public static function loginUser($login, $password, $authenticationMatch = false)
    {
        $user = self::_loginUser($login, $password, $authenticationMatch);
        $ini = eZINI::instance();
        $allowUserLogin = !$ini->hasVariable('UserSettings', 'DenyUserAccess')
            || $ini->variable('UserSettings', 'DenyUserAccess') !== 'enabled';
        if ($user instanceof eZUser) {
            if (
                $allowUserLogin
                || !$user->contentObject()->attribute('class_identifier') == 'user'
                || $user->hasAccessTo('sensor', 'manage')['accessWord'] !== 'no'
            ) {
                self::loginSucceeded($user);
                return $user;
            } else {
                return eZUser::fetch(eZUser::anonymousId());
            }
        } else {
            self::loginFailed($user, $login);
            return false;
        }
    }
}