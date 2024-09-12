<?php
    declare(strict_types=1);

    namespace Stolfam\MS\Azure\Env;

    /**
     * @property-read int    $id
     * @property-read string $name
     * @property-read string $email
     */
    class UserProfile
    {
        public readonly string $id;
        public readonly string $name;
        public readonly string $email;

        /**
         * @param string $id
         * @param string $name
         * @param string $email
         */
        public function __construct(string $id, string $name, string $email)
        {
            $this->id = $id;
            $this->name = $name;
            $this->email = $email;
        }
    }