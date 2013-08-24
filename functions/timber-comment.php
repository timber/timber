<?php

class TimberComment extends TimberCore {

    function __construct($cid) {
        $this->init($cid);
    }

    /* core definition */

    function author() {
        if ($this->user_id) {
            return new TimberUser($this->user_id);
        }
        $fakeUser = new stdClass();
        $fakeUser->name = 'Anonymous';
        if ($this->comment_author) {
            $fakeUser->name = $this->comment_author;
        }
        return $fakeUser;
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
