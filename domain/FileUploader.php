<?php declare(strict_types=1);

require_once 'FileDeleter.php';

class FileUploader extends FileDeleter
{
    public function upload(string $relDir, array $file): void
    {
        if (!isset($file['tmp_name'], $file['name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Invalid upload');
        }

        $dirAbs = $this->absExisting($relDir);

        if (!is_dir($dirAbs)) {
            throw new RuntimeException('Upload target not dir');
        }

        $name = $this->safeName((string) $file['name']);
        $dest = $dirAbs . DIRECTORY_SEPARATOR . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Upload failed');
        }
    }
}