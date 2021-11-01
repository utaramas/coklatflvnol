<?php

declare(strict_types=1);

namespace Staatic\WordPress\Service;

final class Filesystem
{
    public function isDirectory(string $directory) : bool
    {
        return \is_dir($directory);
    }

    /**
     * @return void
     */
    public function ensureDirectoryExists(string $directory)
    {
        if (\is_dir($directory)) {
            return;
        }
        try {
            \mkdir($directory, 0777, \true);
        } catch (\Error $error) {
            throw new \RuntimeException(\sprintf(
                'Unable to create directory "%s": %s',
                $directory,
                $error->getMessage()
            ));
        }
    }

    /**
     * @return void
     */
    public function clearDirectory(string $directory)
    {
        if (!\is_dir($directory)) {
            throw new \InvalidArgumentException(\sprintf('Directory does not exist in %s', $directory));
        }
        $deleteIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
            $directory,
            \FilesystemIterator::SKIP_DOTS
        ), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($deleteIterator as $file) {
            $file = (string) $file;
            if (\is_dir($file)) {
                $this->removeDirectory($file);
            } elseif (\is_file($file)) {
                $this->removeFile($file);
            }
        }
    }

    /**
     * @return void
     */
    public function removeDirectory(string $path)
    {
        try {
            \rmdir($path);
        } catch (\Error $error) {
            throw new \RuntimeException(\sprintf(
                'Directory could not be removed in %s: %s',
                $path,
                $error->getMessage()
            ));
        }
    }

    /**
     * @return void
     */
    public function removeFile(string $path)
    {
        try {
            \unlink($path);
        } catch (\Error $error) {
            throw new \RuntimeException(\sprintf('File could not be removed in %s: %s', $path, $error->getMessage()));
        }
    }

    // e.g. "/wp-content/uploads/sites/3/"
    public function getUploadsPath() : string
    {
        return \str_replace(untrailingslashit(ABSPATH), '', $this->getUploadsDirectory());
    }

    // e.g. "/home/wordpress/domains/wordpress/public_html/wp-content/uploads/sites/3/"
    public function getUploadsDirectory() : string
    {
        $uploadsDirectory = wp_upload_dir(null, \false);
        $uploadsDirectory = $uploadsDirectory['basedir'];
        $realUploadsDirectory = \realpath($uploadsDirectory);
        return trailingslashit($realUploadsDirectory ?: $uploadsDirectory);
    }
}
