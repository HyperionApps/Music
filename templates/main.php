<?php
script('hyperionmusic', [
		'main',
		'app',
		'player',
		'marques',
		'select2.min',
		'mousetrap'
	]
);
style('hyperionmusic', [
		'style',
		'select2.min'
	]
);
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
