<?php

namespace Friendlyit\Search\Model;

use Pagekit\Application as App;
use Pagekit\Database\ORM\ModelTrait;
//use Pagekit\System\Model\DataModelTrait;

/**
 * @Entity(tableClass="@search_keywords")
 */
class SearchKeywords implements \JsonSerializable
{
	//use DataModelTrait, ModelTrait;
	use ModelTrait;
	
	/** @Column(type="integer") @Id */
    public $id;
	
    /** @Column(type="string") */
    public $word;

    /** @Column(type="integer") */
    public $ip;

    /** @Column(type="datetime") */
    public $putdate;

}
