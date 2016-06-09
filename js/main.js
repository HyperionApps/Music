/**
 * nextcloud - hyperionmusic
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Antopower <antomatic10@gmail.com>
 */

(function ($, OC) {

    $(document).ready(function () {

        // Play/Pause button event
        $('.player-button.play-pause').click(function(){
            player.playPause();
        });

        // Play/Pause button event
        $('.player-button.backward').click(function(){
            player.previousSong();
        });

        // Play/Pause button event
        $('.player-button.forward').click(function(){
            player.nextSong();
        });

        // Repeat button event
        $('.player-button.repeat').click(function(){
            player.repeatSong();
        });

        // Shuffle button event
        $('.player-button.shuffle').click(function(){
            player.shuffleSong();
        });

        // Mute button event
        $('.volume-control .volume-button').click(function(){
            player.muteVolume();
        });

        // Music scan button event
        $('#music-scan').click(function () {
            music.scan_music();
        });

        // Delete music data button event
        $('.music-delete-data').click(function () {
            if (confirm('Are you sure you want to delete all the music information? This is irreversible!')) {
                music.delete_music_data()
            }
        });

        // Delete music data button event
        $('#music-show-artist').click(function () {
            music.get_artist_list();
        });

        // Delete music data button event
        $('#music-show-all').click(function () {
            music.get_music();
        });


        $(".volume-bar").slider({
            min: 0,
            max: 100,
            value: 100,
            range: "min",
            animate: true,
            slide: function(event, ui) {
                if(player.playingState == "Playing") {
                    player.setVolume();
                }
            },
            change: function( event, ui ) {
                if(player.playingState == "Playing") {
                    player.setVolume();
            }}
        });

        // Fix the player lenght to the same as the content
        $('.player-container').width($('#app-content-wrapper').width());
        var containerWidth = $('#app-content-wrapper').width();
        var playerCenterWidth = containerWidth-5 - $('.player-left').width() - $('.player-right').width();
        $('.player-center').width(playerCenterWidth);
        $(window).resize(function() {
            $('.player-container').width($('#app-content-wrapper').width());
            var containerWidth = $('#app-content-wrapper').width();
            var playerCenterWidth = containerWidth-5 - $('.player-left').width() - $('.player-right').width();
            $('.player-center').width(playerCenterWidth);
        });

        music.get_music();

        $(player.playerElement).on('ended', function() {
            music.add_time_played_counter(player.currentSong.file_id);
            player.nextSong();
        });

        $(player.playerElement).on("error", function (e) {
            alert("Error loading the song. It may have been moved or deleted. Please rescan your music!");
        });

        player.playerElement.ontimeupdate = function() {
            var progress = ((player.playerElement.currentTime*100)/player.playerElement.duration).toFixed(2);
            $('.seek-bar-progress').css('width',progress+"%");
            $('.seek-bar-ball').css('left',progress+"%");
            $('.current-time').text(music.second_to_duration(player.playerElement.currentTime.toFixed(0)));
            $('.duration-time').text(music.second_to_duration(player.playerElement.duration.toFixed(0)));
        };

        player.playerElement.onprogress = function() {
            if(player.playerElement.buffered.length > 0) {
                var bufferedEnd = player.playerElement.buffered.end(player.playerElement.buffered.length - 1);
                var duration =  player.playerElement.duration;
                var progress = ((bufferedEnd*100)/duration).toFixed(2);
                if (duration > 0) {
                    $('.seek-bar-progress-buffered').css('width',progress+"%");
                }
            }
        };
        $('.song-title').marquee();
        RefreshTagsList();

        function RefreshTagsList() {
            //Activate Tag Selector
            var tagSelector = $(".js-example-basic-multiple");
            tagSelector.select2({tags: true, placeholder: 'Select a tag'});
            var url = OC.generateUrl('/apps/hyperionmusic/returnalltags');
            var data = {};
            $.post(url, data).success(function (response) {
                if (response.data !== undefined) {
                    $.each(response.data, function (i, item) {
                        $('.js-example-basic-multiple').append($('<option>', {
                            value: item.id,
                            text : item.name
                        }));
                    });
                    tagSelector.on("select2:select select2:unselect", function (e) {
                        var selectedValue = tagSelector.val();
                        if (selectedValue == null) {
                            music.get_music();
                        } else {
                            music.get_music(selectedValue.toString());
                        }
                    });
                } else {
                    OC.Notification.showTemporary(t('hyperionmusic', 'No tag found.'))
                }
            });
        }

        Mousetrap.bind('r', function() { player.repeatSong(); });
        Mousetrap.bind('s', function() { player.shuffleSong(); });
        Mousetrap.bind('j', function() { player.previousSong(); });
        Mousetrap.bind('k', function() { player.nextSong(); });
        Mousetrap.bind('m', function() { player.muteVolume(); });
        Mousetrap.bind('right', function() { player.changeVolume(5); });
        Mousetrap.bind('left', function() { player.changeVolume(-5); });

        $(document).keydown(function(e) {
            switch(e.which) {

                case 32: // Space
                    player.playPause();
                    break;

                default: return; // exit this handler for other keys
            }
            e.preventDefault(); // prevent the default action (scroll / move caret)
        });

    });
})(jQuery, OC);