<?php
use Friendlyit\Search\Helpers\EXSearchHelper;
return [

    'name' => 'search/searchwidget',

    'label' => 'Search Widget',
	
	'defaults' => [
        'result_per_page' => 6,
        'triggering_chars' => 3,
		'char_limit' => 140,
		'css_enabled' => false,
    ],

    'events' => [

        'view.scripts' => function ($event, $scripts) use ($app) {
            $scripts->register('widget-searchwidget', 'friendlyit/search:app/bundle/search-widget-settings.js', ['~widgets']);
        }

    ],

    'render' => function ($widget) use ($app) {

		$EXSearchHelper = new EXSearchHelper();
		$upper_limit = $EXSearchHelper::getUpperLimitSearchWord();
		$upper_limit = (int)((!$upper_limit) ? 200 : $upper_limit);
		//$layout = false;
		$layout = true;
		$result_per_page	= (int)((!$widget->get('result_per_page')) ? 6 : $widget->get('result_per_page'));
		$triggering_chars 	= (int)((!$widget->get('triggering_chars')) ? 3 : $widget->get('triggering_chars'));
		$char_limit			= (int)((!$widget->get('char_limit')) ? 140 : $widget->get('char_limit'));
		$css_enabled		= (int)((!$widget->get('css_enabled')) ? false : $widget->get('css_enabled'));
        //return $app['view']('friendlyit/search:views/widget/widget-search.php', compact('widget', 'options', 'result_per_page', 'triggering_chars', 'char_limit', 'css_enabled', 'upper_limit', 'layout'));
		return $app['view']('friendlyit/search/widget/widget-search.php', compact('widget', 'options', 'result_per_page', 'triggering_chars', 'char_limit', 'css_enabled', 'upper_limit', 'layout'));
    }
];