# PHP Router class

A simple Rails inspired PHP router class.

* Usage of different HTTP Methods
* REST / Resourceful routing
* Reversed routing using named routes
* Dynamic URL's: use URL segments as parameters.

## Usage

    <?php
    require 'Router.php';
    require 'Route.php';

    $router = new Router();

    $router->setBasePath('/PHP-Router');

    // defining routes can be as simple as this
    $router->map('/', 'users#index');

    // or somewhat more complicated
    $router->map('/users/:id/edit/', array('controller' => 'SomeController', 'action' => 'someAction'), array('methods' => 'GET,PUT', 'name' => 'users_edit', 'filters' => array('id' => '(\d+)')));

    // You can even specify closures as the Route's target
    $router->map('/hello/:name', function($name) { echo "Hello $name."; });

    // match current request URL & http method
    $target = $router->matchCurrentRequest();
    var_dump($target);

    // generate an URL 
    $router->generate('users_edit', array('id' => 5));


## More information
Have a look at the example.php file or read trough the class' documentation for a better understanding on how to use this class.

If you like PHP Router you might also like [AltoRouter](//github.com/dannyvankooten/AltoRouter).

## License
MIT Licensed, http://www.opensource.org/licenses/MIT