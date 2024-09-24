<?php
    declare(strict_types=1);

    namespace Stolfam\MS\Azure;

    use Stolfam\DataStorage\IDataStorage;
    use Stolfam\DataStorage\Impl\CookiesStorage;
    use Stolfam\MS\Azure\Env\AccessToken;
    use Stolfam\MS\Azure\Env\RefreshToken;


    class Client
    {
        protected string $tenantId;
        protected string $appId; // appId is same as clientId
        protected string $clientSecret;
        protected string $loginBaseUri = "https://login.microsoftonline.com";
        protected string $apiBaseUri = "https://login.windows.net";
        protected string $redirectUri;
        protected string $refreshTokenKey;

        protected int $refreshTokenRotationTime = 60; // 60 sec

        protected IDataStorage|null $dataStorage;

        /** @var callable[] */
        public array $onAuthSuccess = [];

        /** @var string[] */
        public array $errors = [];

        public function __construct(array $args)
        {
            $this->dataStorage = new CookiesStorage();

            $requiredArgs = [
                'appId',
                'clientSecret',
                'loginBaseUri',
                'apiBaseUri',
                'redirectUri',
                'tenantId',
                'refreshTokenKey',
                'refreshTokenRotationTime'
            ];

            foreach ($requiredArgs as $requiredArg) {
                if (isset($args[$requiredArg])) {
                    $this->{$requiredArg} = $args[$requiredArg];
                } else {
                    throw new \Exception(__CLASS__ . " is missing an argument: " . $requiredArg);
                }
            }
        }

        public function setDataStorage(?IDataStorage $dataStorage): void
        {
            $this->dataStorage = $dataStorage;
        }

        public function getLoginUrl(string $state, string $scope = "User.Read"): string
        {
            $redirectUri = urlencode($this->redirectUri);

            return "$this->loginBaseUri/$this->tenantId/oauth2/v2.0/authorize?client_id=$this->appId&response_type=code&redirect_uri=$redirectUri&response_mode=query&scope=$scope&state=$state";
        }

        public function isSessionValid(): bool
        {
            if ($this->dataStorage == null) {
                $this->errors[] = "No Data Storage is set.";

                return false;
            }

            $refreshToken = $this->dataStorage->load($this->refreshTokenKey);

            if ($refreshToken == null) {
                $this->errors[] = "No Refresh Token found.";

                return false;
            }

            if ($refreshToken instanceof RefreshToken) {
                if (!$refreshToken->shouldRotate() && $refreshToken->isValid()) {
                    return true;
                }
                $this->errors[] = "Refresh Token expired or needs to be rotated.";
            }

            return false;
        }

        public function invokeReAuthorization(): bool
        {
            if ($this->dataStorage == null) {
                $this->errors[] = "No Data Storage is set.";

                return false;
            }

            $refreshToken = $this->dataStorage->load($this->refreshTokenKey);

            if ($refreshToken == null) {
                $this->errors[] = "No Refresh Token found.";

                return false;
            }

            $response = $this->tryToReAuthorize($refreshToken);
            if ($response != null) {
                if (!empty($response->access_token) && !empty($response->expires_on) &&
                    !empty($response->refresh_token)) {
                    $refreshToken = new RefreshToken($response->refresh_token, (int) $response->expires_on,
                        time() + $this->refreshTokenRotationTime);
                    if (!$this->persistRefreshToken($refreshToken)) {
                        $this->errors[] = "Refresh Token has not been persisted!";
                    }

                    return true;
                }
            }

            return false;
        }

        public function authorize(string $authorizationCode): bool
        {
            if (empty($authorizationCode)) {
                return false;
            }

            $response = $this->tryToAuthorize($authorizationCode);
            if ($response != null) {
                if (!empty($response->access_token) && !empty($response->expires_on) &&
                    !empty($response->refresh_token)) {
                    $accessToken = new AccessToken($response->access_token, (int) $response->expires_on);
                    $refreshToken = new RefreshToken($response->refresh_token, (int) $response->expires_on, time() + $this->refreshTokenRotationTime);
                    if (!$this->persistRefreshToken($refreshToken)) {
                        $this->errors[] = "Refresh Token has not been persisted!";
                    }

                    foreach ($this->onAuthSuccess as $onAuthSuccess) {
                        call_user_func($onAuthSuccess, $accessToken->getUserProfile());
                    }

                    return true;
                }
            }

            return false;
        }

        private function persistRefreshToken(RefreshToken $refreshToken): bool
        {
            if ($this->dataStorage != null) {
                return $this->dataStorage->save($this->refreshTokenKey, $refreshToken);
            }

            return false;
        }

        private function tryToAuthorize(string $authorizationCode): ?\stdClass
        {
            $ch = curl_init("$this->apiBaseUri/$this->tenantId/oauth2/token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                "client_id"     => $this->appId,
                "code"          => $authorizationCode,
                "grant_type"    => "authorization_code",
                "redirect_uri"  => $this->redirectUri,
                "client_secret" => $this->clientSecret
            ]));

            $headers = [
                "Content-Type" => "application/x-www-form-urlencoded"
            ];

            if (!empty($headers)) {
                $h = [];
                foreach ($headers as $key => $header) {
                    $h[] = "$key: $header";
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
            }

            curl_setopt($ch, CURLOPT_POST, true);

            $response = curl_exec($ch);

            $error = null;
            if (curl_error($ch)) {
                $error = curl_error($ch);
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($httpCode == 200) {
                return json_decode($response);
            } else {
                $this->errors[] = "($httpCode) " . $error;
                $this->errors[] = $response;
            }

            return null;
        }

        private function tryToReAuthorize(RefreshToken $refreshToken): ?\stdClass
        {
            $ch = curl_init("$this->apiBaseUri/$this->tenantId/oauth2/token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                "client_id"     => $this->appId,
                "refresh_token" => $refreshToken->__toString(),
                "grant_type"    => "refresh_token",
                "redirect_uri"  => $this->redirectUri,
                "client_secret" => $this->clientSecret
            ]));

            $headers = [
                "Content-Type" => "application/x-www-form-urlencoded"
            ];

            if (!empty($headers)) {
                $h = [];
                foreach ($headers as $key => $header) {
                    $h[] = "$key: $header";
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
            }

            curl_setopt($ch, CURLOPT_POST, true);

            $response = curl_exec($ch);

            $error = null;
            if (curl_error($ch)) {
                $error = curl_error($ch);
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($httpCode == 200) {
                return json_decode($response);
            } else {
                $this->errors[] = "($httpCode) " . $error;
                $this->errors[] = $response;
            }

            return null;
        }

        public function getLogoutURL(): string
        {
            return "$this->loginBaseUri/common/oauth2/v2.0/logout";
        }
    }