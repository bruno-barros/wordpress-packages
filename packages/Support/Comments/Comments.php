<?php namespace WpPack\Support\Comments;

/**
 * Comments
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2016 Bruno Barros
 */
class Comments
{
    protected $itemTemplate = 'partials.comments.item';

    function __construct()
    {
        if($tmpl = config('comments.itemTemplate'))
        {
            $this->itemTemplate = $tmpl;
        }
    }

    public function item($comment, $args, $depth)
    {
        $GLOBALS['comment'] = $comment;
        extract($args, EXTR_SKIP);

        if ('div' == $args['style'])
        {
            $tag       = 'div';
            $add_below = 'comment';
        }
        else
        {
            $tag       = 'li';
            $add_below = 'div-comment';
        }

        echo view($this->itemTemplate, compact('args', 'tag', 'add_below', 'comment', 'depth'))->render();

    }

}