<?php
    declare(strict_types=1);

    namespace Stolfam\MS\Azure\Env;

    class IdentityToken extends Token
    {
        public function getUserProfile(): ?UserProfile
        {
            $fragments = explode(".", $this->value);

            if (count($fragments) >= 1) {
                $data = json_decode(base64_decode(str_pad(strtr($fragments[1], '-_', '+/'), strlen($fragments[1]) % 4,
                    '=', STR_PAD_RIGHT)));

                $roles = [];
                if (isset($data->roles) && is_array($data->roles)) {
                    foreach ($data->roles as $role) {
                        $roles[] = $role;
                    }
                }

                return new UserProfile($data->sub, ($data->given_name ?? "") . " " . ($data->family_name ?? ""),
                    $data->email ?? $data->upn ?? "", $roles);
            }

            return null;
        }
    }