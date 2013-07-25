<?php

class TimberPage
{

  function __construct($path, $file)
  {

  }

}

Timber::add_route('/articles/page/$p', function ($page) {
  if ($page > 1) {
    $query['paged'] = $page;
  }
});

Timber::add_route('/blog/', function ($page) {

});
