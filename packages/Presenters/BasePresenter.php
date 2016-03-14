<?php namespace WpPack\Presenters;

use WP_Post;

/**
 * BasePresenter
 * 
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright	Copyright (c) 2014 Bruno Barros
 */
abstract class BasePresenter{

	/**
	 * The $post object
	 * @var WP_Post
	 */
	protected $wpo;

	/**
	 * The post category taxonomy
	 * category (default), false
	 * @var string
	 */
	protected $categoryTax = 'category';

	/**
	 * The post tag taxonomy
	 * post_tag (default), false
	 * @var string
	 */
	protected $tagTax = 'post_tag';

	/**
	 * If is main query, set up WordPress: setup_postdata(post)
	 * @var boolean
	 */
	protected $isMainQuery = true;



	public function __construct($thePost = null, $mainQuery = true)
	{
		global $post;

		$this->isMainQuery = (bool)$mainQuery;

		if (is_object($thePost))
		{
			$this->wpo = $thePost;
		}
		else if (is_numeric($thePost))
		{
			$this->wpo = get_post($thePost);
		}
		else if (is_string($thePost))
		{
			$this->wpo = new \WP_Query(array('name' => $thePost));
		}
		else
		{
			$this->wpo = $post;
		}

		/*
		@link https://codex.wordpress.org/Function_Reference/setup_postdata
		Sets up global post data. Helps to format custom query results for using Template tags.
		setup_postdata() fills the global variables $id, $authordata, $currentday, $currentmonth,
		$page, $pages, $multipage, $more, $numpages, which help many Template Tags work in the
		current post context. It does not assign the global $post variable, but seems to expect
		that its argument is a reference to it.
		 */
		if($this->isMainQuery)
		{
			setup_postdata( $this->wpo );
		}
	}

	/**
	 * Pass any unknown variable calls to present{$variable}()
	 * or fall through to the injected object.
	 *
	 * @param  string $var
	 * @return mixed
	 */
	public function __get($var)
	{
		$method = 'present' . str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $var)));

		if (method_exists($this, $method))
		{
			return $this->$method();
		}

		if (get_class($this->wpo) == 'WP_Post')
		{
			return $this->wpo->$var;
		}
		else // WP_Query
		{
			return $this->wpo->post->$var;
		}
	}

	/**
	 * Pass any unknown methods through to the inject object.
	 *
	 * @param  string $method
	 * @param  array  $arguments
	 * @return mixed
	 */
	public function __call($method, $arguments)
	{
		return call_user_func_array(array($this->wpo, $method), $arguments);
	}

}