(function (music, $, OC) {

    music.get_music = function (tag) {
        var url = OC.generateUrl('/apps/hyperionmusic/getmusic');
        var data = {tags: tag};
        $.post(url, data).success(function (response) {
            if (response.data !== 'nodata') {
                music.render_songlist(response.data.songs);
            } else {
                $('#songlist').html('' +
                    '<div class="no-music-found">' +
                        'No music found. Add more in the files application and click Scan your music!' +
                    '</div>'
                );
            }
        });
    };

    music.get_artist = function (artistId) {
        var url = OC.generateUrl('/apps/hyperionmusic/getartist');
        var data = {artistId: artistId};
        $.post(url, data).success(function (response) {
            if (response.data !== 'nodata') {
                music.render_songlist(response.data.songs,response.data.artist);
                // Back button to return to the artist list
                $('#artist-return-artist-list').click(function () {
                    music.get_artist_list();
                });
            } else {
                $('#songlist').html('' +
                    '<div class="no-music-found">' +
                    'No artist found. Add more in the files application or edit a song information' +
                    '</div>'
                );
            }
        });
    };

    music.get_artist_list = function () {
        var url = OC.generateUrl('/apps/hyperionmusic/getartistlist');
        var data = {};
        $.post(url, data).success(function (response) {
            if (response.data !== 'nodata') {
                music.render_artistlist(response.data.artists);
            } else {
                $('#songlist').html('' +
                    '<div class="no-music-found">' +
                        'No artist found. Add more in the files application or edit a song information' +
                    '</div>'
                );
            }
        });
    };

    music.add_time_played_counter = function (index) {
        var url = OC.generateUrl('/apps/hyperionmusic/addtimeplayed');
        var data = {fileid: index};
        $.post(url, data).success(function (response) {});
    };

    music.scan_music = function () {
        var url = OC.generateUrl('/apps/hyperionmusic/scandrivemusic');
        var data = {};
        $.post(url, data).success(function (response) {
            if (response.success == true) {
                music.get_music();
            } else {
                OC.Notification.showTemporary(t('hyperionmusic', 'Error while scanning for music. Try again later.'));
            }
        });
    };

    music.delete_music_data = function () {
        var url = OC.generateUrl('/apps/hyperionmusic/deletemusicdatauser');
        var data = {};
        $.post(url, data).success(function (response) {
            if (response.success == true) {
                OC.Notification.showTemporary(t('hyperionmusic', response.message));
                music.get_music();
            } else {
                OC.Notification.showTemporary(t('hyperionmusic', 'Error while scanning for music. Try again later.'));
            }
        });
    };

    music.second_to_duration = function (second) {
        if(second == 'NaN') {
            minutes = '00';
            seconds = '00';
        } else {
            var sec_num = parseInt(second); // don't forget the second param
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
            var seconds = sec_num - (hours * 3600) - (minutes * 60);

            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}
        }
        return minutes+':'+seconds;
    };

    music.render_songlist = function (response, artist) {
        var song;
        var songindex = 0;
        var songlist = '';
        if(artist != undefined) {
            songlist +=
                '<div>' +
                    '<i id="artist-return-artist-list" class="fa fa-arrow-left" aria-hidden="true"></i>' +
                    '<div class="artist-breadcrumb">Current Artist: ' + artist + '</div>' +
                '</div>';
        }

        songlist += '<table style="width:100%">' +
            '<thead>' +
            '<th>Title</th>' +
            '<th>Artist</th>' +
            '<th>Album</th>' +
            '<th>Genre</th>' +
            '<th>Year</th>' +
            '<th>Bitrate</th>' +
            '<th>Time</th>' +
            '<th>Time played</th>' +
            '</thead>'+
            '<tbody>';
        response.forEach( function (song) {
            songlist += '<tr data-index="' + songindex + '" data-id="' + song.file_id + '" class="song">';
            songlist += '<td class="songlist-title">'+ song.title +'</td>';
            songlist += '<td class="songlist-artist">'+ song.artist +'</td>';
            songlist += '<td class="songlist-album">'+ song.album +'</td>';
            songlist += '<td class="songlist-genre">'+ song.genre +'</td>';
            songlist += '<td class="songlist-year">'+ song.year +'</td>';
            songlist += '<td class="songlist-bitrate">'+ parseInt(song.bitrate/1000) +'kbps</td>';
            songlist += '<td class="songlist-playtime">'+ song.play_time +'</td>';
            songlist += '<td class="songlist-timeplayed">'+ song.time_played +'</td>';
            songlist += '</tr>';
            songindex = songindex + 1;
        });
        songlist += '</tbody></table>';
        $('#songlist').html(songlist);
        music.musicList = response;
        $('tr.song').click(function(){
            load_track = $(this).attr('data-path');//gets me the url of the new track
            $('.playing-song').removeClass('playing-song');
            $(this).addClass('playing-song');
            player.change_track($(this).attr('data-id'));// function to change the track of the loaded audio player without page refresh preferred...
        });
        return songlist;
    };

    music.render_artistlist = function (response) {

        var songList = document.getElementById('songlist');
        songList.innerHTML = '';

        response.forEach( function (artist) {

            var artistContainer = document.createElement('div');
            var artistName = document.createElement('div');
            var imgContainer = document.createElement('img');
            var fancyArtistNameEnd = document.createElement('span');

            artistContainer.className = 'artist-list-container';
            artistContainer.dataset.id = artist.id;
            artistContainer.title = artist.name;

            if(artist.image != undefined) {
                imgContainer.src = artist.image;
            } else {
                imgContainer.src = '/apps/hyperionmusic/img/app.svg';
            }
            imgContainer.className = 'artist-list-img';
            artistContainer.appendChild(imgContainer);

            fancyArtistNameEnd.className = 'fancy-artist-name-end';

            artistName.innerHTML = artist.name;
            artistName.className = 'artist-list-name';
            artistName.appendChild(fancyArtistNameEnd);
            artistContainer.appendChild(artistName);

            songList.appendChild(artistContainer);

        });

        $('.artist-list-container').click(function(){
            var clickedArtist = $(this).attr('data-id');
            music.get_artist(clickedArtist);
        });

        return songlist;
    };

}( window.music = window.music || {}, jQuery, OC ));