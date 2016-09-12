<?php

return [

    /*
     * Installation hook.
     */
	'install' => function ($app) {

        $util = $app['db']->getUtility();

        if ($util->tableExists('@search_keywords') === false) {
            $util->createTable('@search_keywords', function ($table) {
                $table->addColumn('id', 'integer', ['unsigned' => true, 'length' => 10, 'autoincrement' => true]);
                $table->addColumn('word', 'string', ['length' => 255]);
				$table->addColumn('ip', 'integer',['unsigned' => true, 'default' => 0]);
				$table->addColumn('putdate', 'datetime', ['notnull' => false]);
                $table->setPrimaryKey(['id']);
				$table->addIndex(['word'], '@SEARCH_KEYWORDS_WORD');
                $table->addIndex(['putdate'], '@SEARCH_KEYWORDS_PUTDATE');
            });
        }
	},
	 
    /*
     * Enable hook
     *
     */
    'enable' => function ($app) {

    },
	
    /*
     * Uninstall hook
     *
     */
    'uninstall' => function ($app) {

		$util = $app['db']->getUtility();

        if ($util->tableExists('@search_keywords')) {
            $util->dropTable('@search_keywords');
        }
        // remove the config
        $app['config']->remove('friendlyit/search');

    },

    /*
     * Runs all updates that are newer than the current version.
     *
     */
	 
	'updates' => [

        '0.1.5' => function ($app) {

			$util = $app['db']->getUtility();

			if ($util->tableExists('@search_keywords') === false) {
				$util->createTable('@search_keywords', function ($table) {
					$table->addColumn('id', 'integer', ['unsigned' => true, 'length' => 10, 'autoincrement' => true]);
					$table->addColumn('word', 'string', ['length' => 255]);
					$table->addColumn('ip', 'integer',['unsigned' => true, 'default' => 0]);
					$table->addColumn('putdate', 'datetime', ['notnull' => false]);
					$table->setPrimaryKey(['id']);
					$table->addIndex(['word'], '@SEARCH_KEYWORDS_WORD');
					$table->addIndex(['putdate'], '@SEARCH_KEYWORDS_PUTDATE');
				});
			}
		}
    ]

];