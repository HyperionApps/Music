<?php
script('hyperionmusic', 'main');
script('hyperionmusic', 'app');
script('hyperionmusic', 'player');
script('hyperionmusic', 'marques');
script('hyperionmusic', 'select2.min');
script('hyperionmusic', 'mousetrap');
style('hyperionmusic', 'style');
style('hyperionmusic', 'select2.min');
style('hyperionmusic/3rdparty/fontawesome','font-awesome');
?>

<div id="app">
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
		<?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php print_unescaped($this->inc('part.content')); ?>
		</div>
	</div>
</div>
