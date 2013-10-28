<?php

class TimberComment extends TimberCore {

    var $PostClass = 'TimberPost';

    public static $representation = 'comment';

    function __construct($cid) {
        $this->init($cid);
    }

    function author() {
        if ($this->user_id) {
            return new TimberUser($this->user_id);
        } else {
            $author = new TimberUser(0);
            if (isset($this->comment_author) && $this->comment_author){
                $author->name = $this->comment_author;
            } else {
                $author->name = 'Anonymous';
            }
        }
        return $author;
    }

    function date() {
        return $this->comment_date;
    }

    function content() {
        return $this->comment_content;
    }

    function init($cid) {
        $comment_data = $cid;
        if (is_integer($cid)) {
            $comment_data = get_comment($cid);
        }
        $this->import($comment_data);
        $this->ID = $this->comment_ID;
    }

}