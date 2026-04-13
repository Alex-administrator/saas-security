<?php
declare(strict_types=1);

namespace App\Services;

final class SlugService
{
    public function make(string $value): string
    {
        $slug = mb_strtolower($value);
        $slug = preg_replace('/[^a-zA-Z0-9а-яА-ЯёЁ]+/u', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug;
        $slug = preg_replace('/[^a-z0-9-]+/', '', strtolower($slug)) ?? '';

        return $slug !== '' ? $slug : 'item-' . bin2hex(random_bytes(3));
    }
}

