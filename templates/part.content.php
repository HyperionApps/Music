<div id="songlist"></div>
<div class="player-container mpbsc noselect">
    <div class="player-interface">
        <div class="player-left">
            <div class="song-status">
                <div class="song-preview">
                    <img id="song-preview-img" src="<?php p(OCP\image_path('hyperionmusic', 'app.svg')) ?>" alt="">
                </div>
                <div class="song-info">
                    <div class="song-title">No song selected</div>
                    <div class="song-artist"></div>
                </div>
            </div>
            <div class="player-main-button">
                <i class="player-button backward fa fa-2x fa-backward"></i>
                <i class="player-button play-pause fa fa-2x fa-play"></i>
                <i class="player-button forward fa fa-2x fa-forward"></i>
            </div>
        </div>
         <div class="player-center">
             <div class="current-time">00:00</div>
             <div class="time-separator"> - </div>
             <div class="seek-bar-track">
                 <div class="seek-bar-progress-buffered"></div>
                 <div class="seek-bar-progress"></div>
                 <div class="seek-bar-ball"></div>
             </div>
             <div class="duration-time">00:00</div>
        </div>
        <div class="player-right">
            <i class="player-button repeat fa fa-2x fa-refresh"></i>
            <i class="player-button shuffle fa fa-2x fa-random"></i>
            <div class="volume-control">
                <i class="volume-button fa fa-volume-up"></i>
                <div class="volume-bar"></div>
            </div>
        </div>
    </div>
</div>
