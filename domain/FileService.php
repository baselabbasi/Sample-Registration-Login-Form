<?php declare(strict_types=1);

require_once 'PathGuard.php';

class FileService
{
    private PathGuard $guard;

    public function __construct(PathGuard $guard)
    {
        $this->guard = $guard;
    }

    public function upload(string $relDir, array $file): void
    {
        if (!isset($file['tmp_name'], $file['name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Invalid upload');
        }

        $dirAbs = $this->guard->absExisting($relDir);
        $name = $this->guard->safeName((string) $file['name']);
        $dest = $dirAbs . DIRECTORY_SEPARATOR . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Upload failed');
        }
    }

    public function makeDir(string $parentRel, string $name): void
    {
        $parentAbs = $this->guard->absExisting($parentRel);
        $name = $this->guard->safeName($name);
        $target = $parentAbs . DIRECTORY_SEPARATOR . $name;

        if (!is_dir($target)) {
            if (!mkdir($target, 0775, true)) {
                throw new RuntimeException('Cannot create folder');
            }
        }
    }

    public function delete(string $rel): void
    {
        $abs = $this->guard->absExisting($rel);

        if (is_dir($abs)) {
            $this->deleteDirRecursive($abs);
        } elseif (!unlink($abs)) {
            throw new RuntimeException('Cannot delete file');
        }
    }

    private function deleteDirRecursive(string $dir): void
    {
        $items = scandir($dir);
        foreach ($items as $it) {
            if ($it === '.' || $it === '..') continue;
            $p = $dir . DIRECTORY_SEPARATOR . $it;
            is_dir($p) ? $this->deleteDirRecursive($p) : unlink($p);
        }
        rmdir($dir);
    }
}