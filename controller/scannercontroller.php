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
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\IRequest;
use \OC\Files\View;
use \OCP\IDb;

/**
 * Controller class for main page.
 */
class ScannerController extends Controller {

    private $userId;
    private $path;
    private $abscount = 0;
    private $progress;
    private $progresskey;
    private $currentSong;
    private $iDublicate = 0;
    private $iAlbumCount = 0;
    private $numOfSongs;
    private $db;

    public function __construct($appName, IRequest $request, $userId,IDb $db) {
        parent::__construct($appName, $request);
        $this -> userId = $userId;
        $this->db = $db;
    }

    /**
     * Simply method that posts back the payload of the request
     * @NoAdminRequired
     */
    public function scanDriveMusic() {
        if(!class_exists('getid3_exception')) {
            require_once __DIR__ . '/../3rdparty/getID3/getid3/getid3.php';
        }

        $userView =  new View('/' . $this -> userId . '/files');
        $audios = $userView->searchByMime('audio/mpeg');
        $music = [];
        foreach($audios as $audio) {

            if($this->checkIfTrackDbExists($audio['fileid']) === false){
                $getID3 = new \getID3;
                $ThisFileInfo = $getID3->analyze($userView->getLocalFile($audio['path']));
                \getid3_lib::CopyTagsToComments($ThisFileInfo);

                /* FILENAME */
                $name = $audio['name'];

                /* TITLE */
                $title = $audio['name'];
                if(isset($ThisFileInfo['comments']['title'][0])) {
                    $title = $ThisFileInfo['comments']['title'][0];
                }

                /* ARTIST */
                $artist = 'Various Artists';
                if(isset($ThisFileInfo['comments']['artist'][0])) {
                    $artist = $ThisFileInfo['comments']['artist'][0];
                }
                $artist_id = $this->writeArtistToDB($artist);

                /* ALBUM */
                $album = 'Various';
                if(isset($ThisFileInfo['comments']['album'][0])) {
                    $album = $ThisFileInfo['comments']['album'][0];
                }

                /* GENRE */
                $genre = '';
                if(isset($ThisFileInfo['comments']['genre'][0])) {
                    $genre = $ThisFileInfo['comments']['genre'][0];
                }

                /* BITRATE */
                $bitrate = 0;
                if(isset($ThisFileInfo['bitrate'])) {
                    $bitrate = $ThisFileInfo['bitrate'];
                }

                /* YEAR */
                $year = '';
                if(isset($ThisFileInfo['comments']['year'][0])) {
                    $year = $ThisFileInfo['comments']['year'][0];
                }

                /* PLAYTIME */
                $playTimeString = $ThisFileInfo['playtime_string'];
                if($playTimeString == null) {
                    $playTimeString = '';
                }

                /* PATH */
                $path = $audio['path'];

                /* TIME PLAYED */
                $timePlayed = 0;

                /* DATE ADDED */
                $dateAdded = time();

                /* FILE ID */
                $fileId = $audio['fileid'];

                $music = [
                    'user_id' => $this->userId,
                    'file' => $name,
                    'title' => $title,
                    'artist_id' => $artist_id,
                    'album' => $album,
                    'genre' => $genre,
                    'bitrate' => $bitrate,
                    'year' => $year,
                    'play_time' => $playTimeString,
                    'path' => $path,
                    'time_played' => $timePlayed,
                    'date_added' => $dateAdded,
                    'file_id' => $fileId,
                ];

                if($rowId = $this->writeTrackToDB($music)) {
                    $this->writeTrackArtistToDB($rowId, $artist_id);
                }


            }
        }
        $response =  ['success' => true];
        return $response;
    }

