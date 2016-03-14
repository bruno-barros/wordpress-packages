<?php namespace WpPack\Presenters;

use Illuminate\Support\Collection;

/**
 * PostPresenter
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2014 Bruno Barros
 */
class PostPresenter extends BasePresenter
{


	/**
	 * | -----------------------------------
	 * | Date presenters
	 * | -----------------------------------
	 */
	/**
	 * Return the time on format defined on admin
	 *
	 * @see http://codex.wordpress.org/Function_Reference/get_the_time
	 * @return string
	 */
	public function presentTime()
	{
		return esc_attr(get_the_time('', $this->ID));
	}

	/**
	 * Return the date on format defined on admin
	 *
	 * @return string
	 */
	public function presentDate()
	{
		return esc_html(mysql2date(get_option('date_format'), $this->post_date));
	}

	/**
	 * Return the url to list posts on the same month and year
	 *
	 * @return string
	 */
	public function presentDateUrl()
	{
		return get_month_link(get_the_time('Y', $this->ID), get_the_time('m', $this->ID));
	}

	/**
	 * Return how many day was published
	 */
	public function presentPublishedDays()
	{
		$todaysDate = time();
		$postDate   = strtotime($this->post_date);

		$diff = abs($todaysDate - $postDate);

		$years  = floor($diff / (365 * 60 * 60 * 24));
		$months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
		$days   = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

		return (int)$days;
	}



	/**
	 * | -----------------------------------
	 * | Content presenters
	 * | -----------------------------------
	 */

	/**
	 * @return string
	 */
	public function presentTitle()
	{
		return esc_attr($this->post_title);
	}

	/**
	 * @return string
	 */
	public function presentSlug()
	{
		return $this->post_name;
	}

	/**
	 * @return string
	 */
	public function presentPermalink()
	{
		return get_permalink($this->ID);
	}


	/**
	 * @return string
	 */
	public function presentExcerpt()
	{
		return apply_filters('the_excerpt', get_post_field('post_excerpt', $this->ID));
	}

	/**
	 * @return string
	 */
	public function presentContent()
	{
		//		if ($this->isMainQuery)
		//		{
		//			return get_the_content();
		//		}

		$content = apply_filters('the_content', $this->post_content);

		$content = str_replace(']]>', ']]&gt;', $content);

		return $content;
	}



	/**
	 * | -----------------------------------
	 * | Relationship presenters
	 * | -----------------------------------
	 */

	/**
	 * Return an array of object with the categories of the post
	 *
	 * stdClass Object
	 * (
	 * [term_id] => int
	 * [name] => string
	 * [slug] => string
	 * [term_group] => int
	 * [term_taxonomy_id] => int
	 * [taxonomy] => category
	 * [description] => string
	 * [parent] => int
	 * [count] => int
	 * [permalink] => string
	 * )
	 * @return Collection
	 */
	public function presentCategories()
	{
		$args       = array(
			'fields'  => 'all',
			'orderby' => 'name',
			'order'   => 'ASC'
		);
		$categories = wp_get_post_categories($this->ID, $args);

		if (count($categories) == 0 || !$categories)
		{
			return null;
		}

		$cats = array();

		foreach ($categories as $c)
		{
			$c->permalink = get_category_link($c->term_id);
			$cats[]       = $c;
		}

		return Collection::make($cats);
	}


	/**
	 * Return an array of object with the tags of the post
	 *
	 * stdClass Object
	 * (
	 * [term_id] => int
	 * [name] => string
	 * [slug] => string
	 * [term_group] => int
	 * [term_taxonomy_id] => int
	 * [taxonomy] => category
	 * [description] => string
	 * [parent] => int
	 * [count] => int
	 * [permalink] => string
	 * )
	 * @return Collection
	 */
	public function presentTags()
	{
		$args = array(
			'fields'  => 'all',
			'orderby' => 'name',
			'order'   => 'ASC'
		);

		$tags = wp_get_post_tags($this->ID, $args);

		if (count($tags) == 0 || !$tags)
		{
			return null;
		}

		$postTags = array();

		foreach ($tags as $t)
		{
			$t->permalink = get_tag_link($t->term_id);
			$postTags[]   = $t;
		}

		return Collection::make($postTags);
	}


	/**
	 * Return a collection of post taxonomies terms
	 *
	 * @param  string $taxonomy 'post_tag' is default
	 * @return Collection
	 */
	public function terms($taxonomy = 'post_tag')
	{
		$postTerms = wp_get_post_terms($this->ID, $taxonomy);

		$cats = array();

		foreach ($postTerms as $c)
		{
			$c->permalink = get_term_link($c, $c->taxonomy);
			$cats[]       = $c;
		}

		return Collection::make($cats);
	}

	/**
	 * Attribute call to terms()
	 *
	 * @return Collection
	 */
	public function presentTerms()
	{
		return $this->terms();
	}


