<?php

use Timber\Post;

class PostCommentCount extends Post
{
    public function set_comment_count($count)
    {
        $this->comment_count = $count;
    }
}
