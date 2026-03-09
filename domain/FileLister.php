<?php declare(strict_types=1);

require_once 'PathGuard.php';

class FileLister
{
    private PathGuard $guard;

    public function __construct(PathGuard $guard)
    {
        $this->guard = $guard;
    }

    public function list(string $relDir = ''): array
    {
        $dir = $this->guard->absExisting($relDir);
        $items = scandir($dir);
        $out = [];

        foreach ($items as $it) {
            if ($it === '.' || $it === '..') continue;
            $p = $dir . DIRECTORY_SEPARATOR . $it;
            $out[] = [
                'name' => $it,
                'isDir' => is_dir($p),
                'size' => is_file($p) ? filesize($p) : 0,
                'mtime' => filemtime($p) ?: 0,
            ];
        }

        usort($out, fn($a, $b) => ($a['isDir'] === $b['isDir']) 
            ? strcasecmp($a['name'], $b['name']) 
            : ($a['isDir'] ? -1 : 1));

        return $out;
    }
}