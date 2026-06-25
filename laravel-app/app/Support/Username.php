<?php

namespace App\Support;

class Username
{
    public const MIN_LENGTH = 3;

    public const MAX_LENGTH = 30;

    /**
     * Lowercase and strip a value down to the allowed username characters.
     * Allowed characters are a-z, 0-9, underscore and dot.
     */
    public static function normalize(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9._]/', '', $value) ?? '';
        // Collapse repeated separators and trim them from the edges.
        $value = preg_replace('/[._]{2,}/', '_', $value) ?? '';

        return trim($value, '._');
    }

    /**
     * A username is valid when it is 3-30 characters, starts with a letter or
     * digit, and only contains lowercase letters, digits, underscores or dots.
     */
    public static function isValid(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9][a-z0-9._]{'.(self::MIN_LENGTH - 1).','.(self::MAX_LENGTH - 1).'}$/', $value);
    }

    /**
     * Build a unique, valid username from a seed (email local part or name).
     * The $exists callback receives a candidate and returns true when taken.
     *
     * @param  callable(string): bool  $exists
     */
    public static function generate(string $seed, callable $exists): string
    {
        $base = self::normalize($seed);

        if (mb_strlen($base) < self::MIN_LENGTH) {
            $base = 'user'.($base !== '' ? '_'.$base : '');
        }

        $base = mb_substr($base, 0, self::MAX_LENGTH);
        $candidate = $base;
        $suffix = 1;

        while ($candidate === '' || ! self::isValid($candidate) || $exists($candidate)) {
            $suffix++;
            $trimmed = mb_substr($base, 0, self::MAX_LENGTH - mb_strlen((string) $suffix) - 1);
            $candidate = trim($trimmed, '._').'_'.$suffix;
        }

        return $candidate;
    }
}
