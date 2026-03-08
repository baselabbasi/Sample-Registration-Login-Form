<?php declare(strict_types=1);

require_once 'helper.php';

class UserDirectoryCore
{
    protected string $root;

    public function __construct(string $usersRoot, string $username)
    {
        $username = trim($username);
        if ($username === '') {
            throw new RuntimeException('Missing username');
        }

        $usersRoot = rtrim($usersRoot, DIRECTORY_SEPARATOR);
        if (!is_dir($usersRoot)) {
            mkdir($usersRoot, 0775, true);
        }

        $userDir = $usersRoot . DIRECTORY_SEPARATOR . $username;

        if (!is_dir($userDir)) {
            if (!mkdir($userDir, 0775, true) && !is_dir($userDir)) {
                throw new RuntimeException('Cannot create user directory');
            }
        }

        $real = realpath($userDir);
        if ($real === false) {
            throw new RuntimeException('Cannot resolve user directory');
        }

        $this->root = rtrim($real, DIRECTORY_SEPARATOR);
    }

    public function root(): string
    {
        return $this->root;
    }
}