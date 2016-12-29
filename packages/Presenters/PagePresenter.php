<?php namespace WpPack\Presenters;

use Illuminate\Support\Collection;

/**
 * PagePresenter
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2014 Bruno Barros
 */
class PagePresenter extends PostPresenter
{


	/**
	 * | -----------------------------------
	 * | Page Relationships presenters
	 * | -----------------------------------
	 */

	/**
	 * Return the parent page as PagePresenter instance
	 *
	 * @return \self
	 */
	public function presentParent()
	{
		if ((int)$this->post_parent === 0)
		{
			return null;
		}
		$parent = get_post($this->post_parent);

		return new static($parent, false);
	}


	/**
	 * Return direct children of the page as PagePresenter instances
	 *
	 * @return Collection
	 */
	public function presentChildren()
	{

		$childrenPages = new \WP_Query(
			array(
				'post_type'      => 'page',
				'post_parent'    => $this->ID,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'posts_per_page' => -1
			)
		);

		if (count($childrenPages->posts) == 0 || ! $childrenPages)
		{
			return null;
		}

		$pages = array();

		foreach ($childrenPages->posts as $p)
		{
			$pages[] = new static($p, false);
		}

		return Collection::make($pages);

	}

}
