<?php
/**
 * nextcloud - hyperionmusic
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Antopower <antomatic10@gmail.com>
 */

namespace OCA\HyperionMusic\Controller;

use \OCP\AppFramework\Controller;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ISystemTag;
use OCP\SystemTag\TagNotFoundException;
use OCP\IGroupManager;
use OCP\IUserSession;


/**
 * Controller class for main page.
 */
class TagController extends Controller {

    private $userId;
    private $db;

    public function __construct($appName, $request, $userId, $db) {
        parent::__construct($appName, $request);
        $this -> userId = $userId;
        $this->db = $db;
    }

    /**
     * Add track to db if not exist
     * @NoAdminRequired
     * @return tags list
     */
    public function returnAllTags(){
        $stmtCount = \OCP\DB::prepare( 'SELECT `name`,`id` FROM `*PREFIX*systemtag` WHERE `visibility` = 1' );
        $resultCount = $stmtCount->execute();
        $tag = [];
        while($row = $resultCount->fetchRow()) {
            $tag[] = ['id' => $row["id"], 'name' => $row["name"]];
        }

        $response =  ['success' => true, 'data' => $tag];
        return $response;
    }
}