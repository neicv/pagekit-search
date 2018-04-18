<?php

namespace Friendlyit\Search\Controller;

/**
 * @Access(admin=true)
 */
class InfoController
{
    public function indexAction()
    {

        return [
            '$view' => [
                'title' => 'Search Info',
                'name' => 'friendlyit/search:views/admin/info/index.info.php'
            ]
            //'$data' => $data
        ];
    }
}