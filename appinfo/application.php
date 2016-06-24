<?php
/**
 * nextcloud - hyperionmusic
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Antopower <antomatic10@gmail.com>
 */

namespace OCA\HyperionMusic\AppInfo;

use \OCP\AppFramework\App;
use OCP\IContainer;
use OCP\AppFramework\IAppContainer;

use \OCA\HyperionMusic\Controller\PageController;
use \OCA\HyperionMusic\Controller\ScannerController;
use \OCA\HyperionMusic\Controller\MusicController;
use \OCA\HyperionMusic\Controller\TagController;

class Application extends App {

    public function __construct (array $urlParams=array()) {

        parent::__construct('hyperionmusic', $urlParams);
        $container = $this->getContainer();


        $container->registerService('PageController', function(IContainer $c) {
            return new PageController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId')
            );
        });
        $container->registerService('ScannerController', function(IContainer $c) {
            return new ScannerController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('ServerContainer')->getDb()
            );
        });
        $container->registerService('MusicController', function(IContainer $c) {
            return new MusicController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('ServerContainer')->getDb()
            );
        });
        $container->registerService('TagController', function(IContainer $c) {
            return new TagController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('ServerContainer')->getDb()
            );
        });


        /**
         * Core
         */

        $container->registerService('URLGenerator', function(IContainer $c) {
            /** @var \OC\Server $server */
            $server = $c->query('ServerContainer');
            return $server->getURLGenerator();
        });

        $container -> registerService('UserId', function(IContainer $c) {
            return \OC::$server->getUserSession()->getUser()->getUID();
        });


    }


}

