<?php
    require __DIR__ . "/../bootstrap.php";

    use Tester\Assert;


    $token = new class("tokendata", time()) extends \Stolfam\MS\Azure\Env\Token {

    };

    Assert::same(true, $token->isValid());

    sleep(1);

    Assert::same(false, $token->isValid());