<?php
/**
 * This file is part of the Font Awesome Bundle for Symfony.
 *
 * @copyright   Copyright (C) 2014 Hassan Amouhzi. All rights reserved.
 * @license     The MIT License (MIT), see LICENSE.md
 */

namespace Anezi\Bundle\FontAwesomeBundle\Composer;

use Composer\Script\Event;

use Composer\Script\CommandEvent;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as SensioScriptHandler;

class ScriptHandler extends SensioScriptHandler
{
    public static function installAssets(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $consoleDir = self::getConsoleDir($event, 'install assets');

        if (null === $consoleDir) {
            return;
        }

        if(!isset($options['fontawesome-version'])) {
            $event->getIO()->write(
                '<error>Option "fontawesome-version" for Font Awesome Bundle is missing!</error>'
            );
            return;
        }

        $version = $options['fontawesome-version'];

        $webDir = $options['symfony-web-dir'];

        if (!self::hasDirectory($event, 'symfony-web-dir', $webDir, 'install assets')) {
            return;
        }

        static::executeCommand(
            $event,
            $consoleDir,
            'fontawesome:assets:install -z ' . escapeshellarg($version) . ' ' . escapeshellarg($webDir)
        );
    }
}
