<?php

use Timber\Comment;

class CustomComment extends Comment
{
    public function foo()
    {
        return 'bar';
    }
}
