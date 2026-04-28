<?php

namespace {
    require __DIR__ . '/../vendor/autoload.php';

    if (!function_exists('get_session_id')) {
        function get_session_id()
        {
            return 'test-session-id';
        }
    }

    if (!function_exists('get_card_folder')) {
        function get_card_folder($card)
        {
            return '/tmp/cards/' . ($card->id ?? 0) . '/';
        }
    }
}

namespace Intervention\Image\Laravel\Facades {
    if (!class_exists(Image::class)) {
        class Image extends \Illuminate\Support\Facades\Facade
        {
            protected static function getFacadeAccessor()
            {
                return 'intervention.image.v3';
            }

            public static function getFacadeRoot()
            {
                $name = static::getFacadeAccessor();

                return static::$resolvedInstance[$name] ?? null;
            }
        }
    }
}
