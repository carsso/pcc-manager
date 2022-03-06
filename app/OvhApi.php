<?php

namespace App;

use Exception;
use Ovh\Api as OvhcloudLegacyApi;
use GuzzleHttp\Exception\RequestException;
use League\OAuth2\Client\Token\AccessToken;
use Carsso\OAuth2\Client\Provider\Ovhcloud as OvhcloudOAuth2Api;

class OvhApi
{
    private String $endpoint;
    private OvhcloudOAuth2Api $oauth2Provider;
    private OvhcloudLegacyApi $legacyProvider;
    private ?AccessToken $token = null;
    private ?String $consumerKey;
    private ?String $state = null;
    private String $id;
    private bool $isOauth;
    private String $redirectUrl;

    public function __construct(String $endpoint, $token = null, $state = null)
    {
        OvhApi::checkEndpoint($endpoint);
        $this->endpoint = $endpoint;
        $this->redirectUrl = route('login.redirect', ['endpoint' => $endpoint]);
        if(config('ovh.'.$this->endpoint.'.client_id')) {
            $this->isOauth = true;
        } else {
            $this->isOauth = false;
        }
        if($state) {
            $this->state = $state;
        }
        if($this->isOauth) {
            if($token) {
                $values = json_decode(base64_decode($token), true);
                $this->token = new AccessToken($values);
            }
            $this->oauth2Provider = new OvhcloudOAuth2Api([
                'endpoint'          => $this->endpoint,
                'clientId'          => config('ovh.'.$this->endpoint.'.client_id'),
                'clientSecret'      => config('ovh.'.$this->endpoint.'.client_secret'),
                'redirectUri'       => $this->redirectUrl,
            ]);
            if($this->token) {
                if($this->token->hasExpired() && $this->token->getRefreshToken()) {
                    $refreshToken = $this->token->getRefreshToken();
                    $this->token = $this->oauth2Provider->getAccessToken('refresh_token', [
                        'refresh_token' => $refreshToken
                    ]);
                    # Reinject refresh token in new AccessToken
                    $rawToken = $this->getToken(true);
                    $rawToken['refresh_token'] = $refreshToken;
                    $this->token = new AccessToken($rawToken);
                }
            }
        } else {
            $this->consumerKey = $token;
            $this->legacyProvider = new OvhcloudLegacyApi(
                config('ovh.'.$this->endpoint.'.application_key'),
                config('ovh.'.$this->endpoint.'.application_secret'),
                $this->endpoint,
                $this->consumerKey,
            );
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getToken(bool $raw = false)
    {
        if($this->isOauth) {
            $values = $this->token->getValues();
            $values['access_token'] = $this->token->getToken();
            $values['resource_owner_id'] = $this->token->getResourceOwnerId();
            $values['refresh_token'] = $this->token->getRefreshToken();
            $values['expires'] = $this->token->getExpires();
            if($raw) {
                return $values;
            }
            return base64_encode(json_encode($values));
        } else {
            return $this->consumerKey;
        }
    }

    public function prepareLogin($readOnly = false)
    {
        if($this->isOauth) {
            $authUrl = $this->oauth2Provider->getAuthorizationUrl();
            $this->state = $this->oauth2Provider->getState();
            return $authUrl;
        } else {
            $rights = [
                [ 'method' => 'GET', 'path' => '/*' ],
                [ 'method' => 'POST', 'path' => '/*' ],
                [ 'method' => 'PUT', 'path' => '/*' ],
                [ 'method' => 'DELETE', 'path' => '/*' ],
            ];
            if($readOnly) {
                $rights = [
                    [ 'method' => 'GET', 'path' => '/*' ],
                ];
            }
            $credentials = $this->legacyProvider->requestCredentials($rights, $this->redirectUrl);
            $this->state = $credentials['consumerKey'];
            $validationUrl = $credentials['validationUrl'];
            if($forceValidationUrl = config('ovh.'.$this->endpoint.'.custom_api_validation_url')) {
                $validationUrl = preg_replace('/^(.*\/auth\/)\?/', $forceValidationUrl.'?', $validationUrl);
            }
            return $validationUrl;
        }
    }

    public function doLogin(String $code = null, String $state = null)
    {
        if($this->isOauth) {
            if($state && $this->state && $state != $this->state) {
                abort(400, 'Invalid state, maybe CSRF attack?');
                return false;
            }
            $token = $this->oauth2Provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            $user = $this->oauth2Provider->getResourceOwner($token);
            $this->id = $user->getId();
            $this->token = $token;
        } else {
            $this->id = $state;
            $this->consumerKey = $state;
            $this->legacyProvider = new OvhcloudLegacyApi(
                config('ovh.'.$this->endpoint.'.application_key'),
                config('ovh.'.$this->endpoint.'.application_secret'),
                $this->endpoint,
                $this->consumerKey,
            );
        }
        return $this->get('/me');
    }

    public static function checkEndpoint(String $endpoint, $abort = true)
    {
        if(!config('ovh.'.$endpoint)) {
            if(!$abort) {
                return false;
            }
            abort(404, 'Endpoint '.$endpoint.' not found');
        }
        if(config('ovh.'.$endpoint.'.client_id')) {
            if(!config('ovh.'.$endpoint.'.client_secret')) {
                if(!$abort) {
                    return false;
                }
                abort(404, 'Endpoint '.$endpoint.' client secret not found');
            }
        } elseif(config('ovh.'.$endpoint.'.application_key')) {
            if(!config('ovh.'.$endpoint.'.application_secret')) {
                if(!$abort) {
                    return false;
                }
                abort(404, 'Endpoint '.$endpoint.' application secret not found');
            }
        } else {
            if(!$abort) {
                return false;
            }
            abort(404, 'Endpoint '.$endpoint.' application key or client id not found');
        }

        return true;
    }

    public function apiRequest($method, $path, $content = null, $headers = null)
    {
        if($this->isOauth) {
            $request = $this->oauth2Provider->getAuthenticatedApiRequest(
                strtoupper($method),
                $path,
                $this->token,
                [
                    'headers' => $headers,
                    'body' => $content,
                ],
            );
            $response = $this->oauth2Provider->getResponse($request);
            return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } else {
            $method = strtolower($method);
            return $this->legacyProvider->$method($path, $content, $headers);
        }
    }

    public function get($path, $content = null, $headers = null)
    {
        return $this->apiRequest('GET', $path, $content, $headers);
    }

    public function put($path, $content = null, $headers = null)
    {
        return $this->apiRequest('PUT', $path, $content, $headers);
    }

    public function post($path, $content = null, $headers = null)
    {
        return $this->apiRequest('POST', $path, $content, $headers);
    }

    public function delete($path, $content = null, $headers = null)
    {
        return $this->apiRequest('DELETE', $path, $content, $headers);
    }
}