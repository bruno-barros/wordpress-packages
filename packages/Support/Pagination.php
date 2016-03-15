<?php namespace WpPack\Support;

/**
 * Pagination
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2016 Bruno Barros
 */
class Pagination
{
    protected $defaults = [
        'container_class' => 'pagination-wrapper',
        'ul_class'        => 'pagination',
    ];

    /**
     * (optional) How many numbers on either the start and the end list edges.
     * @var int
     */
    protected $endSize = 4;

    /**
     * (optional) How many numbers to either side of current page,
     * but not including current page.
     * @var int
     */
    protected $midSize = 4;

    /**
     * The view to render pagination
     * @var string
     */
    protected $view = null;

    function __construct($defaults = null)
    {

        $this->defaults['prev_text'] = __('« Previous');
        $this->defaults['next_text'] = __('Next »');

        if ($defaults)
        {
            $this->defaults = array_merge($this->defaults, (array)$defaults);
        }

        $this->view = config('pagination.view');
    }


    /**
     * Return pagination HTML
     *
     * @param array $options
     * @return string
     */
    public static function render($options = null)
    {
        $pagination = new static($options);

        if (!$links = $pagination->getLinks())
        {
            return '';
        }

        if (!$pagination->view)
        {
            return $pagination->autoRender($links);
        }

        return $pagination->renderView($links);

    }

    /**
     * Return the current page.
     * Default 1
     *
     * @return int
     */
    public static function current()
    {
        global $wp_query;

        return $wp_query->query_vars['paged'] > 1 ? $wp_query->query_vars['paged'] : 1;
    }

    /**
     * Render pagination based on a view
     *
     * @param $links
     * @return mixed
     */
    private function renderView($links)
    {
        $defaults = $this->defaults;

        return view($this->view, compact('links', 'defaults'))->render();
    }

    /**
     * Render de HTML
     *
     * @param $links
     * @return string
     */
    private function autoRender($links)
    {
        $list = "<div class=\"{$this->defaults['container_class']}\">\n\t";

        $list .= "<ul class=\"{$this->defaults['ul_class']}\">\n\t";

        foreach ($links as $l)
        {

            $active = ($l['active']) ? 'class="active"' : '';

            $list .= "<li {$active}>{$l['link']}</li>";
        }

        $list .= "</ul>\n";

        $list .= "</div>\n";

        return $list;
    }


    /**
     * WordPress regular functions to process pagination
     *
     * @param array $options
     * @return array|null
     */
    protected function getLinks($options = array())
    {
        global $wp_query, $wp_rewrite;

        //use the query for paging
        $current = $wp_query->query_vars['paged'] > 1 ? $wp_query->query_vars['paged'] : 1;

        //set the "paginate_links" array to do what we would like it it.
        //Check the codex for examples http://codex.wordpress.org/Function_Reference/paginate_links
        $pagination = array(
            'base'     => @add_query_arg('paged', '%#%'),
            //'format' => '',
            'showall'  => false,
            'end_size' => $this->endSize,
            'mid_size' => $this->midSize,
            'total'    => $wp_query->max_num_pages,
            'current'  => $current,
            'type'     => 'array',
            'prev_text' => $this->defaults['prev_text'],
            'next_text' => $this->defaults['next_text'],
        );

        //build the paging links
        if ($wp_rewrite->using_permalinks())
        {
            $pagination['base'] = user_trailingslashit(trailingslashit(remove_query_arg('s', get_pagenum_link(1))) . 'page/%#%/', 'paged');
        }

        //more paging links
        if (!empty($wp_query->query_vars['s']))
        {
            $pagination['add_args'] = array('s' => urlencode(get_query_var('s')));
        }

        $aLinks = paginate_links($pagination);

        if (!$aLinks)
        {
            return null;
        }

        return $this->parseWpLinks($aLinks);

    }

    /**
     * parse array of links and set which is active.
     *
     * @param $aLinks
     * @return array
     */
    private function parseWpLinks($aLinks)
    {
        $links = [];

        foreach ($aLinks as $link)
        {
            $links[] = [
                'active' => $this->checkActive($link),
                'link'   => $link
            ];
        }

        return $links;
    }

    /**
     * Check if a links is the active one
     *
     * @param $link
     * @return bool
     */
    private function checkActive($link)
    {
        if (strpos($link, 'page-numbers current') !== false)
        {
            return true;
        }

        return false;
    }
}