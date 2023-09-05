<?php

namespace JaxWilko\ComposerWinterPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    public const PRE_AUTOLOAD_DUMP = 'pre-autoload-dump';
    public const POST_AUTOLOAD_DUMP = 'post-autoload-dump';

    protected Composer $composer;
    protected IOInterface $io;

    protected array $winterTypes = ['winter-plugin', 'winter-theme', 'winter-module'];

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    // COMPOSER_DEBUG_EVENTS=1
    public static function getSubscribedEvents()
    {
        return [
            static::POST_AUTOLOAD_DUMP => ['postDumpReport'],
        ];
    }

    public function postDumpReport(Event $event)
    {
        $packages = [];
        $basePath = dirname($this->composer->getConfig()->get('vendor-dir'));

        foreach ($this->composer->getLocker()->getLockedRepository()->getPackages() as $package) {
            if (in_array($package->getType(), $this->winterTypes) && $package instanceof CompletePackage) {
                $path = $basePath . '/' . $this->composer->getInstallationManager()
                        ->getInstaller($package->getType())
                        ->getInstallPath($package);

                $packages[] = [
                    'name' => $package->getName(),
                    'type' => $package->getType(),
                    'version' => $package->getVersion(),
                    'path' => rtrim($path, '/'),
                ];
            }
        }

        file_put_contents($basePath . '/storage/framework/packages.json', json_encode($packages, JSON_PRETTY_PRINT));
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // TODO: Implement deactivate() method.
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO: Implement uninstall() method.
    }
}
