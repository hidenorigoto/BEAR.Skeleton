<?php

/**
 * This file is part of the BEAR.Skeleton package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
class Installer
{
    public static function postInstall(Event $event = null)
    {
        $skeletonRoot = __DIR__;
        $splFile = new \SplFileInfo($skeletonRoot);
        $folderName = $splFile->getFilename();
        list($vendorName, $packageName) = explode('.', $folderName);
        $jobChmod = function (\SplFileInfo $file) {
            chmod($file, 0777);
        };
        $jobRename = function (\SplFileInfo $file) use ($vendorName, $packageName) {
            $fineName = $file->getFilename();
            if ($file->isDir() || strpos($fineName, '.') === 0 || !is_writable($file)) {
                return;
            }
            $contents = file_get_contents($file);
            $contents = str_replace('BEAR.Skeleton', "{$vendorName}.{$packageName}", $contents);
            $contents = str_replace('BEAR\Skeleton', "{$vendorName}\\{$packageName}", $contents);
            $contents = str_replace('{package_name}', strtolower("{$vendorName}/{$packageName}"), $contents);
            file_put_contents($file, $contents);
        };

        // chmod
        self::recursiveJob("{$skeletonRoot}/var/tmp", $jobChmod);

        // rename file contents
        self::recursiveJob("{$skeletonRoot}", $jobRename);
//        $jobRename(new \SplFileInfo("{$skeletonRoot}/build.xml"));
//        $jobRename(new \SplFileInfo("{$skeletonRoot}/phpcs.xml"));
//        $jobRename(new \SplFileInfo("{$skeletonRoot}/phpdox.xml.dist"));
//        $jobRename(new \SplFileInfo("{$skeletonRoot}/phpmd.xml"));
//        $jobRename(new \SplFileInfo("{$skeletonRoot}/phpunit.xml.dist"));

        // composer.json
        unlink("{$skeletonRoot}/composer.json");
        rename("{$skeletonRoot}/project.composer.json", "{$skeletonRoot}/composer.json");
        $composerJson = file_get_contents("{$skeletonRoot}/composer.json");
        $packageNameComposerJson = str_replace('bear/skeleton', strtolower("{$vendorName}/{$packageName}"), $composerJson);
        file_put_contents("{$skeletonRoot}/composer.json", $packageNameComposerJson);

        // delete self
        unlink(__FILE__);
    }

    /**
     * @param string $path
     * @param Callable $job
     *
     * @return void
     */
    private static function recursiveJob($path, $job)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file) {
            $job($file);
        }
    }
}

Installer::postInstall();
