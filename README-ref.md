# Reference

### TimberCore

#### title

#### slug

#### content

#### path



### Timber
#### get_posts($query, $PostClass = 'TimberPost')
Send WordPress an arbitrary [WordPress Query](http://codex.wordpress.org/Class_Reference/WP_Query) or an array of IDs and it will send you back an array of Post Objects. By default it will use `TimberPost` but you can supply your own subclass of `TimberPost`.

If you send **false** to the $query, Timber takes the WordPress loop and translates into an array of Post Objects. By default it will use `TimberPost` but you can supply your own subclass of `TimberPost`.

##### returns
(array) of TimberPosts

#### get_context()
Returns a basic context object with:
* ['http_host'] = 'http://mywordpresssite.com';
* ['wp_title'] = "Jared's Site";
* ['wp_head'] = the output from wp_head();
* ['wp_footer'] = the output of wp_footer();
* ['wp_nav_menu'] = <ul><li>Whatever HTML is rendered from your nav menu '</li></ul>';

##### returns
(array) an associative array of different types

#### get_wp_footer
##### returns
(string)

#### get_wp_head
##### returns
(string)

### TimberCore
#### import();
#### url_to_path()

### TimberPost extends TimberCore
init()
update()


### TimberComment extends TimberCore
### TimberImage extends TimberCore
### TimberTerm extends TimberCore
### TimberUser extends TimberCore