<?php
    require __DIR__ . "/../bootstrap.php";

    use Tester\Assert;


    $userProfile = new \Stolfam\MS\Azure\Env\UserProfile("abc123", "John Doe", "john.doe@example.com");

    Assert::same("abc123", $userProfile->id);
    Assert::same("John Doe", $userProfile->name);
    Assert::same("john.doe@example.com", $userProfile->email);