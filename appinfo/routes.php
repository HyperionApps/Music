<?php
/**
 * nextcloud - hyperionmusic
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Antopower <antomatic10@gmail.com>
 * @author MrEvertide <cedric.nolin.cde@gmail.com>
 */

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\HyperionMusic\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
namespace OCA\HyperionMusic\AppInfo;

$application  = new Application();
$application->registerRoutes(
    $this, [
        'routes' => [
            ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
            ['name' => 'scanner#scandrivemusic', 'url' => '/scandrivemusic', 'verb' => 'POST'],
            ['name' => 'scanner#deletemusicdatauser', 'url' => '/deletemusicdatauser', 'verb' => 'POST'],
            ['name' => 'music#getmusic', 'url' => '/getmusic', 'verb' => 'POST'],
            ['name' => 'music#getartist', 'url' => '/getartist', 'verb' => 'POST'],
            ['name' => 'music#getartistlist', 'url' => '/getartistlist', 'verb' => 'POST'],
            ['name' => 'music#addtimeplayed', 'url' => '/addtimeplayed', 'verb' => 'POST'],
            ['name' => 'tag#returnAllTags', 'url' => '/returnalltags', 'verb' => 'POST']
        ]
    ]
);
