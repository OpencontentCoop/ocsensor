<?php

class SensorApiBasicAuthStyle extends ezpRestAuthenticationStyle implements ezpRestAuthenticationStyleInterface
{
    /**
     * @param ezcMvcRequest $request
     * @return ezcAuthentication|ezcMvcInternalRedirect
     * @throws \Opencontent\Sensor\Api\Exception\UnauthorizedException
     */
    public function setup(ezcMvcRequest $request)
    {
        $jwtManager = SensorJwtManager::instance();
        if ($jwtManager->isJwtAuthEnabled()
            && isset($request->raw['HTTP_AUTHORIZATION'])
            && preg_match('/Bearer\s(\S+)/', $request->raw['HTTP_AUTHORIZATION'], $matches)) {
            $jwt = $matches[1];
            if ($userID = $jwtManager->getUserIdFromJWTToken($jwt)) {
                $auth = new ezcAuthentication(new ezcAuthenticationIdCredentials($userID));
                $auth->addFilter(new SensorApiAuthenticationEzFilter());

                return $auth;
            }
        } elseif ($request->authentication === null) {
            eZSession::lazyStart();
            $userID = eZSession::issetkey('eZUserLoggedInID', false) ? eZSession::get('eZUserLoggedInID') : eZUser::anonymousId();
            if ($userID) {
                $auth = new ezcAuthentication(new ezcAuthenticationIdCredentials($userID));
                $auth->addFilter(new SensorApiAuthenticationEzFilter());

                return $auth;
            }

            $authRequest = clone $request;
            $authRequest->uri = "{$this->prefix}/auth/http-basic-auth";
            $authRequest->protocol = "http-get";

            return new ezcMvcInternalRedirect($authRequest);
        }

        $cred = new ezcAuthenticationPasswordCredentials($request->authentication->identifier, $request->authentication->password);

        $auth = new ezcAuthentication($cred);
        $auth->addFilter(new SensorApiAuthenticationEzFilter());
        return $auth;
    }

    public function authenticate(ezcAuthentication $auth, ezcMvcRequest $request)
    {
        if (!$auth->run()) {
            $request->uri = "{$this->prefix}/auth/http-basic-auth";
            $request->protocol = "http-get";

            return new ezcMvcInternalRedirect($request);
        } else {
            // We're in. Get the ezp user and return it
            return eZUser::fetchByName($auth->credentials->id);
        }
    }

}