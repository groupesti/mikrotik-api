<?php

declare(strict_types=1);

namespace MikroTik\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class MakeUiScaffoldCommand extends Command
{
    protected $signature = 'mikrotik:ui
        {--framework=tailwind : tailwind|bootstrap}
        {--stack=inertia-vue3 : only inertia-vue3 for now}
        {--force : overwrite files}';

    protected $description = 'Publie des stubs UI simples (Inertia+Vue3) pour Tailwind 4.* ou Bootstrap 5.3.8.';

    public function handle(Filesystem $fs): int
    {
        $framework = strtolower((string) $this->option('framework'));
        $stack = strtolower((string) $this->option('stack'));
        $force = (bool) $this->option('force');

        if (!in_array($framework, ['tailwind', 'bootstrap'], true)) {
            $this->error('framework invalide (tailwind|bootstrap)');
            return self::FAILURE;
        }
        if ($stack !== 'inertia-vue3') {
            $this->error('stack invalide (inertia-vue3)');
            return self::FAILURE;
        }

        $src = __DIR__ . '/../../stubs/ui/' . $stack . '/' . $framework;
        if (!is_dir($src)) {
            $this->error('Source stubs introuvable: ' . $src);
            return self::FAILURE;
        }

        $dstPages = base_path('resources/js/Pages/MikroTik');
        $dstComponents = base_path('resources/js/Components/MikroTik');

        $this->copyDir($fs, $src . '/pages', $dstPages, $force);
        $this->copyDir($fs, $src . '/components', $dstComponents, $force);

        // publish demo route file (opt-in)
        $routeSrc = __DIR__ . '/../../stubs/ui/laravel/routes/mikrotik.php';
        $routeDst = base_path('routes/mikrotik.php');

        if ($fs->exists($routeDst) && !$force) {
            $this->warn('Route existe déjà: routes/mikrotik.php (utilise --force pour écraser)');
        } else {
            $fs->ensureDirectoryExists(dirname($routeDst));
            $fs->copy($routeSrc, $routeDst);
            $this->info('Route publiée: routes/mikrotik.php');
        }

        $this->line('À faire dans routes/web.php:');
        $this->line("  require __DIR__ . '/mikrotik.php';");

        return self::SUCCESS;
    }

    private function copyDir(Filesystem $fs, string $src, string $dst, bool $force): void
    {
        if (!is_dir($src)) return;

        $fs->ensureDirectoryExists($dst);

        foreach ($fs->allFiles($src) as $file) {
            $rel = str_replace($src . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $target = $dst . DIRECTORY_SEPARATOR . $rel;

            $fs->ensureDirectoryExists(dirname($target));

            if ($fs->exists($target) && !$force) {
                $this->warn('Existe: ' . $target);
                continue;
            }

            $fs->copy($file->getPathname(), $target);
            $this->info('Copié: ' . $target);
        }
    }
}
