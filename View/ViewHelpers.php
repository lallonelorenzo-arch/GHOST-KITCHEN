<?php
declare(strict_types=1);

class ViewHelpers
{
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function url(string $path, array $query = []): string
    {
        $baseUrl = (string) ($GLOBALS['view_base_url'] ?? '');
        $url = $baseUrl . '/' . ltrim($path, '/');
        if ($path === '/') {
            $url = $baseUrl . '/';
        }

        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }

    public static function asset(string $path): string
    {
        return self::url('/public/assets/' . ltrim($path, '/'));
    }

    public static function mediaUrl(mixed $media, string $fallback): string
    {
        if (is_object($media) && method_exists($media, 'getPathFile')) {
            $path = trim((string) $media->getPathFile());
            if ($path !== '') {
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }

                return self::url($path);
            }
        }

        return $fallback;
    }

    public static function money(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }
}

class_alias(ViewHelpers::class, 'V');
