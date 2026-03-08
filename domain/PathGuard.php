<?php declare(strict_types=1);

require_once 'UserDirectoryCore.php';

class PathGuard extends UserDirectoryCore
{
    public function normalizeRel(string $rel): string
    {
        $rel = str_replace('\\', '/', trim($rel));
        $rel = ltrim($rel, '/');
        $rel = preg_replace('~/+~', '/', $rel) ?? '';

        if ($rel === '') {
            return '';
        }

        if (strpos($rel, "\0") !== false) {
            throw new RuntimeException('Bad path');
        }

        if (preg_match('~(^|/)\.\.(/|$)~', $rel)) {
            throw new RuntimeException('Blocked traversal');
        }

        return $rel;
    }

    public function safeName(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            throw new RuntimeException('Name required');
        }

        if (preg_match('/[\/\\\\]/', $name)) {
            throw new RuntimeException('Invalid name');
        }

        if (strpos($name, '..') !== false) {
            throw new RuntimeException('Invalid name');
        }

        return $name;
    }

    protected function join(string $rel): string
    {
        $rel = $this->normalizeRel($rel);
        return $this->root . ($rel !== '' ? DIRECTORY_SEPARATOR . $rel : '');
    }

    protected function assertInsideRoot(string $abs): void
    {
        $abs = rtrim($abs, DIRECTORY_SEPARATOR);

        if (strpos($abs, $this->root) !== 0) {
            throw new RuntimeException('Forbidden path');
        }
    }

    public function absExisting(string $rel): string
    {
        $abs = $this->join($rel);
        $real = realpath($abs);

        if ($real === false) {
            throw new RuntimeException('Not found');
        }

        $real = rtrim($real, DIRECTORY_SEPARATOR);
        $this->assertInsideRoot($real);

        return $real;
    }
}