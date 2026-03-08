<?php declare(strict_types=1);

require_once 'PathGuard.php';

class FileLister extends PathGuard
{
    public function list(string $relDir = ''): array
    {
        $dir = $this->absExisting($relDir);

        if (!is_dir($dir)) {
            throw new RuntimeException('Not a directory');
        }

        $items = scandir($dir);
        if ($items === false) {
            throw new RuntimeException('Cannot read directory');
        }

        $out = [];

        foreach ($items as $it) {
            if ($it === '.' || $it === '..') {
                continue;
            }

            $p = $dir . DIRECTORY_SEPARATOR . $it;
            $out[] = [
                'name' => $it,
                'isDir' => is_dir($p),
                'size' => is_file($p) ? (int) filesize($p) : 0,
                'mtime' => (int) (filemtime($p) ?: 0),
            ];
        }

        usort($out, function ($a, $b) {
            if ($a['isDir'] !== $b['isDir']) {
                return $a['isDir'] ? -1 : 1;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        return $out;
    }
}