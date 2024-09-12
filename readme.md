# MS Azure SSO PHP

## Install
`composer require stolfam/ms-azure-sso-php`

### Nette
Neon config:
```
parameters:
    microsoft:
        azure:
            loginBaseUri: https://login.microsoftonline.com
            apiBaseUri: https://login.windows.net
            appId: xxx
            clientSecret: xxx
            tenantId: xxx
            redirectUri: http://localhost
               
services:
    - Stolfam\MS\Azure\Client(%microsoft.azure%)
```

## Use
Redirect to Login URL:
```
$client = new Client($arrayArgs);
$state = "abc123";
$loginUrl = $client->getLoginUrl($state)
// redirect to $loginUrl to invoke user authentication with MS AZure
```
Handle returned Authorization Code:
```
$code = $_GET['code'];
$state = $_GET['state']; // abc123

// set callback
$client->onAuthSuccess[] = function(UserProfile $userProfile) {
    // authentication successful
    // persist user data where you need
    echo $userProfile->id;
    echo $userProfile->name;
    echo $userProfile->email;
};

if($client->authorize($code)) {
    // success
    // i.e. redirect back to original page before login invoked
}
```
Handle expired login:
```
if(!$client->isSessionValid()) {
    // session expired
    // try to re-authorize
    $client->invokeReAuthorization();
}
```