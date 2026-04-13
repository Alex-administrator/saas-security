<?php
declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class View
{
    public static function render(string $view, array $data = []): string
    {
        $viewFile = base_path('app/Views/' . str_replace('.', '/', $view) . '.php');
        if (!is_file($viewFile)) {
            throw new RuntimeException('View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean() ?: '';

        $layout = $data['layout'] ?? 'layouts/app';
        if ($layout === false) {
            return $content;
        }

        $layoutFile = base_path('app/Views/' . str_replace('.', '/', (string) $layout) . '.php');
        if (!is_file($layoutFile)) {
            throw new RuntimeException('Layout not found: ' . $layout);
        }

        ob_start();
        require $layoutFile;
        return ob_get_clean() ?: '';
    }
}

