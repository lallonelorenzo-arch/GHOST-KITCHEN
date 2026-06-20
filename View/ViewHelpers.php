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

    public static function cssUrl(mixed $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return 'none';
        }

        $isLocalPath = str_starts_with($url, '/');
        $isHttpUrl = preg_match('#^https?://#i', $url) === 1;

        if (!$isLocalPath && !$isHttpUrl) {
            return 'none';
        }

        if (preg_match('/[\x00-\x1F\x7F\'"()\\\\]/', $url) === 1) {
            return 'none';
        }

        return self::e("url('" . $url . "')");
    }

    public static function money(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    public static function stars(float $rating): string
    {
        $rating = max(0.0, min(5.0, $rating));
        $stars = '';

        for ($i = 1; $i <= 5; $i++) {
            $fill = max(0.0, min(1.0, $rating - ($i - 1)));
            if ($fill >= 1.0) {
                $stars .= '<span class="star filled">&#9733;</span>';
            } elseif ($fill > 0.0) {
                $stars .= '<span class="star partial" style="--star-fill:' . self::e((string) round($fill * 100)) . '%">&#9733;</span>';
            } else {
                $stars .= '<span class="star">&#9734;</span>';
            }
        }

        return $stars;
    }
}

class_alias(ViewHelpers::class, 'V');
