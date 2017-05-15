<?php namespace WpPack\Support;


/**
 * Context
 *
 * @author       Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2015 Bruno Barros
 */
class Context
{

    static public $instance;


    public static function make()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static;
        }

        return self::$instance;
    }


    /**
     * string representing architecture
     */
    public static function get()
    {
        $type = static::make()->type();

        if ($type == 'page')
        {
            $type = 'page';

        }
        else
        {
            if ($type == 'post')
            {
                $type = 'blog';
            }
        }

        return $type;
    }


    /**
     * Post type
     *
     * @return false|string
     */
    public function type($output = 'slug')
    {
        global $wp_query;

        if (is_search())
        {
            $pt = isset($_GET['post_type']) ? $_GET['post_type'] : '';

            if ($output == 'object' && strlen($pt) > 0)
            {
                return $this->typeObject($pt);
            }

            return 'search' . ((strlen($pt) > 0) ? ".{$pt}" : '');
        }

        if (is_404())
        {
            return '404';
        }

        $queriedType = get_post_type();

        if (!$queriedType && isset($wp_query->query['post_type']))
        {

            if ($output == 'object')
            {
                return $this->typeObject($wp_query->query['post_type']);
            }

            return $wp_query->query['post_type'];
        }
        
        if(!$queriedType && is_category())
        {
            return $this->term($output);
        }

        if ($output == 'object')
        {
            return $this->typeObject($queriedType);
        }

        return $queriedType;
    }


    /**
     * Taxonomy or category
     */
    public static function taxonomy()
    {
        global $wp_query;

        if (get_queried_object() && isset(get_queried_object()->taxonomy))
        {
            return get_queried_object()->taxonomy;
        }

        return null;
    }


    /**
     * Taxonomy term or category term
     */
    public static function term($output = 'slug')
    {
        global $wp_query;

        $self = static::make();

        if (isset($wp_query->query_vars['term']))
        {
            if ($output == 'object')
            {
                return $self->termObject($wp_query->query_vars['term'], get_query_var('taxonomy'));
            }

            return $wp_query->query_vars['term'];
        }
        else
        {
            if (is_category() && $cat = get_category(get_query_var('cat')))
            {
                $cat->label = $cat->name;
                return $output == 'object' ? $cat : $cat->slug;
            }
            else
            {
                if (is_tag())
                {
                    $tag = get_term_by('slug', get_query_var('tag'), 'post_tag');

                    $tag->label = $tag->name;
                    return $output == 'object' ? $tag : $tag->name;
                }
            }
        }

        return null;
    }


    public function termObject($term = '', $tax = '')
    {
        $term = get_term_by('slug', $term, $tax);

        if (!$term)
        {
            return $this->sudoTerm();
        }

        $term->label = $term->name;
        $term->permalink = get_term_link($term);

        return $term;
    }

    public function typeObject($typeSlug = '')
    {
        if (!is_string($typeSlug))
        {
            return $this->sudoTerm();
        }
        
        $type = get_post_type_object($typeSlug);

        $type->permalink = get_post_type_archive_link($typeSlug);

        return $type;
    }

    private function sudoTerm()
    {
        $term                   = new \stdClass();
        $term->name             = 'desconhecido';
        $term->label            = 'desconhecido';
        $term->description      = 'desconhecido';
        $term->slug             = 'desconhecido';
        $term->term_id          = 0;
        $term->term_group       = 0;
        $term->parent           = 0;
        $term->count            = 0;
        $term->taxonomy         = '';
        $term->term_taxonomy_id = 0;
        $term->permalink        = '#';

        return $term;
    }


}
