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
use \OCP\IDb;

/**
 * Controller class for main page.
 */
class MusicController extends Controller {

    private $userId;
    private $db;

    public function __construct($appName, IRequest $request, $userId, IDb $db) {
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->db = $db;
    }

    /**
     * Get the music from the database
     * @NoAdminRequired
     * @return id
     */
    public function getMusic() {
        $tags = $this->params('tags');

        if ($tags !== null) {
            if ($this->_validStringNumber($tags)) {
                $aSongs = $this->loadSongWithTags($tags);
            } else {
                $result = [
                    'status' => 'error',
                    'data' => 'nodata'
                ];
                $response = new JSONResponse();
                $response->setData($result);
                return $response;
            }
        } else {
            $aSongs = $this->loadSongs();
        }

        if (is_array($aSongs)) {
            $result = [
                'status' => 'success',
                'data' => ['songs' => $aSongs]
            ];
        } else {
            $result = [
                'status' => 'error',
                'data' => 'nodata'
            ];
        }

        $response = new JSONResponse();
        $response->setData($result);
        return $response;


    }

    /**
     * Get the music from the database
     * @NoAdminRequired
     * @return id
     */
    public function getArtist() {
        $artistId = $this->params('artistId');

        if ($artistId !== null) {
            if (ctype_digit($artistId)) {
                $aSongs = $this->loadSongs($artistId);
            } else {
                $result = [
                    'status' => 'error',
                    'data' => 'nodata'
                ];
                $response = new JSONResponse();
                $response->setData($result);
                return $response;
            }
        } else {
            $aSongs = $this->loadSongs();
        }

        if (is_array($aSongs)) {
            $result = [
                'status' => 'success',
                'data' => ['songs' => $aSongs, 'artist' => $this->_getArtistFromId($artistId)]
            ];
        } else {
            $result = [
                'status' => 'error',
                'data' => 'nodata'
            ];
        }

        $response = new JSONResponse();
        $response->setData($result);
        return $response;

    }

    /**
     * Get the music from the database
     * @NoAdminRequired
     * @return id
     */
    public function getArtistList() {

        $artist = $this->loadArtists();

        if (is_array($artist)) {
            $result = [
                'status' => 'success',
                'data' => ['artists' => $artist]
            ];
        } else {
            $result = [
                'status' => 'error',
                'data' => 'nodata'
            ];
        }

        $response = new JSONResponse();
        $response->setData($result);
        return $response;

    }

    /**
     * return the music from the database
     * @NoAdminRequired
     * @return songArray
     */
    public function addTimePlayed() {
        $fileid = $this->params('fileid');
        $SQLselect = "SELECT `time_played` FROM `*PREFIX*hyperionmusic_tracks` WHERE `user_id` = ? AND `file_id` = ?";
        $stmt = $this->db->prepareQuery($SQLselect);
        $result = $stmt->execute(array($this->userId, $fileid));

        $SQL = "UPDATE  `*PREFIX*hyperionmusic_tracks` SET `time_played` = ? WHERE `user_id` = ? AND `file_id` = ?";
        $stmt = $this->db->prepareQuery($SQL);
        $result = $stmt->execute(array($result->fetchRow()['time_played'] + 1, $this->userId, $fileid));

        return $result;

    }

    /**
     * return the music from the database
     * @NoAdminRequired
     * @return songArray
     */
    public function loadSongs($artistId = null) {
        if($artistId !== null) {
            $where = "WHERE t.user_id = ? AND artist_id = '" . $artistId . "' ";
        } else {
            $where = "WHERE t.user_id = ?";
        }
        $SQL = "SELECT t.id,t.title,a.name as artist,t.album,t.genre,t.bitrate,t.year,t.play_time,t.path,t.time_played,t.date_added,t.file_id FROM *PREFIX*hyperionmusic_tracks as t
                inner join *PREFIX*hyperionmusic_artists as a on t.artist_id = a.id "
            . $where .
                " ORDER BY t.title ASC";

        $stmt = $this->db->prepareQuery($SQL);
        $result = $stmt->execute(array($this->userId));

        $aSongs = '';
        while ($row = $result->fetchRow()) {
            try {
                $path = \OC\Files\Filesystem::getPath($row['file_id']);
                if (\OC\Files\Filesystem::file_exists($path)) {
                    $row['path'] = $path;
                    $aSongs[] = $row;
                }
            }
            catch (\OCP\Files\NotFoundException $ex) {
                $this->_deleteFromDB($row['id'], $row['path'], $row['file_id']);
            }

        }
        if (is_array($aSongs)) {
            return $aSongs;
        } else {
            return false;
        }
    }

