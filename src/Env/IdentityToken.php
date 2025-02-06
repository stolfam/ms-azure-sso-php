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

                $name = "";
                if (isset($data->name)) {
                    $nameFragments = explode(", ", $data->name);
                    for ($i = count($nameFragments) - 1; $i >= 0; $i--) {
                        $name .= $nameFragments[$i] . " ";
                    }
                } else {
                    $name = ($data->given_name ?? "") . " " . ($data->family_name ?? "");
                }
                $name = trim($name);

                return new UserProfile($data->sub, $name,
                    $data->email ?? $data->unique_name ?? $data->upn ?? $data->preferred_username ?? "", $roles);
            }

            return null;
        }
    }