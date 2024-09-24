<?php
    require __DIR__ . "/../bootstrap.php";

    use Tester\Assert;


    $rotateOn = time() + 1;
    $expiresOn = time() + 1;
    $refreshToken = new \Stolfam\MS\Azure\Env\RefreshToken("tokendata.xyz", $expiresOn, $rotateOn);

    Assert::same(true, $refreshToken->isValid());
    Assert::same(false, $refreshToken->shouldRotate());

    $serializedToken = serialize($refreshToken);

    sleep(2);

    $refreshToken = unserialize($serializedToken);

    Assert::same(true, $refreshToken->shouldRotate());
    Assert::same(false, $refreshToken->isValid());