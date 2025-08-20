<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Console;

if (!defined('STDIN')) {
    define('STDIN', fopen('php://stdin', 'r'));
}

use App\Utility\FileUtility;
use App\Utility\VideoMeeting\VideoMeetingFactory;
use Cake\Codeception\Console\Installer as CodeceptionInstaller;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Utility\Security;
use Composer\Script\Event;
use Exception;

/**
 * Provides installation hooks for when this application is installed through
 * composer. Customize this class to suit your needs.
 */
class Installer
{
    /**
     * An array of directories to be made writable
     */
    public const WRITABLE_DIRS = [
        'logs',
        'tmp',
    ];

    protected const ENV_NAMES = [
        'local',
        'development',
        'staging',
        'production',
    ];
    protected const ENV_DEFAULT = 'local';
    protected const WRITABLE_DIRECTORY_PERMISSION = 0777;
    protected const WRITABLE_FILE_PERMISSION = 0666;
    protected const SALT_KEYS = [
        '__SALT__',
        '__COOKIE_KEY__',
        '__VIDEO_MEETING_SALT__',
        '__APP_SETTING_SALT__',
        '__RECAPTCHA_SETTING_SALT__',
    ];
    protected const SALT_LENGTH = 64;

    /**
     * Does some routine installation tasks so people don't have to.
     *
     * @param \Composer\Script\Event $event The composer event object.
     * @throws \Exception Exception raised by validator.
     * @return void
     */
    public static function postInstall(Event $event)
    {
        $io = $event->getIO();

        $rootDir = dirname(dirname(__DIR__));

        if (!is_writable($rootDir)) {
            throw new Exception('Permission error.');
        }

        $envName = static::ENV_DEFAULT;
        $zoomClientId = '';
        $zoomClientSecret = '';
        $announceApiKey = '';
        $createLocalConfig = false;
        if ($io->isInteractive()) {
            $envName = $io->askAndValidate(
                'Env name [ ' . implode(' | ', static::ENV_NAMES) . ' ]: ',
                function ($arg) {
                    if (!in_array($arg, static::ENV_NAMES, true)) {
                        throw new Exception('Invalid env name.');
                    }

                    return $arg;
                },
                null,
                static::ENV_DEFAULT
            );

            $zoomClientId = $io->ask('Zoom Client ID: ');
            $zoomClientSecret = $io->ask('Zoom Client Secret: ');

            $announceApiKey = $io->ask('Announce API key: ');

            $createLocalConfig = $io->askConfirmation('Create local config? [y/n]: ', false);
        }

        static::createEnvConfig($rootDir, $io);

        static::setFolderPermissions($rootDir, $io);
        static::setSecuritySalt($rootDir, $io);

        static::updateEnvConfig($rootDir, $io, [
            '__ENV_NAME__' => $envName,
            '__ZOOM_CLIENT_ID__' => $zoomClientId,
            '__ZOOM_CLIENT_SECRET__' => $zoomClientSecret,
            '__ANNOUNCE_API_KEY__' => $announceApiKey,
        ]);

        if ($createLocalConfig) {
            static::createLocalConfig($rootDir, $io);
        }

        if (class_exists(CodeceptionInstaller::class)) {
            CodeceptionInstaller::customizeCodeceptionBinary($event);
        }
    }

    /**
     * Create config/env/env.php file if it does not exist.
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @return void
     */
    public static function createEnvConfig($dir, $io)
    {
        $envConfig = $dir . '/config/env/env.php';
        $envConfigTemplate = $dir . '/config/env/env_default.php';
        if (!file_exists($envConfig)) {
            copy($envConfigTemplate, $envConfig);
            $io->write('Created `config/env/env.php` file');
        }
    }

    /**
     * Update config/env/env.php file.
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @param array $replacement 置換文字
     * @return void
     */
    public static function updateEnvConfig($dir, $io, $replacement)
    {
        $configFile = $dir . '/config/env/env.php';
        $content = file_get_contents($configFile);
        if ($content === false) {
            throw new Exception('Unable to update env file.');
        }

        $config = require $configFile;
        if (!is_array($config) || !is_string(Hash::get($config, 'Env.videoMeeting.salt'))) {
            throw new Exception('Unable to update env file.');
        }
        Configure::write('Env.videoMeeting.salt', Hash::get($config, 'Env.videoMeeting.salt'));

        if ((string)Hash::get($replacement, '__ZOOM_CLIENT_ID__') !== '') {
            $replacement['__ZOOM_CLIENT_ID__'] = VideoMeetingFactory::encryptApiInfo(
                $replacement['__ZOOM_CLIENT_ID__']
            );
        }
        if ((string)Hash::get($replacement, '__ZOOM_CLIENT_SECRET__') !== '') {
            $replacement['__ZOOM_CLIENT_SECRET__'] = VideoMeetingFactory::encryptApiInfo(
                $replacement['__ZOOM_CLIENT_SECRET__']
            );
        }

        $result = file_put_contents(
            $configFile,
            str_replace(array_keys($replacement), array_values($replacement), $content)
        );
        if (!$result) {
            throw new Exception('Unable to update env file.');
        }

        $io->write('Updated `config/env/env.php` file');
    }

    /**
     * Create config/env/local/app_env.php file if it does not exist.
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @return void
     */
    public static function createLocalConfig($dir, $io)
    {
        $localConfig = $dir . '/config/env/local';
        $localConfigTemplate = $dir . '/config/env/local_default';
        if (!file_exists($localConfig)) {
            FileUtility::copyDirectory($localConfigTemplate, $localConfig);
            $io->write('Created `config/env/local/app_env.php` file');
        }
    }

    /**
     * Set globally writable permissions on the "tmp" and "logs" directory.
     *
     * This is not the most secure default, but it gets people up and running quickly.
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @return void
     */
    public static function setFolderPermissions($dir, $io)
    {
        foreach (static::WRITABLE_DIRS as $target) {
            FileUtility::changePermissionRecursive(
                $dir . '/' . $target,
                static::WRITABLE_DIRECTORY_PERMISSION,
                static::WRITABLE_FILE_PERMISSION
            );
        }
        $io->write('Permissions set.');
    }

    /**
     * Set the security.salt value in the application's config file.
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @return void
     */
    public static function setSecuritySalt($dir, $io)
    {
        static::setSecuritySaltInFile($dir, $io, 'env.php');
        $io->write('Updated salt value.');
    }

    /**
     * Set the security.salt value in a given file
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @param string $file A path to a file relative to the application's root
     * @return void
     */
    public static function setSecuritySaltInFile($dir, $io, $file)
    {
        $config = $dir . '/config/env/' . $file;
        $content = file_get_contents($config);
        if ($content === false) {
            throw new Exception('Unable to update salt value.');
        }

        foreach (static::SALT_KEYS as $saltKey) {
            $newKey = hash('sha256', Security::randomBytes(static::SALT_LENGTH));
            $content = str_replace($saltKey, $newKey, $content);
        }

        $result = file_put_contents($config, $content);
        if (!$result) {
            throw new Exception('Unable to update salt value.');
        }
    }
}
