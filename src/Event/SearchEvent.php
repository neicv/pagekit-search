<?php

namespace Friendlyit\Search\Event;

use Pagekit\Event\Event;

class SearchEvent extends Event
{
    /**
     * @var string
     */
    protected $search;

    /**
     * @var array
     */
    protected $plugins = [];
	
	 /**
     * @var array
     */
    protected $s_array = [];
	
	 /**
     * @var array
     */
    protected $a_data = [];

     /*
     * Constructor.
     *
     * @param string $name
     * @param array  $parameters
    */
    public function __construct($name, array $parameters = [])
    {
       // parent::__construct($parameters);
		parent::__construct($name, $parameters);

        //$this->search = $search;
    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param string $searchev
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }

	 /**
     * @param array $search
     */
    public function setSearchArray(array $search)
    {
        $this->s_array [] = $search;
    }
	/**
     * @return array
     */
    public function getSearchArray()
    {
        return $this->s_array;
    }

    /**
     * @param array $search
     */
    public function setSearchData(array $a_data)
    {
        $this->a_data [] = $a_data;
    }
	/**
     * @return array
     */
    public function getSearchData()
    {
        return $this->a_data;
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

	 /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
	
    /**
     * @param  string $name
     * @return mixed
     */
    public function getPlugin($name)
    {
        return isset($this->plugins[$name]) ? $this->plugins[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed  $callback
     */
    public function addPlugin($name, $callback)
    {
        $this->plugins[$name] = $callback;
    }
}
