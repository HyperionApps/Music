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

\OC::$server->getNavigationManager()->add(function () {
    $urlGenerator = \OC::$server->getURLGenerator();
    $l = \OC::$server->getL10N('hyperionmusic');
    return [
        'id' => 'hyperionmusic',
        'order' => 10,
        'href' => $urlGenerator->linkToRoute('hyperionmusic.page.index'),
        'icon' => $urlGenerator->imagePath('hyperionmusic', 'app.svg'),
        'name' => $l->t('Hyperion Music'),
    ];
});