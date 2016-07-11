bug# 1.
	ContextErrorException in EXSearchHelper.php line 345:
	Warning: stristr(): Empty needle
		public static function remove_accents($str)
		
fixed:
	line:  '161, 164' => strtolower TO: mb_strtolower
	
	
	
Know issues:
	#0001
		widget-search.php:
		line 1: <script src="app/assets/uikit/js/components/search.min.js"></script>
		line 2:	<script src="app/assets/uikit/js/components/autocomplete.min.js"></script>
	
	need to change another defenition
	
fixed:
		line 1,2:
		Changed to:
		<?php $view->script('autocomplete', 'app/assets/uikit/js/components/autocomplete.min.js', 'js') ?>
		<?php $view->script('search', 'app/assets/uikit/js/components/search.min.js', 'js') ?>
		