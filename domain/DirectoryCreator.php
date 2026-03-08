<?php declare(strict_types=1);

require_once 'FileLister.php';

class DirectoryCreator extends FileLister
{
    public function makeDir(string $parentRel, string $name): void
    {
        $name = $this->safeName($name);
        $parentAbs = $this->absExisting($parentRel);

        if (!is_dir($parentAbs)) {
            throw new RuntimeException('Parent not dir');
        }

        $target = $parentAbs . DIRECTORY_SEPARATOR . $name;

        if (is_dir($target)) {
            return;
        }

        if (!mkdir($target, 0775, true) && !is_dir($target)) {
            throw new RuntimeException('Cannot create folder');
        }
    }
}