	/**
	 * Return a collection of post taxonomies
	 *
	 * stdClass Object {
	 *  ["term_id"]=> int
	 *  ["name"]=> string
	 *  ["slug"]=> string
	 *  ["term_group"]=> int
	 *  ["term_taxonomy_id"]=> int
	 *  ["taxonomy"]=> string
	 *  ["description"]=> string
	 *  ["parent"]=> int
	 *  ["count"]=> int
	 *  ["filter"]=> "raw"
	 *  ["permalink"]=>  string
	 *}
	 *
	 * @param  string $taxName
	 * @return Collection
	 */
	public function taxonomies($taxName = null)
	{
		$thisTax = ($taxName) ? $taxName : $this->categoryTax;

		$taxonomies = wp_get_object_terms($this->ID, $thisTax);

		if (!$taxonomies || empty($taxonomies))
		{
			return null;
		}

		$tax = array();

		foreach ($taxonomies as $t)
		{
			$t->permalink = get_term_link($t->slug, $thisTax);
			$tax[]        = $t;
		}

		return Collection::make($tax);
	}

	/**
	 * Attribute call to taxonomies()
	 *
	 * @return array
	 */
	public function presentTaxonomies()
	{
		return $this->taxonomies();
	}

	/**
	 * | ----------------------------------------------------
	 * | Images presenters
	 * | ----------------------------------------------------
	 */

	/**
	 * Return the thumbnail
	 *
	 * @return string
	 */
	public function presentThumb()
	{
		return $this->imgSize('thumbnail');
	}

	/**
	 * Return the featured image on given size
	 *
	 * @param string $size
	 * @return string
	 */
	public function imgSize($size = 'thumbnail')
	{
		$img = wp_get_attachment_image_src(get_post_thumbnail_id($this->ID), $size);

		return $img[0];
	}

	/**
	 * Return the post gallery as array
	 *
	 * @return Collection
	 */
	public function presentGallery()
	{
		return $this->gallery('thumbnail', 'array');
	}

	/**
	 * Return the imagens on post gallery with certain format
	 *
	 * array(5) {
	 * ['url']=> string
	 * ['width']=> int
	 * ['height']=> int
	 * ['resized']=> bool
	 * ["title"]=> string
	 * }
	 * @param string $size Images size to return
	 * @param string $format Only array of data, or <img> tag
	 * @return null|Collection
	 */
	public function gallery($size = 'thumbnail', $format = 'array', $limit = -1)
	{

		$images = get_posts(array(
			'post_parent'    => $this->ID,
			'post_type'      => 'attachment',
			'numberposts'    => $limit,
			'post_status'    => null,
			'post_mime_type' => 'image',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		));

		if (!$images || count($images) == 0)
		{
			return null;
		}

		$return = array();

		foreach ($images as $image)
		{
			if ($format == 'array')
			{
				$img          = wp_get_attachment_image_src($image->ID, $size, false);
				$i            = array();
				$i['url']     = $img[0];
				$i['width']   = $img[1];
				$i['height']  = $img[2];
				$i['resized'] = $img[3];
				$i['title']   = $image->post_title;
				$return[]     = $i;
			}
			else if ($format == 'html')
			{
				$return[] = wp_get_attachment_image($image->ID, $size, false);
			}
		}

		return Collection::make($return);

	}



	/**
	 * | ----------------------------------------------------
	 * | Author presenters
	 * | ----------------------------------------------------
	 */

	/**
	 * Return an instance of AuthorPresenter
	 *
	 * @param null $optionalPresenter
	 * @return AuthorPresenter
	 */
	public function author($optionalPresenter = null)
	{
		if ($optionalPresenter && $optionalPresenter instanceof PresenterInterface)
		{
			return new $optionalPresenter($this->wpo, $this->isMainQuery);
		}

		return new AuthorPresenter($this->wpo, $this->isMainQuery);
	}


	/**
	 * | ---------------------------------------------------
	 * | Helper presenters
	 * | ---------------------------------------------------
	 */

	/**
	 * Return the post meta
	 *
	 * @param  string $metaKey Chave do metadado
	 * @param  boolean $single Se retorna um array de valores, ou string punica
	 * @return mixed
	 */
	public function meta($metaKey = '', $single = true)
	{
		$meta_values = get_post_meta($this->ID, $metaKey, $single);

		return $meta_values;
	}

	/**
	 * Return the post type as object
	 *
	 * @link http://codex.wordpress.org/Function_Reference/get_post_type_object
	 * @return object
	 */
	public function presentPostType()
	{
		global $wp_rewrite;

		$postType = get_post_type_object(get_post_type($this->ID));

		if(! $postType)
		{
			return null;
		}

		if (get_option('permalink_structure') && is_array($postType->rewrite))
		{

			$slug = $postType->rewrite['slug'];

			if ($postType->rewrite['with_front'])
			{
				$structure = $wp_rewrite->front . $slug;
			}
			else
			{
				$structure = $wp_rewrite->root . $slug;
			}

			$link = url(user_trailingslashit($structure, 'post_type_archive'));

		}
		else
		{
			$slug = (isset($postType->slug) && $postType->slug) ? $postType->slug : $postType->name;
			$link = url('?post_type=' . $slug);
		}

		$postType->permalink = $link;

		return $postType;
	}

}