    /**
     * Add track to db if not exist
     *
     *@param array $aTrack
     *@NoAdminRequired
     *
     * @return id
     */
    private function writeTrackToDB($aTrack){

        $SQL='SELECT id FROM *PREFIX*hyperionmusic_tracks WHERE `user_id`= ? AND `title`= ? AND `artist_id`= ? AND `album`= ? AND `play_time`= ? AND `bitrate`= ?';
        $stmt = $this->db->prepareQuery($SQL);
        $result = $stmt->execute(array($this->userId, $aTrack['title'],$aTrack['artist_id'],$aTrack['album'],$aTrack['play_time'],$aTrack['bitrate']));

        $row = $result->fetchRow();
        if(isset($row['id'])){
            $this->iDublicate++;
            return false;
        }else{
            $stmt = $this->db->prepareQuery( 'INSERT INTO `*PREFIX*hyperionmusic_tracks` (`user_id`,`file`,`title`,`artist_id`,`album`,`genre`,`bitrate`,`year`,`play_time`,`path`,`time_played`,`date_added`,`file_id`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)' );
            $result = $stmt->execute(array($this->userId, $aTrack['file'], $aTrack['title'], $aTrack['artist_id'], $aTrack['album'], $aTrack['genre'], $aTrack['bitrate'], $aTrack['year'], $aTrack['play_time'], $aTrack['path'], $aTrack['time_played'], $aTrack['date_added'], $aTrack['file_id']));
            $insertid = \OCP\DB::insertid('*PREFIX*hyperionmusic_tracks');
            return $insertid;
        }

    }

    /**
     * Add Artist to db if not exist
     *
     *@param array $artist
     *@NoAdminRequired
     *
     * @return id
     */
    private function writeArtistToDB($artist){

        $SQL='SELECT id FROM *PREFIX*hyperionmusic_artists WHERE `user_id`= ? AND `name`= ?';
        $stmt = $this->db->prepareQuery($SQL);
        $result = $stmt->execute(array($this->userId, $artist));

        $row = $result->fetchRow();
        if(isset($row['id'])){
            $insertid = $row['id'];
        }else{
            $stmt = $this->db->prepareQuery( 'INSERT INTO `*PREFIX*hyperionmusic_artists` (`user_id`,`name`) VALUES(?,?)' );
            $result = $stmt->execute(array($this->userId, $artist));
            $insertid = \OCP\DB::insertid('*PREFIX*hyperionmusic_artist');
        }
        return $insertid;

    }

    /**
     * Add Artist to db if not exist
     *
     * @param $trackId
     * @param $artistId
     * @return id
     * @internal param array $artist
     * @NoAdminRequired
     *
     */
    private function writeTrackArtistToDB($trackId, $artistId){

        $SQL='SELECT track_id FROM *PREFIX*hyperionmusic_track_artist WHERE `track_id`= ? AND `artist_id`= ?';
        $stmt = $this->db->prepareQuery($SQL);
        $result = $stmt->execute(array($trackId, $artistId));

        $row = $result->fetchRow();
        if(isset($row['track_id'])){
            $insertid = $row['track_id'];
        }else{
            $stmt = $this->db->prepareQuery( 'INSERT INTO `*PREFIX*hyperionmusic_track_artist` (`track_id`,`artist_id`) VALUES(?,?)' );
            $result = $stmt->execute(array($trackId, $artistId));
            $insertid = 0;
        }
        return $insertid;

    }

    /**
     * Delete all the music information for a user
     *
     *@NoAdminRequired
     *
     * @return id
     */
    public function deleteMusicDataUser(){
        $stmt = $this->db->prepareQuery('DELETE FROM `*PREFIX*hyperionmusic_tracks` WHERE `user_id` = ?');
        $result = $stmt->execute(array($this->userId));
        if ($result > 0) {
            return $response =  ['success' => true, 'message' => 'All data has been deleted'];
        } else {
            $response =  ['success' => true, 'message' => 'No row deleted'];
            return $response;
        }

    }

    /**
     * Simply method that posts back the payload of the request
     * @NoAdminRequired
     */
    private function checkIfTrackDbExists($fileid){
        $stmtCount = $this->db->prepareQuery( 'SELECT  COUNT(`id`)  AS COUNTID FROM `*PREFIX*hyperionmusic_tracks` WHERE `user_id` = ? AND `file_id` = ? ' );
        $resultCount = $stmtCount->execute(array($this->userId, $fileid));
        $row = $resultCount->fetchRow();
        if(isset($row['COUNTID']) && $row['COUNTID'] > 0){
            return true;
        }else{
            return false;
        }
    }
}