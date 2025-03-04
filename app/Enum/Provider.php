<?php

namespace App\Enum;

enum Provider:string{
    case Google="google";
    case Github="github";

    public static function values(): array{
        return array_map(
            fn(self $provider) => $provider->value,
            self::cases()
        );
    }
}
