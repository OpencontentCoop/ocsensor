<?php

use Firebase\JWT\JWT;
use Opencontent\Sensor\Api\Exception\UnauthorizedException;
use Opencontent\Sensor\Legacy\PermissionService;

class SensorJwtManager
{
    private static $instance;

    private $isEnabled;

    private $privateKey;

    private $passPhrase;

    private $publicKey;

    private $tokenTTL;

    private $alg = 'RS256';

    private $issuer;

    /**
     * SensorJwtManager constructor.
     *
     * Command to generate key pair:
     * openssl genrsa -des3 -out private.pem 2048
     * openssl genrsa -out private.pem 2048
     * openssl rsa -in private.pem -outform PEM -pubout -out public.pem
     */
    private function __construct()
    {
        $this->issuer = eZINI::instance()->variable('SiteSettings', 'SiteURL');
        $privateKeyFilePath = getenv('JWT_PRIVATE_KEY') ? getenv('JWT_PRIVATE_KEY') : eZSys::cacheDirectory() . '/jwt/private.pem';
        $this->passPhrase = getenv('JWT_PASS_PHRASE') ? getenv('JWT_PASS_PHRASE') : false;
        if (file_exists($privateKeyFilePath)){
            $privateKey = file_get_contents($privateKeyFilePath);
            if ($this->passPhrase) {
                $this->privateKey = openssl_pkey_get_private($privateKey, $this->passPhrase);
            }else {
                $this->privateKey = $privateKey;
            }
        }
        $publicKeyFilePath = getenv('JWT_PUBLIC_KEY') ? getenv('JWT_PUBLIC_KEY') : eZSys::cacheDirectory() . '/jwt/public.pem';
        if (file_exists($publicKeyFilePath)){
            $this->publicKey = file_get_contents($publicKeyFilePath);
        }
        $this->tokenTTL = getenv('JWT_TOKEN_TTL') ? (int)getenv('JWT_TOKEN_TTL') : 3600;
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new SensorJwtManager();
        }

        return self::$instance;
    }

    public function isJwtAuthEnabled()
    {
        if ($this->isEnabled === null) {
            $this->isEnabled = !empty($this->publicKey) && (!empty($this->privateKey) || is_resource($this->privateKey));
        }

        return $this->isEnabled;
    }

    public function issueJWTToken($username, $password)
    {
        $user = SensorApiAuthUser::authUser($username, $password);
        if (!$user instanceof eZUser) {
            throw new UnauthorizedException('Invalid credentials');
        }

        $currentUser = OpenPaSensorRepository::instance()->getCurrentUser();
        $now = time();

        $role = $currentUser->type;
        if ($role !== 'user'){
            $role = 'sensor-' . $role;
        }
        $allowedRoles = [$role];
        $defaultRole = $role;
        if ($currentUser->behalfOfMode){
            $allowedRoles[] = 'sensor-behalf_of';
        }
        if (PermissionService::isSuperAdmin($currentUser)){
            $allowedRoles[] = 'sensor-admin';
            $defaultRole = 'sensor-admin';
        }

        $payload = array(
            "iss" => $this->issuer,
            "aud" => $this->issuer,
            "iat" => $now,
            "nbf" => $now,
            "exp" => $now + $this->tokenTTL,
            "uid" => $user->id(),
            "name" => $currentUser->name,
            "nickname" => $user->Login,
            "email" => $currentUser->email,
            "email_verified" => true,
            "phone_number" => $currentUser->phone,
            "phone_number_verified" => false,
            "fiscal_code" => $currentUser->fiscalCode,
            "https://hasura.io/jwt/claims" => [
                "x-hasura-allowed-roles" => $allowedRoles,
                "x-hasura-default-role" => $defaultRole,
                "x-hasura-user-id" => $user->id(),
                "x-hasura-tenant" => OpenPABase::getCurrentSiteaccessIdentifier()
            ]
        );

        $jwt = JWT::encode($payload, $this->privateKey, $this->alg);
        if ($this->passPhrase){
            openssl_free_key($this->privateKey);
        }
        return $jwt;
    }

    /**
     * @param $jwt
     * @return false|int
     * @throws UnauthorizedException
     */
    public function getUserIdFromJWTToken($jwt)
    {
        try {
            $token = $this->decodeJWTToken($jwt);
            if ($token) {
                return (int)$token->uid;
            }
        } catch (Exception $e) {
            throw new UnauthorizedException($e->getMessage());
        }

        return false;
    }

    /**
     * @param string $jwt
     * @return false|object
     */
    public function decodeJWTToken($jwt)
    {
        $token = JWT::decode($jwt, $this->publicKey, [$this->alg]);
        $now = new DateTimeImmutable();

        if ($token->iss !== $this->issuer ||
            $token->nbf > $now->getTimestamp() ||
            $token->exp < $now->getTimestamp()) {

            return false;
        }

        return $token;
    }
}