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

  
    public function absExisting(string $rel): string
    {
        $rel = ($rel === '' || $rel === '.') ? '' : $this->normalizeRel($rel);
        $abs = $this->root . ($rel !== '' ? DIRECTORY_SEPARATOR . $rel : '');
        
        $real = realpath($abs);
        if ($real === false) {
            throw new RuntimeException('Not found');
        }

        if (strpos(rtrim($real, DIRECTORY_SEPARATOR), $this->root) !== 0) {
            throw new RuntimeException('Forbidden path');
        }

        return $real;
    }
}
