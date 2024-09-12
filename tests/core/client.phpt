<?php
    require __DIR__ . "/../bootstrap.php";

    use Tester\Assert;


    $files = scandir(__DIR__);
    $args = null;
    foreach ($files as $file) {
        if (str_contains($file, "config.json")) {
            $args = (array) json_decode(file_get_contents($file));
            break;
        }
    }

    if ($args == null) {
        die("Any config.json file is missing.");
    }

    Assert::error(function () {
        new \Stolfam\MS\Azure\Client([]);
    }, Exception::class);

    $client = new \Stolfam\MS\Azure\Client($args);
    $client->setDataStorage(new \Stolfam\DataStorage\Impl\FileStorage(__DIR__ . "/../temp", "client", "test_"));
    $client->onAuthSuccess[] = function (\Stolfam\MS\Azure\Env\UserProfile $userProfile) {
        var_dump($userProfile);
    };

    // $state = "xyz123";
    // $loginUrl = $client->getLoginUrl($state);
    // echo $loginUrl . "\n";
    // die();

    // $code = file_get_contents("authorization_code");
    // $profile = $client->getUserProfile($code);
    // var_dump($profile);

    // if(!$client->isSessionValid())
    //    $client->invokeReAuthorization();

    foreach ($client->errors as $error) {
        echo $error . "\n";
    }