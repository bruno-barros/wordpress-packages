<?php namespace WpPack\Support;

use Illuminate\Support\Collection;
use WpPack\Presenters\PagePresenter;

/**
 * Breadcrumb
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2016 Bruno Barros
 */




/**
 * Breadcrumb
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2014 Bruno Barros
 */
class Breadcrumb
{

    /**
     * Post types excluded to show link
     * @var array
     */
    protected $excludedPostTypes = ['page', 'post'];

    /**
     * @var PagePresenter
     */
    protected $post;

    /**
     * @var string
     */
    protected $dateFormat = 'F Y';

    /**
     * The title of the homepage. Default is site name.
     * @var null
     */
    protected $homeTitle = null;
    /**
     * @var array
     */
    protected $args = [
        'taxonomies' => null,
        'overload'   => []
    ];

    function __construct($wpPost = null, array $args = [])
    {
        global $post;

        $thePost = (!$wpPost) ? ($post) ? $post : null : $wpPost;

        if ($thePost)
        {
            $this->post = new PagePresenter($thePost);
        }

        $this->args = array_merge($this->args, $args);
    }


    /**
     * Quick way to get the breadcrumb
     *
     * @param array $args
     * @return mixed
     */
    public static function make(array $args = [])
    {
        $bc = new static(null, $args);

        return $bc->get();
    }

    /**
     * Return the breadcrumb as array
     *
     * @return Collection
     */
    public function get()
    {

        //		dump($this->getPostType());
        //		dump($this->getTax());
        //		dump($this->getTerm());
        $b = [];

        if (!is_front_page())
        {
            // home
            $b[] = [
                'title' => ($this->homeTitle) ? $this->homeTitle : get_bloginfo('name'),
                'url'   => get_option('home')
            ];

            if (is_category())
            {
                if ($c = get_category(get_query_var('cat')))
                {
                    $b[] = ['title' => $c->name, 'url' => false];
                }

            }
            else if (is_post_type_archive() && !is_tax() && $this->post)
            {
                $postType = $this->post->postType;
                if (!in_array($postType->name, $this->excludedPostTypes))
                {
                    $b[] = ['title' => $postType->label, 'url' => null];
                }

            }
            else if (is_tax())
            {

                // if is custom post type
                if ($pt = get_post_type_object($this->getPostType()))
                {
                    if (!in_array($pt->name, $this->excludedPostTypes))
                    {
                        $b[] = ['title' => $pt->label, 'url' => get_post_type_archive_link($this->getPostType())];
                    }
                }

                $tax = get_term_by('slug', $this->getTerm(), get_query_var('taxonomy'));
                $b[] = ['title' => $tax->name, 'url' => null];

            }
            else if (is_single())
            {

                // if is custom post type
                $postType = $this->post->postType;
                if (!in_array($postType->name, $this->excludedPostTypes))
                {
                    $b[] = ['title' => $postType->label, 'url' => $postType->permalink];
                }
                else if (in_array($postType->name, $this->excludedPostTypes)
                    && $custom = $this->getOverloaded($postType->name)
                )
                {
                    $b[] = ['title' => $custom->labels->name, 'url' => $custom->permalink];
                }

                if ($this->post->categories)
                {
                    foreach ($this->post->categories as $c)
                    {
                        $b[] = ['title' => $c->name, 'url' => $c->permalink];
                    }
                }

                if ($tax = $this->getCurrentTaxonomy())
                {
                    $b[] = ['title' => $tax->name, 'url' => get_term_link($tax)];
                }

                $b[] = ['title' => $this->post->title, 'url' => false];

            }
            elseif (is_date())
            {
                $b[] = ['title' => get_the_time($this->dateFormat), 'url' => false];
            }
            elseif (is_page() && $this->post->post_parent)
            {
                for ($i = count($this->post->ancestors) - 1; $i >= 0; $i--)
                {
                    $b[] = [
                        'title' => get_the_title($this->post->ancestors[$i]),
                        'url'   => get_permalink($this->post->ancestors[$i])
                    ];
                }

                $b[] = ['title' => $this->post->title, 'url' => false];

            }
            elseif (is_page())
            {
                $b[] = ['title' => $this->post->title, 'url' => false];
            }
            else if (is_tag())
            {
                $b[] = ['title' => single_tag_title('', false), 'url' => false];
            }
            else if (is_author())
            {
                $b[] = ['title' => sprintf(__('Posts by %s'), get_the_author()), 'url' => false];
            }
            else if (is_search())
            {
                $b[] = [
                    'title' => sprintf(__('Search Results %1$s %2$s'), ':', '"' . esc_html(get_search_query()) . '"'),
                    'url'   => false
                ];
            }
            elseif (is_404())
            {
                $b[] = ['title' => __('Page not found'), 'url' => false];
            }
        }
        else
        {
            $b[] = [
                'title' => ($this->homeTitle) ? $this->homeTitle : get_bloginfo('name'),
                'url'   => false
            ];
        }

        return Collection::make($b);
    }

    /**
     * Find a custom taxonomy
     *
     * @return mixed|null
     */
    protected function getCurrentTaxonomy()
    {
        if (!isset($this->args['taxonomies']) || empty($this->args['taxonomies']))
        {
            $this->args['taxonomies'] = $this->getDefaultTaxonomies();
        }

        foreach ($this->args['taxonomies'] as $tax)
        {
            if ($tax = get_term_by('slug', get_query_var($tax), $tax))
            {
                return $tax;
                break;
            }
        }

        return null;
    }

    /**
     * return the fake post type
     *
     * @param $postType
     * @return null
     */
    protected function getOverloaded($postType)
    {
        if (!isset($this->args['overload'][$postType]))
        {
            return null;
        }

        return $this->args['overload'][$postType];
    }

    /**
     * @return array
     */
    protected function getDefaultTaxonomies()
    {
        return array_values(get_taxonomies());
    }


    /**
     * Return the post type slug if available
     *
     * @return bool|string
     */
    public function getPostType()
    {
        global $wp_query;
        if(isset($wp_query->post) && $wp_query->post->post_type)
        {
            return $wp_query->post->post_type;
        }
        if (isset($wp_query->query) && isset($wp_query->query['post_type']))
        {
            return $wp_query->query['post_type'];
        }
        else if(isset($wp_query->query_vars) && isset($wp_query->query_vars['post_type']))
        {
            return $wp_query->query_vars['post_type'];
        }
        return false;
    }
    /**
     * Return the taxonomy slug if available
     *
     * @return bool|mixed
     */
    public function getTax()
    {
        global $wp_query;
        if (isset($wp_query->tax_query) && !empty($wp_query->tax_query))
        {
            return reset($wp_query->tax_query->queries)['taxonomy'];
        }
        return false;
    }
    /**
     * Return the taxonomy term if available
     *
     * @return bool|mixed
     */
    public function getTerm()
    {
        global $wp_query;
        if (isset($wp_query->tax_query) && !empty($wp_query->tax_query))
        {
            $first = reset($wp_query->tax_query->queries);
            return is_array($first) ? reset($first['terms']) : false;
        }
        return false;
    }

}