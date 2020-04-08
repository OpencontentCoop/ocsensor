<?php

class SensorApiBasicAuthStyle extends ezpRestAuthenticationStyle implements ezpRestAuthenticationStyleInterface
{
    public function setup(ezcMvcRequest $request)
    {
        if ($request->authentication === null) {

            eZSession::lazyStart();
            $userID = eZSession::issetkey( 'eZUserLoggedInID', false ) ? eZSession::get( 'eZUserLoggedInID' ) : eZUser::anonymousId();
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