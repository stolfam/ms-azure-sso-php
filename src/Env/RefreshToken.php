<?php
    declare(strict_types=1);

    namespace Stolfam\MS\Azure\Env;

    final class RefreshToken extends Token
    {
        public int $rotatesOn; // time

        /**
         * @param string $value
         * @param int    $expiresOn
         * @param int    $rotatesOn
         */
        public function __construct(string $value, int $expiresOn, int $rotatesOn)
        {
            parent::__construct($value, $expiresOn);
            $this->rotatesOn = $rotatesOn;
        }

        public function shouldRotate(): bool
        {
            return $this->rotatesOn < time();
        }

        public function __serialize(): array
        {
            $data = parent::__serialize();
            $data['rotateOn'] = $this->rotatesOn;

            return $data;
        }

        public function __unserialize(array $data): void
        {
            parent::__unserialize($data);
            if (isset($data['rotateOn'])) {
                $this->rotatesOn = (int) $data['rotateOn'];
            }
        }
    }