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
        public string $id;
        public string $name;
        public string $email;
        /** @var string[] */
        public array $roles;

        /**
         * @param string $id
         * @param string $name
         * @param string $email
         * @param array  $roles
         */
        public function __construct(string $id, string $name, string $email, array $roles = [])
        {
            $this->id = $id;
            $this->name = $name;
            $this->email = $email;
            $this->roles = $roles;
        }
    }