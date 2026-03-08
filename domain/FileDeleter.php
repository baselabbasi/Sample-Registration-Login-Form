<?php declare(strict_types=1);

require_once 'DirectoryCreator.php';

class FileDeleter extends DirectoryCreator
{
    public function delete(string $rel): void
    {
        $abs = $this->absExisting($rel);

        if (is_dir($abs)) {
            $this->deleteDirRecursive($abs);
            return;
        }

        if (!unlink($abs)) {
            throw new RuntimeException('Cannot delete file');
        }
    }

    private function deleteDirRecursive(string $dir): void
    {
        $items = scandir($dir);

        if ($items === false) {
            throw new RuntimeException('Cannot read directory');
        }

        foreach ($items as $it) {
            if ($it === '.' || $it === '..') {
                continue;
            }

            $p = $dir . DIRECTORY_SEPARATOR . $it;

            if (is_dir($p)) {
                $this->deleteDirRecursive($p);
            } elseif (!unlink($p)) {
                throw new RuntimeException('Cannot delete file');
            }
        }

        if (!rmdir($dir)) {
            throw new RuntimeException('Cannot delete folder');
        }
    }
}