    /**
     * return the music from the database
     * @NoAdminRequired
     * @return songArray
     */
    public function loadArtists() {
        $SQL = "SELECT * from *PREFIX*hyperionmusic_artists where `user_id` = ? ORDER BY `name` ASC";

        $stmt = $this->db->prepareQuery($SQL);
        $result = $stmt->execute(array($this->userId));

        $artist = '';
        while ($row = $result->fetchRow()) {
            $artist[] = $row;
        }
        if (is_array($artist)) {
            return $artist;
        } else {
            return false;
        }
    }

    /**
     * Return the artist name from the artist_id
     * @NoAdminRequired
     * @param string $artistId
     *
     *
     * @return String artist
     */
    private function _getArtistFromId($artistId) {
        $stmtCount = $this->db->prepareQuery('SELECT `name` FROM `*PREFIX*hyperionmusic_artists` WHERE `user_id` = ? AND `id` = ?');
        $result = $stmtCount->execute(array($this->userId,$artistId));
        $row = $result->fetchRow();
        return $row['name'];
    }

    /**
     * return the music from the database
     * @NoAdminRequired
     * @return songArray
     */
    public function loadSongWithTags($tags) {
        $songId = $this->returnAllSongsWithTags($tags);
        array_walk($songId, 'intval');
        $ids = implode(', ', $songId);

        $SQL = "SELECT t.id,t.title,a.name as artist,t.album,t.genre,t.bitrate,t.year,t.play_time,t.path,t.time_played,t.date_added,t.file_id FROM *PREFIX*hyperionmusic_tracks as t
                inner join *PREFIX*hyperionmusic_artists as a on t.artist_id = a.id
                WHERE t.user_id = ?
                AND file_id IN (" . $ids . ")
                ORDER BY t.title ASC";

        $stmt = $this->db->prepareQuery($SQL);
        $result = $stmt->execute(array($this->userId));


        $aSongs = '';
        while ($row = $result->fetchRow()) {

            $path = \OC\Files\Filesystem::getPath($row['file_id']);

            if (\OC\Files\Filesystem::file_exists($path)) {
                $row['path'] = $path;
                $aSongs[] = $row;
            } else {
                $this->_deleteFromDB($row['id'], $row['path'], $row['file_id']);
            }

        }

        if (is_array($aSongs)) {
            return $aSongs;
        } else {
            return false;
        }
    }

    /**
     * Add track to db if not exist
     * @NoAdminRequired
     * @param array $aTrack
     *
     *
     * @return id
     */
    public function returnAllSongsWithTags($tags) {
        $stmtCount = $this->db->prepareQuery('SELECT `objectid` FROM `*PREFIX*systemtag_object_mapping` WHERE `systemtagid` IN (' . $tags . ')');
        $resultCount = $stmtCount->execute();
        $tag = [];
        while ($row = $resultCount->fetchRow()) {
            $tag[] = $row["objectid"];
        }
        return $tag;
    }

    /**
     * Add track to db if not exist
     * @NoAdminRequired
     */
    private function _deleteFromDB($Id, $path, $fileId) {
        $stmt = $this->db->prepareQuery('DELETE FROM `*PREFIX*hyperionmusic_tracks` WHERE `user_id` = ? AND `id` = ? AND `path` = ? AND `file_id` = ?');
        $result = $stmt->execute(array($this->userId, $Id, $path, $fileId));
    }

    /**
     * Add track to db if not exist
     * @NoAdminRequired
     */
    private function _validStringNumber($string) {
        foreach(explode(',', $string) as $num) {
            if(!is_numeric($num)) return false;
        }
        return true;
    }
}