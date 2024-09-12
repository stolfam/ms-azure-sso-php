<?php
    declare(strict_types=1);

    namespace Stolfam\MS\Azure\Env;

    abstract class Token implements \Serializable
    {
        protected string $value;
        protected int $expiresOn;

        /**
         * @param string $value
         * @param int    $expiresOn
         */
        public function __construct(string $value, int $expiresOn)
        {
            $this->value = $value;
            $this->expiresOn = $expiresOn;
        }

        public function isValid(): bool
        {
            return $this->expiresOn >= time();
        }

        public function __toString(): string
        {
            return $this->value;
        }

        public function serialize(): string
        {
            return json_encode($this->__serialize());
        }

        public function unserialize(string $data): void
        {
            $this->__unserialize(json_decode($data));
        }

        public function __serialize(): array
        {
            return [
                "value"      => $this->value,
                "expires_on" => $this->expiresOn
            ];
        }

        public function __unserialize(array $data): void
        {
            if (isset($data['value'])) {
                $this->value = (string) $data['value'];
            }
            if (isset($data['expires_on'])) {
                $this->expiresOn = (int) $data['expires_on'];
            }
        }
    }