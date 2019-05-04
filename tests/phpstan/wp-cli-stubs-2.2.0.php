<?php

namespace WP_CLI\Loggers {
    /**
     * Base logger class
     */
    abstract class Base
    {
        protected $in_color = false;
        public abstract function info($message);
        public abstract function success($message);
        public abstract function warning($message);
        /**
         * Retrieve the runner instance from the base CLI object. This facilitates
         * unit testing, where the WP_CLI instance isn't available
         *
         * @return Runner Instance of the runner class
         */
        protected function get_runner()
        {
        }
        /**
         * Write a message to STDERR, prefixed with "Debug: ".
         *
         * @param string $message Message to write.
         * @param string|bool $group Organize debug message to a specific group.
         * Use `false` for no group.
         */
        public function debug($message, $group = false)
        {
        }
        /**
         * Write a string to a resource.
         *
         * @param resource $handle Commonly STDOUT or STDERR.
         * @param string $str Message to write.
         */
        protected function write($handle, $str)
        {
        }
        /**
         * Output one line of message to a resource.
         *
         * @param string $message Message to write.
         * @param string $label Prefix message with a label.
         * @param string $color Colorize label with a given color.
         * @param resource $handle Resource to write to. Defaults to STDOUT.
         */
        protected function _line($message, $label, $color, $handle = STDOUT)
        {
        }
    }
    /**
     * Quiet logger only logs errors.
     */
    class Quiet extends \WP_CLI\Loggers\Base
    {
        /**
         * Informational messages aren't logged.
         *
         * @param string $message Message to write.
         */
        public function info($message)
        {
        }
        /**
         * Success messages aren't logged.
         *
         * @param string $message Message to write.
         */
        public function success($message)
        {
        }
        /**
         * Warning messages aren't logged.
         *
         * @param string $message Message to write.
         */
        public function warning($message)
        {
        }
        /**
         * Write an error message to STDERR, prefixed with "Error: ".
         *
         * @param string $message Message to write.
         */
        public function error($message)
        {
        }
        /**
         * Similar to error( $message ), but outputs $message in a red box
         *
         * @param  array $message Message to write.
         */
        public function error_multi_line($message_lines)
        {
        }
    }
    /**
     * Default logger for success, warning, error, and standard messages.
     */
    class Regular extends \WP_CLI\Loggers\Base
    {
        /**
         * @param bool $in_color Whether or not to Colorize strings.
         */
        public function __construct($in_color)
        {
        }
        /**
         * Write an informational message to STDOUT.
         *
         * @param string $message Message to write.
         */
        public function info($message)
        {
        }
        /**
         * Write a success message, prefixed with "Success: ".
         *
         * @param string $message Message to write.
         */
        public function success($message)
        {
        }
        /**
         * Write a warning message to STDERR, prefixed with "Warning: ".
         *
         * @param string $message Message to write.
         */
        public function warning($message)
        {
        }
        /**
         * Write an message to STDERR, prefixed with "Error: ".
         *
         * @param string $message Message to write.
         */
        public function error($message)
        {
        }
        /**
         * Similar to error( $message ), but outputs $message in a red box
         *
         * @param  array $message Message to write.
         */
        public function error_multi_line($message_lines)
        {
        }
    }
    /**
     * Execution logger captures all STDOUT and STDERR writes
     */
    class Execution extends \WP_CLI\Loggers\Regular
    {
        /**
         * Captured writes to STDOUT.
         */
        public $stdout = '';
        /**
         * Captured writes to STDERR.
         */
        public $stderr = '';
        /**
         * @param bool $in_color Whether or not to Colorize strings.
         */
        public function __construct($in_color = false)
        {
        }
        /**
         * Similar to error( $message ), but outputs $message in a red box
         *
         * @param  array $message Message to write.
         */
        public function error_multi_line($message_lines)
        {
        }
        /**
         * Write a string to a resource.
         *
         * @param resource $handle Commonly STDOUT or STDERR.
         * @param string $str Message to write.
         */
        protected function write($handle, $str)
        {
        }
        /**
         * Starts output buffering, using a callback to capture output from `echo`, `print`, `printf` (which write to the output buffer 'php://output' rather than STDOUT).
         */
        public function ob_start()
        {
        }
        /**
         * Callback for `ob_start()`.
         *
         * @param string $str String to write.
         * @return string Returns zero-length string so nothing gets written to the output buffer.
         */
        public function ob_start_callback($str)
        {
        }
        /**
         * To match `ob_start() above. Does an `ob_end_flush()`.
         */
        public function ob_end()
        {
        }
    }
}
namespace WP_CLI\Fetchers {
    /**
     * Fetch a WordPress entity for use in a subcommand.
     */
    abstract class Base
    {
        /**
         * @var string $msg The message to display when an item is not found
         */
        protected $msg;
        /**
         * @param string $arg The raw CLI argument
         * @return mixed|false The item if found; false otherwise
         */
        public abstract function get($arg);
        /**
         * Like get(), but calls WP_CLI::error() instead of returning false.
         *
         * @param string $arg The raw CLI argument
         */
        public function get_check($arg)
        {
        }
        /**
         * @param array The raw CLI arguments
         * @return array The list of found items
         */
        public function get_many($args)
        {
        }
    }
}
namespace WP_CLI\Iterators {
    /**
     * Applies one or more callbacks to an item before returning it.
     */
    class Transform extends \IteratorIterator
    {
        private $transformers = array();
        public function add_transform($fn)
        {
        }
        public function current()
        {
        }
    }
    /**
     * Iterates over results of a query, split into many queries via LIMIT and OFFSET
     *
     * @source https://gist.github.com/4060005
     */
    class Query implements \Iterator
    {
        private $chunk_size;
        private $query = '';
        private $count_query = '';
        private $global_index = 0;
        private $index_in_results = 0;
        private $results = array();
        private $row_count = 0;
        private $offset = 0;
        private $db = null;
        private $depleted = false;
        /**
         * Creates a new query iterator
         *
         * This will loop over all users, but will retrieve them 100 by 100:
         * <code>
         * foreach( new Iterators\Query( 'SELECT * FROM users', 100 ) as $user ) {
         *     tickle( $user );
         * }
         * </code>
         *
         * @param string $query The query as a string. It shouldn't include any LIMIT clauses
         * @param int $chunk_size How many rows to retrieve at once; default value is 500 (optional)
         */
        public function __construct($query, $chunk_size = 500)
        {
        }
        /**
         * Reduces the offset when the query row count shrinks
         *
         * In cases where the iterated rows are being updated such that they will no
         * longer be returned by the original query, the offset must be reduced to
         * iterate over all remaining rows.
         */
        private function adjust_offset_for_shrinking_result_set()
        {
        }
        private function load_items_from_db()
        {
        }
        public function current()
        {
        }
        public function key()
        {
        }
        public function next()
        {
        }
        public function rewind()
        {
        }
        public function valid()
        {
        }
    }
    /**
     * @source https://gist.github.com/4060005
     */
    class Table extends \WP_CLI\Iterators\Query
    {
        /**
         * Creates an iterator over a database table.
         *
         * <code>
         * foreach( new Iterators\Table( array( 'table' => $wpdb->posts, 'fields' => array( 'ID', 'post_content' ) ) ) as $post ) {
         *     count_words_for( $post->ID, $post->post_content );
         * }
         * </code>
         *
         * <code>
         * foreach( new Iterators\Table( array( 'table' => $wpdb->posts, 'where' => 'ID = 8 OR post_status = "publish"' ) ) as $post ) {
         *     …
         * }
         * </code>
         *
         * <code>
         * foreach( new PostIterator( array( 'table' => $wpdb->posts, 'where' => array( 'post_status' => 'publish', 'post_date_gmt BETWEEN x AND y' ) ) ) as $post ) {
         *     …
         * }
         * </code>
         *
         *
         * @param array $args Supported arguments:
         *      table – the name of the database table
         *      fields – an array of columns to get from the table, '*' is a valid value and the default
         *      where – conditions for filtering rows. Supports two formats:
         *              = string – this will be the where clause
         *              = array – each element is treated as a condition if it's positional, or as column => value if
         *                it's a key/value pair. In the latter case the value is automatically quoted and escaped
         *      append - add arbitrary extra SQL
         */
        public function __construct($args = array())
        {
        }
        private static function build_fields($fields)
        {
        }
        private static function build_where_conditions($where)
        {
        }
    }
    /**
     * Allows incrementally reading and parsing lines from a CSV file.
     */
    class CSV implements \Iterator
    {
        const ROW_SIZE = 4096;
        private $file_pointer;
        private $delimiter;
        private $columns;
        private $current_index;
        private $current_element;
        public function __construct($filename, $delimiter = ',')
        {
        }
        public function rewind()
        {
        }
        public function current()
        {
        }
        public function key()
        {
        }
        public function next()
        {
        }
        public function valid()
        {
        }
    }
    class Exception extends \RuntimeException
    {
    }
}
namespace WP_CLI {
    class ExitException extends \Exception
    {
    }
    /**
     * Extract a provided archive file.
     */
    class Extractor
    {
        /**
         * Extract the archive file to a specific destination.
         *
         * @param string $dest
         */
        public static function extract($tarball_or_zip, $dest)
        {
        }
        /**
         * Extract a ZIP file to a specific destination.
         *
         * @param string $zipfile
         * @param string $dest
         */
        private static function extract_zip($zipfile, $dest)
        {
        }
        /**
         * Extract a tarball to a specific destination.
         *
         * @param string $tarball
         * @param string $dest
         */
        private static function extract_tarball($tarball, $dest)
        {
        }
        /**
         * Copy files from source directory to destination directory. Source directory must exist.
         *
         * @param string $source
         * @param string $dest
         */
        public static function copy_overwrite_files($source, $dest)
        {
        }
        /**
         * Delete all files and directories recursively from directory. Directory must exist.
         *
         * @param string $dir
         */
        public static function rmdir($dir)
        {
        }
        /**
         * Return formatted ZipArchive error message from error code.
         *
         * @param int $error_code
         * @return string|int The error message corresponding to the specified code, if found;
         * Other wise the same error code, unmodified.
         */
        public static function zip_error_msg($error_code)
        {
        }
        /**
         * Return formatted error message from ProcessRun of tar command.
         *
         * @param Processrun $process_run
         * @return string|int The error message of the process, if available;
         * otherwise the return code.
         */
        public static function tar_error_msg($process_run)
        {
        }
    }
    /**
     * Doctrine inflector has static methods for inflecting text.
     *
     * The methods in these classes are from several different sources collected
     * across several different php projects and several different authors. The
     * original author names and emails are not known.
     *
     * Pluralize & Singularize implementation are borrowed from CakePHP with some modifications.
     *
     * @link   www.doctrine-project.org
     * @since  1.0
     * @author Konsta Vesterinen <kvesteri@cc.hut.fi>
     * @author Jonathan H. Wage <jonwage@gmail.com>
     */
    class Inflector
    {
        /**
         * Plural inflector rules.
         *
         * @var array
         */
        private static $plural = array('rules' => array('/(s)tatus$/i' => '\\1\\2tatuses', '/(quiz)$/i' => '\\1zes', '/^(ox)$/i' => '\\1\\2en', '/([m|l])ouse$/i' => '\\1ice', '/(matr|vert|ind)(ix|ex)$/i' => '\\1ices', '/(x|ch|ss|sh)$/i' => '\\1es', '/([^aeiouy]|qu)y$/i' => '\\1ies', '/(hive)$/i' => '\\1s', '/(?:([^f])fe|([lr])f)$/i' => '\\1\\2ves', '/sis$/i' => 'ses', '/([ti])um$/i' => '\\1a', '/(p)erson$/i' => '\\1eople', '/(m)an$/i' => '\\1en', '/(c)hild$/i' => '\\1hildren', '/(f)oot$/i' => '\\1eet', '/(buffal|her|potat|tomat|volcan)o$/i' => '\\1\\2oes', '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\\1i', '/us$/i' => 'uses', '/(alias)$/i' => '\\1es', '/(analys|ax|cris|test|thes)is$/i' => '\\1es', '/s$/' => 's', '/^$/' => '', '/$/' => 's'), 'uninflected' => array('.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', 'people', 'cookie'), 'irregular' => array('atlas' => 'atlases', 'axe' => 'axes', 'beef' => 'beefs', 'brother' => 'brothers', 'cafe' => 'cafes', 'chateau' => 'chateaux', 'child' => 'children', 'cookie' => 'cookies', 'corpus' => 'corpuses', 'cow' => 'cows', 'criterion' => 'criteria', 'curriculum' => 'curricula', 'demo' => 'demos', 'domino' => 'dominoes', 'echo' => 'echoes', 'foot' => 'feet', 'fungus' => 'fungi', 'ganglion' => 'ganglions', 'genie' => 'genies', 'genus' => 'genera', 'graffito' => 'graffiti', 'hippopotamus' => 'hippopotami', 'hoof' => 'hoofs', 'human' => 'humans', 'iris' => 'irises', 'leaf' => 'leaves', 'loaf' => 'loaves', 'man' => 'men', 'medium' => 'media', 'memorandum' => 'memoranda', 'money' => 'monies', 'mongoose' => 'mongooses', 'motto' => 'mottoes', 'move' => 'moves', 'mythos' => 'mythoi', 'niche' => 'niches', 'nucleus' => 'nuclei', 'numen' => 'numina', 'occiput' => 'occiputs', 'octopus' => 'octopuses', 'opus' => 'opuses', 'ox' => 'oxen', 'penis' => 'penises', 'person' => 'people', 'plateau' => 'plateaux', 'runner-up' => 'runners-up', 'sex' => 'sexes', 'soliloquy' => 'soliloquies', 'son-in-law' => 'sons-in-law', 'syllabus' => 'syllabi', 'testis' => 'testes', 'thief' => 'thieves', 'tooth' => 'teeth', 'tornado' => 'tornadoes', 'trilby' => 'trilbys', 'turf' => 'turfs', 'volcano' => 'volcanoes'));
        /**
         * Singular inflector rules.
         *
         * @var array
         */
        private static $singular = array('rules' => array('/(s)tatuses$/i' => '\\1\\2tatus', '/^(.*)(menu)s$/i' => '\\1\\2', '/(quiz)zes$/i' => '\\1', '/(matr)ices$/i' => '\\1ix', '/(vert|ind)ices$/i' => '\\1ex', '/^(ox)en/i' => '\\1', '/(alias)(es)*$/i' => '\\1', '/(buffal|her|potat|tomat|volcan)oes$/i' => '\\1o', '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\\1us', '/([ftw]ax)es/i' => '\\1', '/(analys|ax|cris|test|thes)es$/i' => '\\1is', '/(shoe|slave)s$/i' => '\\1', '/(o)es$/i' => '\\1', '/ouses$/' => 'ouse', '/([^a])uses$/' => '\\1us', '/([m|l])ice$/i' => '\\1ouse', '/(x|ch|ss|sh)es$/i' => '\\1', '/(m)ovies$/i' => '\\1\\2ovie', '/(s)eries$/i' => '\\1\\2eries', '/([^aeiouy]|qu)ies$/i' => '\\1y', '/([lr])ves$/i' => '\\1f', '/(tive)s$/i' => '\\1', '/(hive)s$/i' => '\\1', '/(drive)s$/i' => '\\1', '/([^fo])ves$/i' => '\\1fe', '/(^analy)ses$/i' => '\\1sis', '/(analy|diagno|^ba|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis', '/([ti])a$/i' => '\\1um', '/(p)eople$/i' => '\\1\\2erson', '/(m)en$/i' => '\\1an', '/(c)hildren$/i' => '\\1\\2hild', '/(f)eet$/i' => '\\1oot', '/(n)ews$/i' => '\\1\\2ews', '/eaus$/' => 'eau', '/^(.*us)$/' => '\\1', '/s$/i' => ''), 'uninflected' => array('.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss'), 'irregular' => array('criteria' => 'criterion', 'curves' => 'curve', 'emphases' => 'emphasis', 'foes' => 'foe', 'hoaxes' => 'hoax', 'media' => 'medium', 'neuroses' => 'neurosis', 'waves' => 'wave', 'oases' => 'oasis'));
        /**
         * Words that should not be inflected.
         *
         * @var array
         */
        private static $uninflected = array('Amoyese', 'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers', 'cod', 'coitus', 'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn', 'eland', 'elk', 'equipment', 'Faroese', 'flounder', 'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti', 'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese', 'mackerel', 'Maltese', '.*?media', 'mews', 'moose', 'mumps', 'Nankingese', 'news', 'nexus', 'Niasese', 'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese', 'proceedings', 'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass', 'series', 'Shavese', 'shears', 'siemens', 'species', 'staff', 'swine', 'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese', 'whiting', 'wildebeest', 'Yengeese');
        /**
         * Method cache array.
         *
         * @var array
         */
        private static $cache = array();
        /**
         * The initial state of Inflector so reset() works.
         *
         * @var array
         */
        private static $initial_state = array();
        /**
         * Converts a word into the format for a Doctrine table name. Converts 'ModelName' to 'model_name'.
         *
         * @param string $word The word to tableize.
         *
         * @return string The tableized word.
         */
        public static function tableize($word)
        {
        }
        /**
         * Converts a word into the format for a Doctrine class name. Converts 'table_name' to 'TableName'.
         *
         * @param string $word The word to classify.
         *
         * @return string The classified word.
         */
        public static function classify($word)
        {
        }
        /**
         * Camelizes a word. This uses the classify() method and turns the first character to lowercase.
         *
         * @param string $word The word to camelize.
         *
         * @return string The camelized word.
         */
        public static function camelize($word)
        {
        }
        /**
         * Uppercases words with configurable delimeters between words.
         *
         * Takes a string and capitalizes all of the words, like PHP's built-in
         * ucwords function.  This extends that behavior, however, by allowing the
         * word delimeters to be configured, rather than only separating on
         * whitespace.
         *
         * Here is an example:
         * <code>
         * <?php
         * $string = 'top-o-the-morning to all_of_you!';
         * echo \Doctrine\Common\Inflector\Inflector::ucwords($string);
         * // Top-O-The-Morning To All_of_you!
         *
         * echo \Doctrine\Common\Inflector\Inflector::ucwords($string, '-_ ');
         * // Top-O-The-Morning To All_Of_You!
         * ?>
         * </code>
         *
         * @param string $string The string to operate on.
         * @param string $delimiters A list of word separators.
         *
         * @return string The string with all delimeter-separated words capitalized.
         */
        public static function ucwords($string, $delimiters = " \n\t\r\0\v-")
        {
        }
        /**
         * Clears Inflectors inflected value caches, and resets the inflection
         * rules to the initial values.
         *
         * @return void
         */
        public static function reset()
        {
        }
        /**
         * Adds custom inflection $rules, of either 'plural' or 'singular' $type.
         *
         * ### Usage:
         *
         * {{{
         * Inflector::rules('plural', array('/^(inflect)or$/i' => '\1ables'));
         * Inflector::rules('plural', array(
         *     'rules' => array('/^(inflect)ors$/i' => '\1ables'),
         *     'uninflected' => array('dontinflectme'),
         *     'irregular' => array('red' => 'redlings')
         * ));
         * }}}
         *
         * @param string  $type  The type of inflection, either 'plural' or 'singular'
         * @param array   $rules An array of rules to be added.
         * @param boolean $reset If true, will unset default inflections for all
         *                       new rules that are being defined in $rules.
         *
         * @return void
         */
        public static function rules($type, $rules, $reset = false)
        {
        }
        /**
         * Returns a word in plural form.
         *
         * @param string $word The word in singular form.
         *
         * @return string The word in plural form.
         */
        public static function pluralize($word)
        {
        }
        /**
         * Returns a word in singular form.
         *
         * @param string $word The word in plural form.
         *
         * @return string The word in singular form.
         */
        public static function singularize($word)
        {
        }
    }
    /**
     * Output one or more items in a given format (e.g. table, JSON).
     */
    class Formatter
    {
        /**
         * @var array $args How the items should be output.
         */
        private $args;
        /**
         * @var string $prefix Standard prefix for object fields.
         */
        private $prefix;
        /**
         * @param array $assoc_args Output format arguments.
         * @param array $fields Fields to display of each item.
         * @param string|bool $prefix Check if fields have a standard prefix.
         * False indicates empty prefix.
         */
        public function __construct(&$assoc_args, $fields = null, $prefix = false)
        {
        }
        /**
         * Magic getter for arguments.
         *
         * @param string $key
         * @return mixed
         */
        public function __get($key)
        {
        }
        /**
         * Display multiple items according to the output arguments.
         *
         * @param array      $items
         * @param bool|array $ascii_pre_colorized Optional. A boolean or an array of booleans to pass to `format()` if items in the table are pre-colorized. Default false.
         */
        public function display_items($items, $ascii_pre_colorized = false)
        {
        }
        /**
         * Display a single item according to the output arguments.
         *
         * @param mixed      $item
         * @param bool|array $ascii_pre_colorized Optional. A boolean or an array of booleans to pass to `show_multiple_fields()` if the item in the table is pre-colorized. Default false.
         */
        public function display_item($item, $ascii_pre_colorized = false)
        {
        }
        /**
         * Format items according to arguments.
         *
         * @param array      $items
         * @param bool|array $ascii_pre_colorized Optional. A boolean or an array of booleans to pass to `show_table()` if items in the table are pre-colorized. Default false.
         */
        private function format($items, $ascii_pre_colorized = false)
        {
        }
        /**
         * Show a single field from a list of items.
         *
         * @param array Array of objects to show fields from
         * @param string The field to show
         */
        private function show_single_field($items, $field)
        {
        }
        /**
         * Find an object's key.
         * If $prefix is set, a key with that prefix will be prioritized.
         *
         * @param object $item
         * @param string $field
         * @return string $key
         */
        private function find_item_key($item, $field)
        {
        }
        /**
         * Show multiple fields of an object.
         *
         * @param object|array $data                Data to display
         * @param string       $format              Format to display the data in
         * @param bool|array   $ascii_pre_colorized Optional. A boolean or an array of booleans to pass to `show_table()` if the item in the table is pre-colorized. Default false.
         */
        private function show_multiple_fields($data, $format, $ascii_pre_colorized = false)
        {
        }
        /**
         * Show items in a \cli\Table.
         *
         * @param array      $items
         * @param array      $fields
         * @param bool|array $ascii_pre_colorized Optional. A boolean or an array of booleans to pass to `Table::setAsciiPreColorized()` if items in the table are pre-colorized. Default false.
         */
        private static function show_table($items, $fields, $ascii_pre_colorized = false)
        {
        }
        /**
         * Format an associative array as a table.
         *
         * @param array     $fields    Fields and values to format
         * @return array    $rows
         */
        private function assoc_array_to_rows($fields)
        {
        }
        /**
         * Transforms objects and arrays to JSON as necessary
         *
         * @param mixed $item
         * @return mixed
         */
        public function transform_item_values_to_json($item)
        {
        }
    }
    /**
     * Generate a synopsis from a command's PHPdoc arguments.
     * Turns something like "<object-id>..."
     * into [ optional=>false, type=>positional, repeating=>true, name=>object-id ]
     */
    class SynopsisParser
    {
        /**
         * @param string A synopsis
         * @return array List of parameters
         */
        public static function parse($synopsis)
        {
        }
        /**
         * Render the Synopsis into a format string.
         *
         * @param array $synopsis A structured synopsis. This might get reordered
         *                        to match the parsed output.
         * @return string Rendered synopsis.
         */
        public static function render(&$synopsis)
        {
        }
        /**
         * Classify argument attributes based on its syntax.
         *
         * @param string $token
         * @return array $param
         */
        private static function classify_token($token)
        {
        }
        /**
         * An optional parameter is surrounded by square brackets.
         *
         * @param string $token
         * @return array
         */
        private static function is_optional($token)
        {
        }
        /**
         * A repeating parameter is followed by an ellipsis.
         *
         * @param string $token
         * @return array
         */
        private static function is_repeating($token)
        {
        }
    }
    /**
     * Checks if the list of parameters matches the specification defined in the synopsis.
     */
    class SynopsisValidator
    {
        /**
         * @var array $spec Structured representation of command synopsis.
         */
        private $spec = array();
        /**
         * @param string $synopsis Command's synopsis.
         */
        public function __construct($synopsis)
        {
        }
        /**
         * Get any unknown arguments.
         *
         * @return array
         */
        public function get_unknown()
        {
        }
        /**
         * Check whether there are enough positional arguments.
         *
         * @param array $args Positional arguments.
         * @return bool
         */
        public function enough_positionals($args)
        {
        }
        /**
         * Check for any unknown positionals.
         *
         * @param array $args Positional arguments.
         * @return array
         */
        public function unknown_positionals($args)
        {
        }
        /**
         * Check that all required keys are present and that they have values.
         *
         * @param array $assoc_args Parameters passed to command.
         * @return array
         */
        public function validate_assoc($assoc_args)
        {
        }
        /**
         * Check whether there are unknown parameters supplied.
         *
         * @param array $assoc_args Parameters passed to command.
         * @return array|false
         */
        public function unknown_assoc($assoc_args)
        {
        }
        /**
         * Filters a list of associative arrays, based on a set of key => value arguments.
         *
         * @param array $args An array of key => value arguments to match against
         * @param string $operator
         * @return array
         */
        private function query_spec($args, $operator = 'AND')
        {
        }
    }
    /**
     * A Upgrader Skin for WordPress that only generates plain-text
     *
     * @package wp-cli
     */
    class UpgraderSkin extends \WP_Upgrader_Skin
    {
        public $api;
        public function header()
        {
        }
        public function footer()
        {
        }
        public function bulk_header()
        {
        }
        public function bulk_footer()
        {
        }
        public function error($error)
        {
        }
        public function feedback($string)
        {
        }
    }
}
namespace WP_CLI\Dispatcher {
    /**
     * A non-leaf node in the command tree.
     * Contains one or more Subcommands.
     *
     * @package WP_CLI
     */
    class CompositeCommand
    {
        protected $name;
        protected $shortdesc;
        protected $synopsis;
        protected $docparser;
        protected $parent;
        protected $subcommands = array();
        /**
         * Instantiate a new CompositeCommand
         *
         * @param mixed $parent Parent command (either Root or Composite)
         * @param string $name Represents how command should be invoked
         * @param \WP_CLI\DocParser
         */
        public function __construct($parent, $name, $docparser)
        {
        }
        /**
         * Get the parent composite (or root) command
         *
         * @return mixed
         */
        public function get_parent()
        {
        }
        /**
         * Add a named subcommand to this composite command's
         * set of contained subcommands.
         *
         * @param string $name Represents how subcommand should be invoked
         * @param Subcommand|CompositeCommand
         */
        public function add_subcommand($name, $command)
        {
        }
        /**
         * Remove a named subcommand from this composite command's set of contained
         * subcommands
         *
         * @param string $name Represents how subcommand should be invoked
         */
        public function remove_subcommand($name)
        {
        }
        /**
         * Composite commands always contain subcommands.
         *
         * @return true
         */
        public function can_have_subcommands()
        {
        }
        /**
         * Get the subcommands contained by this composite
         * command.
         *
         * @return array
         */
        public function get_subcommands()
        {
        }
        /**
         * Get the name of this composite command.
         *
         * @return string
         */
        public function get_name()
        {
        }
        /**
         * Get the short description for this composite
         * command.
         *
         * @return string
         */
        public function get_shortdesc()
        {
        }
        /**
         * Set the short description for this composite command.
         *
         * @param string
         */
        public function set_shortdesc($shortdesc)
        {
        }
        /**
         * Get the long description for this composite
         * command.
         *
         * @return string
         */
        public function get_longdesc()
        {
        }
        /**
         * Set the long description for this composite command
         *
         * @param string
         */
        public function set_longdesc($longdesc)
        {
        }
        /**
         * Get the synopsis for this composite command.
         * As a collection of subcommands, the composite
         * command is only intended to invoke those
         * subcommands.
         *
         * @return string
         */
        public function get_synopsis()
        {
        }
        /**
         * Get the usage for this composite command.
         *
         * @return string
         */
        public function get_usage($prefix)
        {
        }
        /**
         * Show the usage for all subcommands contained
         * by the composite command.
         */
        public function show_usage()
        {
        }
        /**
         * When a composite command is invoked, it shows usage
         * docs for its subcommands.
         *
         * @param array $args
         * @param array $assoc_args
         * @param array $extra_args
         */
        public function invoke($args, $assoc_args, $extra_args)
        {
        }
        /**
         * Given supplied arguments, find a contained
         * subcommand
         *
         * @param array $args
         * @return \WP_CLI\Dispatcher\Subcommand|false
         */
        public function find_subcommand(&$args)
        {
        }
        /**
         * Get any registered aliases for this composite command's
         * subcommands.
         *
         * @param array $subcommands
         * @return array
         */
        private static function get_aliases($subcommands)
        {
        }
        /**
         * Composite commands can only be known by one name.
         *
         * @return false
         */
        public function get_alias()
        {
        }
        /***
         * Get the list of global parameters
         *
         * @param string $root_command whether to include or not root command specific description
         * @return string
         */
        protected function get_global_params($root_command = false)
        {
        }
    }
    /**
     * A leaf node in the command tree.
     *
     * @package WP_CLI
     */
    class Subcommand extends \WP_CLI\Dispatcher\CompositeCommand
    {
        private $alias;
        private $when_invoked;
        public function __construct($parent, $name, $docparser, $when_invoked)
        {
        }
        /**
         * Extract the synopsis from PHPdoc string.
         *
         * @param string $longdesc Command docs via PHPdoc
         * @return string
         */
        private static function extract_synopsis($longdesc)
        {
        }
        /**
         * Subcommands can't have subcommands because they
         * represent code to be executed.
         *
         * @return bool
         */
        public function can_have_subcommands()
        {
        }
        /**
         * Get the synopsis string for this subcommand.
         * A synopsis defines what runtime arguments are
         * expected, useful to humans and argument validation.
         *
         * @return string
         */
        public function get_synopsis()
        {
        }
        /**
         * Set the synopsis string for this subcommand.
         *
         * @param string
         */
        public function set_synopsis($synopsis)
        {
        }
        /**
         * If an alias is set, grant access to it.
         * Aliases permit subcommands to be instantiated
         * with a secondary identity.
         *
         * @return string
         */
        public function get_alias()
        {
        }
        /**
         * Print the usage details to the end user.
         *
         * @param string $prefix
         */
        public function show_usage($prefix = 'usage: ')
        {
        }
        /**
         * Get the usage of the subcommand as a formatted string.
         *
         * @param string $prefix
         * @return string
         */
        public function get_usage($prefix)
        {
        }
        /**
         * Wrapper for CLI Tools' prompt() method.
         *
         * @param string $question
         * @param string $default
         * @return string|false
         */
        private function prompt($question, $default)
        {
        }
        /**
         * Interactively prompt the user for input
         * based on defined synopsis and passed arguments.
         *
         * @param array $args
         * @param array $assoc_args
         * @return array
         */
        private function prompt_args($args, $assoc_args)
        {
        }
        /**
         * Validate the supplied arguments to the command.
         * Throws warnings or errors if arguments are missing
         * or invalid.
         *
         * @param array $args
         * @param array $assoc_args
         * @param array $extra_args
         * @return array list of invalid $assoc_args keys to unset
         */
        private function validate_args($args, $assoc_args, $extra_args)
        {
        }
        /**
         * Invoke the subcommand with the supplied arguments.
         * Given a --prompt argument, interactively request input
         * from the end user.
         *
         * @param array $args
         * @param array $assoc_args
         */
        public function invoke($args, $assoc_args, $extra_args)
        {
        }
        /**
         * Get an array of parameter names, by merging the command-specific and the
         * global parameters.
         *
         * @param array  $spec Optional. Specification of the current command.
         *
         * @return array Array of parameter names
         */
        private function get_parameters($spec = array())
        {
        }
    }
    /**
     * Adds a command namespace without actual functionality.
     *
     * This is meant to provide the means to attach meta information to a namespace
     * when there's no actual command needed.
     *
     * In case a real command gets registered for the same name, it replaces the
     * command namespace.
     *
     * @package WP_CLI
     */
    class CommandNamespace extends \WP_CLI\Dispatcher\CompositeCommand
    {
        /**
         * Show the usage for all subcommands contained
         * by the composite command.
         */
        public function show_usage()
        {
        }
    }
    /**
     * The root node in the command tree.
     *
     * @package WP_CLI
     */
    class RootCommand extends \WP_CLI\Dispatcher\CompositeCommand
    {
        public function __construct()
        {
        }
        /**
         * Get the human-readable long description.
         *
         * @return string
         */
        public function get_longdesc()
        {
        }
        /**
         * Find a subcommand registered on the root
         * command.
         *
         * @param array $args
         * @return \WP_CLI\Dispatcher\Subcommand|false
         */
        public function find_subcommand(&$args)
        {
        }
    }
    /**
     * Controls whether adding of a command should be completed or not.
     *
     * This is needed because we can't reliably pass scalar values by reference
     * through the hooks mechanism. An object is always passed by reference.
     *
     * @package WP_CLI
     */
    final class CommandAddition
    {
        /**
         * Whether the command addition was aborted or not.
         *
         * @var bool
         */
        private $abort = false;
        /**
         * Reason for which the addition was aborted.
         *
         * @var string
         */
        private $reason = '';
        /**
         * Abort the current command addition.
         *
         * @param string $reason Reason as to why the addition was aborted.
         */
        public function abort($reason = '')
        {
        }
        /**
         * Check whether the command addition was aborted.
         *
         * @return bool
         */
        public function was_aborted()
        {
        }
        /**
         * Get the reason as to why the addition was aborted.
         *
         * @return string
         */
        public function get_reason()
        {
        }
    }
    /**
     * Creates CompositeCommand or Subcommand instances.
     *
     * @package WP_CLI
     */
    class CommandFactory
    {
        // Cache of file contents, indexed by filename. Only used if opcache.save_comments is disabled.
        private static $file_contents = array();
        /**
         * Create a new CompositeCommand (or Subcommand if class has __invoke())
         *
         * @param string $name Represents how the command should be invoked
         * @param string $callable A subclass of WP_CLI_Command, a function, or a closure
         * @param mixed $parent The new command's parent Composite (or Root) command
         */
        public static function create($name, $callable, $parent)
        {
        }
        /**
         * Clear the file contents cache.
         */
        public static function clear_file_contents_cache()
        {
        }
        /**
         * Create a new Subcommand instance.
         *
         * @param mixed $parent The new command's parent Composite command
         * @param string|bool $name Represents how the command should be invoked.
         * If false, will be determined from the documented subject, represented by `$reflection`.
         * @param mixed $callable A callable function or closure, or class name and method
         * @param object $reflection Reflection instance, for doc parsing
         * @param string $class A subclass of WP_CLI_Command
         * @param string $method Class method to be called upon invocation.
         */
        private static function create_subcommand($parent, $name, $callable, $reflection)
        {
        }
        /**
         * Create a new Composite command instance.
         *
         * @param mixed $parent The new command's parent Root or Composite command
         * @param string $name Represents how the command should be invoked
         * @param mixed $callable
         */
        private static function create_composite_command($parent, $name, $callable)
        {
        }
        /**
         * Create a new command namespace instance.
         *
         * @param mixed $parent The new namespace's parent Root or Composite command.
         * @param string $name Represents how the command should be invoked
         * @param mixed $callable
         */
        private static function create_namespace($parent, $name, $callable)
        {
        }
        /**
         * Check whether a method is actually callable.
         *
         * @param ReflectionMethod $method
         * @return bool
         */
        private static function is_good_method($method)
        {
        }
        /**
         * Gets the document comment. Caters for PHP directive `opcache.save comments` being disabled.
         *
         * @param ReflectionMethod|ReflectionClass|ReflectionFunction $reflection Reflection instance.
         * @return string|false|null Doc comment string if any, false if none (same as `Reflection*::getDocComment()`), null if error.
         */
        private static function get_doc_comment($reflection)
        {
        }
        /**
         * Returns the last doc comment if any in `$content`.
         *
         * @param string $content The content, which should end at the class or function declaration.
         * @return string|bool The last doc comment if any, or false if none.
         */
        private static function extract_last_doc_comment($content)
        {
        }
    }
}
namespace WP_CLI {
    /**
     * Escape route for not doing anything.
     */
    final class NoOp
    {
        public function __set($key, $value)
        {
        }
        public function __call($method, $args)
        {
        }
    }
    /**
     * Handles file- and runtime-based configuration values.
     *
     * @package WP_CLI
     */
    class Configurator
    {
        /**
         * @var array $spec Configurator argument specification.
         */
        private $spec;
        /**
         * @var array $config Values for keys defined in Configurator spec.
         */
        private $config = array();
        /**
         * @var array $extra_config Extra config values not specified in spec.
         */
        private $extra_config = array();
        /**
         * @var array $aliases Any aliases defined in config files.
         */
        private $aliases = array();
        /**
         * @var string ALIAS_REGEX Regex pattern used to define an alias
         */
        const ALIAS_REGEX = '^@[A-Za-z0-9-_\\.\\-]+$';
        /**
         * @var array ALIAS_SPEC Arguments that can be used in an alias
         */
        private static $alias_spec = array('user', 'url', 'path', 'ssh', 'http');
        /**
         * @param string $path Path to config spec file.
         */
        public function __construct($path)
        {
        }
        /**
         * Get declared configuration values as an array.
         *
         * @return array
         */
        public function to_array()
        {
        }
        /**
         * Get configuration specification, i.e. list of accepted keys.
         *
         * @return array
         */
        public function get_spec()
        {
        }
        /**
         * Get any aliases defined in config files.
         *
         * @return array
         */
        public function get_aliases()
        {
        }
        /**
         * Splits a list of arguments into positional, associative and config.
         *
         * @param array(string)
         * @return array(array)
         */
        public function parse_args($arguments)
        {
        }
        /**
         * Splits positional args from associative args.
         *
         * @param array
         * @return array(array)
         */
        public static function extract_assoc($arguments)
        {
        }
        /**
         * Separate runtime parameters from command-specific parameters.
         *
         * @param array $mixed_args
         * @return array
         */
        private function unmix_assoc_args($mixed_args, $global_assoc = array(), $local_assoc = array())
        {
        }
        /**
         * Handle turning an $assoc_arg into a runtime arg.
         */
        private function assoc_arg_to_runtime_config($key, $value, &$runtime_config)
        {
        }
        /**
         * Load a YAML file of parameters into scope.
         *
         * @param string $path Path to YAML file.
         */
        public function merge_yml($path, $current_alias = null)
        {
        }
        /**
         * Merge an array of values into the configurator config.
         *
         * @param array $config
         */
        public function merge_array($config)
        {
        }
        /**
         * Load values from a YAML file.
         *
         * @param string $yml_file Path to the YAML file
         * @return array $config Declared configuration values
         */
        private static function load_yml($yml_file)
        {
        }
        /**
         * Conform a variable to an array.
         *
         * @param mixed $val A string or an array
         */
        private static function arrayify(&$val)
        {
        }
        /**
         * Make a path absolute.
         *
         * @param string $path Path to file.
         * @param string $base Base path to prepend.
         */
        private static function absolutize(&$path, $base)
        {
        }
    }
    /**
     * Run a system process, and learn what happened.
     */
    class Process
    {
        /**
         * @var string The full command to execute by the system.
         */
        private $command;
        /**
         * @var string|null The path of the working directory for the process or NULL if not specified (defaults to current working directory).
         */
        private $cwd;
        /**
         * @var array Environment variables to set when running the command.
         */
        private $env;
        /**
         * @var array Descriptor spec for `proc_open()`.
         */
        private static $descriptors = array(0 => STDIN, 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        /**
         * @var bool Whether to log run time info or not.
         */
        public static $log_run_times = false;
        /**
         * @var array Array of process run time info, keyed by process command, each a 2-element array containing run time and run count.
         */
        public static $run_times = array();
        /**
         * @param string $command Command to execute.
         * @param string $cwd Directory to execute the command in.
         * @param array $env Environment variables to set when running the command.
         *
         * @return Process
         */
        public static function create($command, $cwd = null, $env = array())
        {
        }
        private function __construct()
        {
        }
        /**
         * Run the command.
         *
         * @return ProcessRun
         */
        public function run()
        {
        }
        /**
         * Run the command, but throw an Exception on error.
         *
         * @return ProcessRun
         */
        public function run_check()
        {
        }
        /**
         * Run the command, but throw an Exception on error.
         * Same as `run_check()` above, but checks the correct stderr.
         *
         * @return ProcessRun
         */
        public function run_check_stderr()
        {
        }
    }
    /**
     * Parse command attributes from its PHPdoc.
     * Used to determine execution characteristics (arguments, etc.).
     */
    class DocParser
    {
        /**
         * @var string $docComment PHPdoc command for the command.
         */
        protected $doc_comment;
        /**
         * @param string $doc_comment
         */
        public function __construct($doc_comment)
        {
        }
        /**
         * Remove unused cruft from PHPdoc comment.
         *
         * @param string $comment PHPdoc comment.
         * @return string
         */
        private static function remove_decorations($comment)
        {
        }
        /**
         * Get the command's short description (e.g. summary).
         *
         * @return string
         */
        public function get_shortdesc()
        {
        }
        /**
         * Get the command's full description
         *
         * @return string
         */
        public function get_longdesc()
        {
        }
        /**
         * Get the value for a given tag (e.g. "@alias" or "@subcommand")
         *
         * @param string $name Name for the tag, without '@'
         * @return string
         */
        public function get_tag($name)
        {
        }
        /**
         * Get the command's synopsis.
         *
         * @return string
         */
        public function get_synopsis()
        {
        }
        /**
         * Get the description for a given argument.
         *
         * @param string $name Argument's doc name.
         * @return string
         */
        public function get_arg_desc($name)
        {
        }
        /**
         * Get the arguments for a given argument.
         *
         * @param string $name Argument's doc name.
         * @return mixed|null
         */
        public function get_arg_args($name)
        {
        }
        /**
         * Get the description for a given parameter.
         *
         * @param string $key Parameter's key.
         * @return string
         */
        public function get_param_desc($key)
        {
        }
        /**
         * Get the arguments for a given parameter.
         *
         * @param string $key Parameter's key.
         * @return mixed|null
         */
        public function get_param_args($key)
        {
        }
        /**
         * Get the args for an arg or param
         *
         * @param string $regex Pattern to match against
         * @return array|null Interpreted YAML document, or null.
         */
        private function get_arg_or_param_args($regex)
        {
        }
    }
}
namespace WP_CLI\Bootstrap {
    /**
     * Class RunnerInstance.
     *
     * Convenience class for steps that make use of the `WP_CLI\Runner` object.
     *
     * @package WP_CLI\Bootstrap
     */
    final class RunnerInstance
    {
        /**
         * Return an instance of the `WP_CLI\Runner` object.
         *
         * Includes necessary class files first as needed.
         *
         * @return \WP_CLI\Runner
         */
        public function __invoke()
        {
        }
    }
    /**
     * Interface BootstrapStep.
     *
     * Represents a single bootstrapping step that can be processed.
     *
     * @package WP_CLI\Bootstrap
     */
    interface BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state);
    }
    /**
     * Class RegisterFrameworkCommands.
     *
     * Register the commands that are directly included with the framework.
     *
     * @package WP_CLI\Bootstrap
     */
    final class RegisterFrameworkCommands implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
    }
    /**
     * Class RegisterDeferredCommands.
     *
     * Registers the deferred commands that for which no parent was registered yet.
     * This is necessary, because we can have sub-commands that have no direct
     * parent, like `wp network meta`.
     *
     * @package WP_CLI\Bootstrap
     */
    final class RegisterDeferredCommands implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
        /**
         * Add deferred commands that are still waiting to be processed.
         */
        public function add_deferred_commands()
        {
        }
    }
    /**
     * Class IncludeFrameworkAutoloader.
     *
     * Loads the framework autoloader through an autolaoder separate from the
     * Composer one, to avoid coupling the loading of the framework with bundled
     * commands.
     *
     * This only contains classes for the framework.
     *
     * @package WP_CLI\Bootstrap
     */
    final class IncludeFrameworkAutoloader implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
    }
    /**
     * Abstract class AutoloaderStep.
     *
     * Abstract base class for steps that include an autoloader.
     *
     * @package WP_CLI\Bootstrap
     */
    abstract class AutoloaderStep implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Store state for subclasses to have access.
         *
         * @var BootstrapState
         */
        protected $state;
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
        /**
         * Get the name of the custom vendor folder as set in `composer.json`.
         *
         * @return string|false Name of the custom vendor folder or false if none.
         */
        protected function get_custom_vendor_folder()
        {
        }
        /**
         * Handle the failure to find an autoloader.
         *
         * @return void
         */
        protected function handle_failure()
        {
        }
        /**
         * Get the autoloader paths to scan for an autoloader.
         *
         * @return string[]|false Array of strings with autoloader paths, or false
         *                        to skip.
         */
        protected abstract function get_autoloader_paths();
    }
    /**
     * Class IncludePackageAutoloader.
     *
     * Loads the package autoloader that includes all the external packages.
     *
     * @package WP_CLI\Bootstrap
     */
    final class IncludePackageAutoloader extends \WP_CLI\Bootstrap\AutoloaderStep
    {
        /**
         * Get the autoloader paths to scan for an autoloader.
         *
         * @return string[]|false Array of strings with autoloader paths, or false
         *                        to skip.
         */
        protected function get_autoloader_paths()
        {
        }
        /**
         * Handle the failure to find an autoloader.
         *
         * @return void
         */
        protected function handle_failure()
        {
        }
    }
    /**
     * Class InitializeLogger.
     *
     * Initialize the logger through the `WP_CLI\Runner` object.
     *
     * @package WP_CLI\Bootstrap
     */
    final class InitializeLogger implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
        /**
         * Load the class declarations for the loggers.
         */
        private function declare_loggers()
        {
        }
    }
    /**
     * Class DefineProtectedCommands.
     *
     * Define the commands that are "protected", meaning that they shouldn't be able
     * to break due to extension code.
     *
     * @package WP_CLI\Bootstrap
     */
    final class DefineProtectedCommands implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
        /**
         * Get the list of protected commands.
         *
         * @return array
         */
        private function get_protected_commands()
        {
        }
        /**
         * Get the current command as a string.
         *
         * @return string Current command to be executed.
         */
        private function get_current_command()
        {
        }
    }
    /**
     * Class BootstrapState.
     *
     * Represents the state that is passed from one bootstrap step to the next.
     *
     * @package WP_CLI\Bootstrap
     *
     * Maintain BC: Changing the method names in this class breaks autoload interactions between Phar
     * & framework/commands you use outside of Phar (like when running the Phar WP inside of a command folder).
     * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
     */
    class BootstrapState
    {
        /**
         * Whether the command currently being run is "protected".
         *
         * This means that the command should not be allowed to break due to
         * extension code.
         */
        const IS_PROTECTED_COMMAND = 'is_protected_command';
        /**
         * Internal storage of the state values.
         *
         * @var array
         */
        private $state = array();
        /**
         * Get the state value for a given key.
         *
         * @param string $key      Key to get the state from.
         * @param mixed  $fallback Fallback value to use if the key is not defined.
         *
         * @return mixed
         */
        public function getValue($key, $fallback = null)
        {
        }
        /**
         * Set the state value for a given key.
         *
         * @param string $key   Key to set the state for.
         * @param mixed  $value Value to set the state for the given key to.
         *
         * @return void
         */
        public function setValue($key, $value)
        {
        }
    }
    /**
     * Class LoadUtilityFunctions.
     *
     * Loads the functions available through `WP_CLI\Utils`.
     *
     * @package WP_CLI\Bootstrap
     */
    final class LoadUtilityFunctions implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
    }
    /**
     * Class IncludeFallbackAutoloader.
     *
     * Loads the fallback autoloader that is provided through the `composer.json`
     * file.
     *
     * @package WP_CLI\Bootstrap
     */
    final class IncludeFallbackAutoloader extends \WP_CLI\Bootstrap\AutoloaderStep
    {
        /**
         * Get the autoloader paths to scan for an autoloader.
         *
         * @return string[]|false Array of strings with autoloader paths, or false
         *                        to skip.
         */
        protected function get_autoloader_paths()
        {
        }
    }
    /**
     * Class LoadDispatcher.
     *
     * Loads the dispatcher that will dispatch command names to file locations.
     *
     * @package WP_CLI\Bootstrap
     */
    final class LoadDispatcher implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
    }
    /**
     * Class DeclareMainClass.
     *
     * Declares the main `WP_CLI` class.
     *
     * @package WP_CLI\Bootstrap
     */
    final class DeclareMainClass implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
    }
    /**
     * Class LaunchRunner.
     *
     * Kick off the Runner object that starts the actual commands.
     *
     * @package WP_CLI\Bootstrap
     */
    final class LaunchRunner implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
    }
    /**
     * Class DeclareAbstractBaseCommand.
     *
     * Declares the abstract `WP_CLI_Command` base class.
     *
     * @package WP_CLI\Bootstrap
     */
    final class DeclareAbstractBaseCommand implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
    }
    /**
     * Class InitializeColorization.
     *
     * Initialize the colorization through the `WP_CLI\Runner` object.
     *
     * @package WP_CLI\Bootstrap
     */
    final class InitializeColorization implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
    }
    /**
     * Class ConfigureRunner.
     *
     * Initialize the configuration for the `WP_CLI\Runner` object.
     *
     * @package WP_CLI\Bootstrap
     */
    final class ConfigureRunner implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
    }
    /**
     * Class LoadRequiredCommand.
     *
     * Loads a command that was passed through the `--require=<command>` option.
     *
     * @package WP_CLI\Bootstrap
     */
    final class LoadRequiredCommand implements \WP_CLI\Bootstrap\BootstrapStep
    {
        /**
         * Process this single bootstrapping step.
         *
         * @param BootstrapState $state Contextual state to pass into the step.
         *
         * @return BootstrapState Modified state to pass to the next step.
         */
        public function process(\WP_CLI\Bootstrap\BootstrapState $state)
        {
        }
    }
}
namespace WP_CLI {
    /**
     * Results of an executed command.
     */
    class ProcessRun
    {
        /**
         * @var string The full command executed by the system.
         */
        public $command;
        /**
         * @var string Captured output from the process' STDOUT.
         */
        public $stdout;
        /**
         * @var string Captured output from the process' STDERR.
         */
        public $stderr;
        /**
         * @var string|null The path of the working directory for the process or NULL if not specified (defaults to current working directory).
         */
        public $cwd;
        /**
         * @var array Environment variables set for this process.
         */
        public $env;
        /**
         * @var int Exit code of the process.
         */
        public $return_code;
        /**
         * @var float The run time of the process.
         */
        public $run_time;
        /**
         * @var array $props Properties of executed command.
         */
        public function __construct($props)
        {
        }
        /**
         * Return properties of executed command as a string.
         *
         * @return string
         */
        public function __toString()
        {
        }
    }
    /**
     * Performs the execution of a command.
     *
     * @package WP_CLI
     */
    class Runner
    {
        private $global_config_path;
        private $project_config_path;
        private $config;
        private $extra_config;
        private $alias;
        private $aliases;
        private $arguments;
        private $assoc_args;
        private $runtime_config;
        private $colorize = false;
        private $early_invoke = array();
        private $global_config_path_debug;
        private $project_config_path_debug;
        private $required_files;
        public function __get($key)
        {
        }
        /**
         * Register a command for early invocation, generally before WordPress loads.
         *
         * @param string $when Named execution hook
         * @param WP_CLI\Dispatcher\Subcommand $command
         */
        public function register_early_invoke($when, $command)
        {
        }
        /**
         * Perform the early invocation of a command.
         *
         * @param string $when Named execution hook
         */
        private function do_early_invoke($when)
        {
        }
        /**
         * Get the path to the global configuration YAML file.
         *
         * @return string|false
         */
        public function get_global_config_path()
        {
        }
        /**
         * Get the path to the project-specific configuration
         * YAML file.
         * wp-cli.local.yml takes priority over wp-cli.yml.
         *
         * @return string|false
         */
        public function get_project_config_path()
        {
        }
        /**
         * Get the path to the packages directory
         *
         * @return string
         */
        public function get_packages_dir_path()
        {
        }
        /**
         * Attempts to find the path to the WP installation inside index.php
         *
         * @param string $index_path
         * @return string|false
         */
        private static function extract_subdir_path($index_path)
        {
        }
        /**
         * Find the directory that contains the WordPress files.
         * Defaults to the current working dir.
         *
         * @return string An absolute path
         */
        private function find_wp_root()
        {
        }
        /**
         * Set WordPress root as a given path.
         *
         * @param string $path
         */
        private static function set_wp_root($path)
        {
        }
        /**
         * Guess which URL context WP-CLI has been invoked under.
         *
         * @param array $assoc_args
         * @return string|false
         */
        private static function guess_url($assoc_args)
        {
        }
        private function cmd_starts_with($prefix)
        {
        }
        /**
         * Given positional arguments, find the command to execute.
         *
         * @param array $args
         * @return array|string Command, args, and path on success; error message on failure
         */
        public function find_command_to_run($args)
        {
        }
        /**
         * Find the WP-CLI command to run given arguments, and invoke it.
         *
         * @param array $args        Positional arguments including command name
         * @param array $assoc_args  Associative arguments for the command.
         * @param array $options     Configuration options for the function.
         */
        public function run_command($args, $assoc_args = array(), $options = array())
        {
        }
        /**
         * Show synopsis if the called command is a composite command
         */
        public function show_synopsis_if_composite_command()
        {
        }
        private function run_command_and_exit($help_exit_warning = '')
        {
        }
        /**
         * Perform a command against a remote server over SSH (or a container using
         * scheme of "docker" or "docker-compose").
         *
         * @param string $connection_string Passed connection string.
         * @return void
         */
        private function run_ssh_command($connection_string)
        {
        }
        /**
         * Generate a shell command from the parsed connection string.
         *
         * @param array  $bits       Parsed connection string.
         * @param string $wp_command WP-CLI command to run.
         * @return string
         */
        private function generate_ssh_command($bits, $wp_command)
        {
        }
        /**
         * Check whether a given command is disabled by the config
         *
         * @return bool
         */
        public function is_command_disabled($command)
        {
        }
        /**
         * Returns wp-config.php code, skipping the loading of wp-settings.php
         *
         * @return string
         */
        public function get_wp_config_code()
        {
        }
        /**
         * Transparently convert deprecated syntaxes
         *
         * @param array $args
         * @param array $assoc_args
         * @return array
         */
        private static function back_compat_conversions($args, $assoc_args)
        {
        }
        /**
         * Whether or not the output should be rendered in color
         *
         * @return bool
         */
        public function in_color()
        {
        }
        public function init_colorization()
        {
        }
        public function init_logger()
        {
        }
        public function get_required_files()
        {
        }
        /**
         * Do WordPress core files exist?
         *
         * @return bool
         */
        private function wp_exists()
        {
        }
        /**
         * Are WordPress core files readable?
         *
         * @return bool
         */
        private function wp_is_readable()
        {
        }
        private function check_wp_version()
        {
        }
        public function init_config()
        {
        }
        private function check_root()
        {
        }
        private function run_alias_group($aliases)
        {
        }
        private function set_alias($alias)
        {
        }
        public function start()
        {
        }
        /**
         * Load WordPress, if it hasn't already been loaded
         */
        public function load_wordpress()
        {
        }
        private static function fake_current_site_blog($url_parts)
        {
        }
        /**
         * Called after wp-config.php is eval'd, to potentially reset `--url`
         */
        private function maybe_update_url_from_domain_constant()
        {
        }
        /**
         * Set up hooks meant to run during the WordPress bootstrap process
         */
        private function setup_bootstrap_hooks()
        {
        }
        /**
         * Set up the filters to skip the loaded plugins
         */
        private function setup_skip_plugins_filters()
        {
        }
        /**
         * Set up the filters to skip the loaded theme
         */
        public function action_setup_theme_wp_cli_skip_themes()
        {
        }
        /**
         * Whether or not this WordPress installation is multisite.
         *
         * For use after wp-config.php has loaded, but before the rest of WordPress
         * is loaded.
         */
        private function is_multisite()
        {
        }
        /**
         * Error handler for `wp_die()` when the command is help to try to trap errors (db connection failure in particular) during WordPress load.
         */
        public function help_wp_die_handler($message)
        {
        }
        /**
         * Check whether there's a WP-CLI update available, and suggest update if so.
         */
        private function auto_check_update()
        {
        }
        /**
         * Get a suggestion on similar (sub)commands when the user entered an
         * unknown (sub)command.
         *
         * @param string           $entry        User entry that didn't match an
         *                                       existing command.
         * @param CompositeCommand $root_command Root command to start search for
         *                                       suggestions at.
         *
         * @return string Suggestion that fits the user entry, or an empty string.
         */
        private function get_subcommand_suggestion($entry, \WP_CLI\Dispatcher\CompositeCommand $root_command = null)
        {
        }
        /**
         * Recursive method to enumerate all known commands.
         *
         * @param CompositeCommand $command Composite command to recurse over.
         * @param array            $list    Reference to list accumulating results.
         * @param string           $parent  Parent command to use as prefix.
         */
        private function enumerate_commands(\WP_CLI\Dispatcher\CompositeCommand $command, array &$list, $parent = '')
        {
        }
        /**
         * Enables (almost) full PHP error reporting to stderr.
         */
        private function enable_error_reporting()
        {
        }
    }
    /**
     * Class Autoloader.
     *
     * This is a custom autoloader to replace the functionality that we would
     * normally get through the autoloader generated by Composer.
     *
     * We need this separate autoloader for the bootstrapping process, which happens
     * before the Composer autoloader(s) could be loaded.
     *
     * @package WP_CLI
     */
    class Autoloader
    {
        /**
         * Array containing the registered namespace structures
         *
         * @var array
         */
        protected $namespaces = array();
        /**
         * Destructor for the Autoloader class.
         *
         * The destructor automatically unregisters the autoload callback function
         * with the SPL autoload system.
         */
        public function __destruct()
        {
        }
        /**
         * Registers the autoload callback with the SPL autoload system.
         */
        public function register()
        {
        }
        /**
         * Unregisters the autoload callback with the SPL autoload system.
         */
        public function unregister()
        {
        }
        /**
         * Add a specific namespace structure with our custom autoloader.
         *
         * @param string  $root        Root namespace name.
         * @param string  $base_dir    Directory containing the class files.
         * @param string  $prefix      Prefix to be added before the class.
         * @param string  $suffix      Suffix to be added after the class.
         * @param boolean $lowercase   Whether the class should be changed to
         *                             lowercase.
         * @param boolean $underscores Whether the underscores should be changed to
         *                             hyphens.
         *
         * @return self
         */
        public function add_namespace($root, $base_dir, $prefix = '', $suffix = '.php', $lowercase = false, $underscores = false)
        {
        }
        /**
         * The autoload function that gets registered with the SPL Autoloader
         * system.
         *
         * @param string $class The class that got requested by the spl_autoloader.
         */
        public function autoload($class)
        {
        }
        /**
         * Normalize a namespace root.
         *
         * @param string $root Namespace root that needs to be normalized.
         *
         * @return string Normalized namespace root.
         */
        protected function normalize_root($root)
        {
        }
        /**
         * Remove a leading backslash from a string.
         *
         * @param string $string String to remove the leading backslash from.
         *
         * @return string Modified string.
         */
        protected function remove_leading_backslash($string)
        {
        }
        /**
         * Make sure a string ends with a trailing backslash.
         *
         * @param string $string String to check the trailing backslash of.
         *
         * @return string Modified string.
         */
        protected function add_trailing_backslash($string)
        {
        }
        /**
         * Make sure a string ends with a trailing slash.
         *
         * @param string $string String to check the trailing slash of.
         *
         * @return string Modified string.
         */
        protected function add_trailing_slash($string)
        {
        }
    }
    /**
     * Reads/writes to a filesystem cache
     */
    class FileCache
    {
        /**
         * @var string cache path
         */
        protected $root;
        /**
         * @var bool
         */
        protected $enabled = true;
        /**
         * @var int files time to live
         */
        protected $ttl;
        /**
         * @var int max total size
         */
        protected $max_size;
        /**
         * @var string key allowed chars (regex class)
         */
        protected $whitelist;
        /**
         * @param string $cache_dir  location of the cache
         * @param int    $ttl        cache files default time to live (expiration)
         * @param int    $max_size   max total cache size
         * @param string $whitelist  List of characters that are allowed in path names (used in a regex character class)
         */
        public function __construct($cache_dir, $ttl, $max_size, $whitelist = 'a-z0-9._-')
        {
        }
        /**
         * Cache is enabled
         *
         * @return bool
         */
        public function is_enabled()
        {
        }
        /**
         * Cache root
         *
         * @return string
         */
        public function get_root()
        {
        }
        /**
         * Check if a file is in cache and return its filename
         *
         * @param string $key cache key
         * @param int    $ttl time to live
         * @return bool|string filename or false
         */
        public function has($key, $ttl = null)
        {
        }
        /**
         * Write to cache file
         *
         * @param string $key      cache key
         * @param string $contents file contents
         * @return bool
         */
        public function write($key, $contents)
        {
        }
        /**
         * Read from cache file
         *
         * @param string $key cache key
         * @param int    $ttl time to live
         * @return bool|string file contents or false
         */
        public function read($key, $ttl = null)
        {
        }
        /**
         * Copy a file into the cache
         *
         * @param string $key    cache key
         * @param string $source source filename
         * @return bool
         */
        public function import($key, $source)
        {
        }
        /**
         * Copy a file out of the cache
         *
         * @param string $key    cache key
         * @param string $target target filename
         * @param int    $ttl    time to live
         * @return bool
         */
        public function export($key, $target, $ttl = null)
        {
        }
        /**
         * Remove file from cache
         *
         * @param string $key cache key
         * @return bool
         */
        public function remove($key)
        {
        }
        /**
         * Clean cache based on time to live and max size
         *
         * @return bool
         */
        public function clean()
        {
        }
        /**
         * Remove all cached files.
         *
         * @return bool
         */
        public function clear()
        {
        }
        /**
         * Remove all cached files except for the newest version of one.
         *
         * @return bool
         */
        public function prune()
        {
        }
        /**
         * Ensure directory exists
         *
         * @param string $dir directory
         * @return bool
         */
        protected function ensure_dir_exists($dir)
        {
        }
        /**
         * Prepare cache write
         *
         * @param string $key cache key
         * @return bool|string filename or false
         */
        protected function prepare_write($key)
        {
        }
        /**
         * Validate cache key
         *
         * @param string $key cache key
         * @return string relative filename
         */
        protected function validate_key($key)
        {
        }
        /**
         * Filename from key
         *
         * @param string $key
         * @return string filename
         */
        protected function filename($key)
        {
        }
        /**
         * Get a Finder that iterates in cache root only the files
         *
         * @return Finder
         */
        protected function get_finder()
        {
        }
    }
    /**
     * Manage caching with whitelisting
     *
     * @package WP_CLI
     */
    class WpHttpCacheManager
    {
        /**
         * @var array map whitelisted urls to keys and ttls
         */
        protected $whitelist = array();
        /**
         * @var FileCache
         */
        protected $cache;
        /**
         * @param FileCache $cache
         */
        public function __construct(\WP_CLI\FileCache $cache)
        {
        }
        /**
         * short circuit wp http api with cached file
         */
        public function filter_pre_http_request($response, $args, $url)
        {
        }
        /**
         * cache wp http api downloads
         *
         * @param array $response
         * @param array $args
         * @param string $url
         */
        public function filter_http_response($response, $args, $url)
        {
        }
        /**
         * whitelist a package url
         *
         * @param string $url
         * @param string $group   package group (themes, plugins, ...)
         * @param string $slug    package slug
         * @param string $version package version
         * @param int    $ttl
         */
        public function whitelist_package($url, $group, $slug, $version, $ttl = null)
        {
        }
        /**
         * whitelist a url
         *
         * @param string $url
         * @param string $key
         * @param int    $ttl
         */
        public function whitelist_url($url, $key = null, $ttl = null)
        {
        }
        /**
         * check if url is whitelisted
         *
         * @param string $url
         * @return bool
         */
        public function is_whitelisted($url)
        {
        }
    }
    class Completions
    {
        private $words;
        private $opts = array();
        public function __construct($line)
        {
        }
        private function get_command($words)
        {
        }
        private function get_global_parameters()
        {
        }
        private function add($opt)
        {
        }
        public function render()
        {
        }
    }
}
namespace {
    /**
     * Base class for WP-CLI commands
     *
     * @package wp-cli
     */
    abstract class WP_CLI_Command
    {
        public function __construct()
        {
        }
    }
    /**
     * Review current WP-CLI info, check for updates, or see defined aliases.
     *
     * ## EXAMPLES
     *
     *     # Display the version currently installed.
     *     $ wp cli version
     *     WP-CLI 0.24.1
     *
     *     # Check for updates to WP-CLI.
     *     $ wp cli check-update
     *     Success: WP-CLI is at the latest version.
     *
     *     # Update WP-CLI to the latest stable release.
     *     $ wp cli update
     *     You have version 0.24.0. Would you like to update to 0.24.1? [y/n] y
     *     Downloading from https://github.com/wp-cli/wp-cli/releases/download/v0.24.1/wp-cli-0.24.1.phar...
     *     New version works. Proceeding to replace.
     *     Success: Updated WP-CLI to 0.24.1.
     *
     *     # Clear the internal WP-CLI cache.
     *     $ wp cli cache clear
     *     Success: Cache cleared.
     *
     * @when before_wp_load
     */
    class CLI_Command extends \WP_CLI_Command
    {
        private function command_to_array($command)
        {
        }
        /**
         * Print WP-CLI version.
         *
         * ## EXAMPLES
         *
         *     # Display CLI version.
         *     $ wp cli version
         *     WP-CLI 0.24.1
         */
        public function version()
        {
        }
        /**
         * Print various details about the WP-CLI environment.
         *
         * Helpful for diagnostic purposes, this command shares:
         *
         * * OS information.
         * * Shell information.
         * * PHP binary used.
         * * PHP binary version.
         * * php.ini configuration file used (which is typically different than web).
         * * WP-CLI root dir: where WP-CLI is installed (if non-Phar install).
         * * WP-CLI global config: where the global config YAML file is located.
         * * WP-CLI project config: where the project config YAML file is located.
         * * WP-CLI version: currently installed version.
         *
         * See [config docs](https://wp-cli.org/config/) for more details on global
         * and project config YAML files.
         *
         * ## OPTIONS
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: list
         * options:
         *   - list
         *   - json
         * ---
         *
         * ## EXAMPLES
         *
         *     # Display various data about the CLI environment.
         *     $ wp cli info
         *     OS:  Linux 4.10.0-42-generic #46~16.04.1-Ubuntu SMP Mon Dec 4 15:57:59 UTC 2017 x86_64
         *     Shell:   /usr/bin/zsh
         *     PHP binary:  /usr/bin/php
         *     PHP version: 7.1.12-1+ubuntu16.04.1+deb.sury.org+1
         *     php.ini used:    /etc/php/7.1/cli/php.ini
         *     WP-CLI root dir:    phar://wp-cli.phar
         *     WP-CLI packages dir:    /home/person/.wp-cli/packages/
         *     WP-CLI global config:
         *     WP-CLI project config:
         *     WP-CLI version: 1.5.0
         */
        public function info($_, $assoc_args)
        {
        }
        /**
         * Check to see if there is a newer version of WP-CLI available.
         *
         * Queries the Github releases API. Returns available versions if there are
         * updates available, or success message if using the latest release.
         *
         * ## OPTIONS
         *
         * [--patch]
         * : Only list patch updates.
         *
         * [--minor]
         * : Only list minor updates.
         *
         * [--major]
         * : Only list major updates.
         *
         * [--field=<field>]
         * : Prints the value of a single field for each update.
         *
         * [--fields=<fields>]
         * : Limit the output to specific object fields. Defaults to version,update_type,package_url.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: table
         * options:
         *   - table
         *   - csv
         *   - json
         *   - count
         *   - yaml
         * ---
         *
         * ## EXAMPLES
         *
         *     # Check for update.
         *     $ wp cli check-update
         *     Success: WP-CLI is at the latest version.
         *
         *     # Check for update and new version is available.
         *     $ wp cli check-update
         *     +---------+-------------+-------------------------------------------------------------------------------+
         *     | version | update_type | package_url                                                                   |
         *     +---------+-------------+-------------------------------------------------------------------------------+
         *     | 0.24.1  | patch       | https://github.com/wp-cli/wp-cli/releases/download/v0.24.1/wp-cli-0.24.1.phar |
         *     +---------+-------------+-------------------------------------------------------------------------------+
         *
         * @subcommand check-update
         */
        public function check_update($_, $assoc_args)
        {
        }
        /**
         * Update WP-CLI to the latest release.
         *
         * Default behavior is to check the releases API for the newest stable
         * version, and prompt if one is available.
         *
         * Use `--stable` to install or reinstall the latest stable version.
         *
         * Use `--nightly` to install the latest built version of the master branch.
         * While not recommended for production, nightly contains the latest and
         * greatest, and should be stable enough for development and staging
         * environments.
         *
         * Only works for the Phar installation mechanism.
         *
         * ## OPTIONS
         *
         * [--patch]
         * : Only perform patch updates.
         *
         * [--minor]
         * : Only perform minor updates.
         *
         * [--major]
         * : Only perform major updates.
         *
         * [--stable]
         * : Update to the latest stable release. Skips update check.
         *
         * [--nightly]
         * : Update to the latest built version of the master branch. Potentially unstable.
         *
         * [--yes]
         * : Do not prompt for confirmation.
         *
         * ## EXAMPLES
         *
         *     # Update CLI.
         *     $ wp cli update
         *     You have version 0.24.0. Would you like to update to 0.24.1? [y/n] y
         *     Downloading from https://github.com/wp-cli/wp-cli/releases/download/v0.24.1/wp-cli-0.24.1.phar...
         *     New version works. Proceeding to replace.
         *     Success: Updated WP-CLI to 0.24.1.
         */
        public function update($_, $assoc_args)
        {
        }
        /**
         * Returns update information.
         */
        private function get_updates($assoc_args)
        {
        }
        /**
         * Dump the list of global parameters, as JSON or in var_export format.
         *
         * ## OPTIONS
         *
         * [--with-values]
         * : Display current values also.
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: json
         * options:
         *   - var_export
         *   - json
         * ---
         *
         * ## EXAMPLES
         *
         *     # Dump the list of global parameters.
         *     $ wp cli param-dump --format=var_export
         *     array (
         *       'path' =>
         *       array (
         *         'runtime' => '=<path>',
         *         'file' => '<path>',
         *         'synopsis' => '',
         *         'default' => NULL,
         *         'multiple' => false,
         *         'desc' => 'Path to the WordPress files.',
         *       ),
         *       'url' =>
         *       array (
         *
         * @subcommand param-dump
         */
        public function param_dump($_, $assoc_args)
        {
        }
        /**
         * Dump the list of installed commands, as JSON.
         *
         * ## EXAMPLES
         *
         *     # Dump the list of installed commands.
         *     $ wp cli cmd-dump
         *     {"name":"wp","description":"Manage WordPress through the command-line.","longdesc":"\n\n## GLOBAL PARAMETERS\n\n  --path=<path>\n      Path to the WordPress files.\n\n  --ssh=<ssh>\n      Perform operation against a remote server over SSH (or a container using scheme of "docker" or "docker-compose").\n\n  --url=<url>\n      Pretend request came from given URL. In multisite, this argument is how the target site is specified. \n\n  --user=<id|login|email>\n
         *
         * @subcommand cmd-dump
         */
        public function cmd_dump()
        {
        }
        /**
         * Generate tab completion strings.
         *
         * ## OPTIONS
         *
         * --line=<line>
         * : The current command line to be executed.
         *
         * --point=<point>
         * : The index to the current cursor position relative to the beginning of the command.
         *
         * ## EXAMPLES
         *
         *     # Generate tab completion strings.
         *     $ wp cli completions --line='wp eva' --point=100
         *     eval
         *     eval-file
         */
        public function completions($_, $assoc_args)
        {
        }
        /**
         * Get a string representing the type of update being checked for.
         */
        private function get_update_type_str($assoc_args)
        {
        }
        /**
         * Detects if a command exists
         *
         * This commands checks if a command is registered with WP-CLI.
         * If the command is found then it returns with exit status 0.
         * If the command doesn't exist, then it will exit with status 1.
         *
         * ## OPTIONS
         * <command_name>...
         * : The command
         *
         * ## EXAMPLES
         *
         *     # The "site delete" command is registered.
         *     $ wp cli has-command "site delete"
         *     $ echo $?
         *     0
         *
         *     # The "foo bar" command is not registered.
         *     $ wp cli has-command "foo bar"
         *     $ echo $?
         *     1
         *
         * @subcommand has-command
         *
         * @when after_wp_load
         */
        public function has_command($_, $assoc_args)
        {
        }
    }
    /**
     * Manages the internal WP-CLI cache,.
     *
     * ## EXAMPLES
     *
     *     # Remove all cached files.
     *     $ wp cli cache clear
     *     Success: Cache cleared.
     *
     *     # Remove all cached files except for the newest version of each one.
     *     $ wp cli cache prune
     *     Success: Cache pruned.
     *
     * @when before_wp_load
     */
    class CLI_Cache_Command extends \WP_CLI_Command
    {
        /**
         * Clear the internal cache.
         *
         * ## EXAMPLES
         *
         *     $ wp cli cache clear
         *     Success: Cache cleared.
         *
         * @subcommand clear
         */
        public function cache_clear()
        {
        }
        /**
         * Prune the internal cache.
         *
         * Removes all cached files except for the newest version of each one.
         *
         * ## EXAMPLES
         *
         *     $ wp cli cache prune
         *     Success: Cache pruned.
         *
         * @subcommand prune
         */
        public function cache_prune()
        {
        }
    }
    /**
     * Retrieves, sets and updates aliases for WordPress Installations.
     *
     * Aliases are shorthand references to WordPress installs. For instance,
     * `@dev` could refer to a development install and `@prod` could refer to a production install.
     * This command gives you and option to add, update and delete, the registered aliases you have available.
     *
     * ## EXAMPLES
     *
     *     # List alias information.
     *     $ wp cli alias list
     *     list
     *     ---
     *     @all: Run command against every registered alias.
     *     @local:
     *       user: wpcli
     *       path: /Users/wpcli/sites/testsite
     *
     *     # Get alias information.
     *     $ wp cli alias get @dev
     *     ssh: dev@somedeve.env:12345/home/dev/
     *
     *     # Add alias.
     *     $ wp cli alias add prod --set-ssh=login@host --set-path=/path/to/wordpress/install/ --set-user=wpcli
     *     Success: Added '@prod' alias.
     *
     *     # Update alias.
     *     $ wp cli alias update @prod --set-user=newuser --set-path=/new/path/to/wordpress/install/
     *     Success: Updated 'prod' alias.
     *
     *     # Delete alias.
     *     $ wp cli alias delete @prod
     *     Success: Deleted '@prod' alias.
     *
     * @package wp-cli
     * @when    before_wp_load
     */
    class CLI_Alias_Command extends \WP_CLI_Command
    {
        /**
         * List available WP-CLI aliases.
         *
         * ## OPTIONS
         *
         * [--format=<format>]
         * : Render output in a particular format.
         * ---
         * default: yaml
         * options:
         *   - yaml
         *   - json
         *   - var_export
         * ---
         *
         * ## EXAMPLES
         *
         *     # List all available aliases.
         *     $ wp cli alias list
         *     ---
         *     @all: Run command against every registered alias.
         *     @prod:
         *       ssh: runcommand@runcommand.io~/webapps/production
         *     @dev:
         *       ssh: vagrant@192.168.50.10/srv/www/runcommand.dev
         *     @both:
         *       - @prod
         *       - @dev
         *
         * @subcommand list
         */
        public function list_($args, $assoc_args)
        {
        }
        /**
         * Gets the value for an alias.
         *
         * ## OPTIONS
         *
         * <key>
         * : Key for the alias.
         *
         * ## EXAMPLES
         *
         *     # Get alias.
         *     $ wp cli alias get @prod
         *     ssh: dev@somedeve.env:12345/home/dev/
         */
        public function get($args, $assoc_args)
        {
        }
        /**
         * Creates an alias.
         *
         * ## OPTIONS
         *
         * <key>
         * : Key for the alias.
         *
         * [--set-user=<user>]
         * : Set user for alias.
         *
         * [--set-url=<url>]
         * : Set url for alias.
         *
         * [--set-path=<path>]
         * : Set path for alias.
         *
         * [--set-ssh=<ssh>]
         * : Set ssh for alias.
         *
         * [--set-http=<http>]
         * : Set http for alias.
         *
         * [--grouping=<grouping>]
         * : For grouping multiple aliases.
         *
         * [--config=<config>]
         * : Config file to be considered for operations.
         * ---
         * default: global
         * options:
         *   - global
         *   - project
         * ---
         *
         * ## EXAMPLES
         *
         *     # Add alias to global config.
         *     $ wp cli alias add @prod  --set-ssh=login@host --set-path=/path/to/wordpress/install/ --set-user=wpcli
         *     Success: Added '@prod' alias.
         *
         *     # Add alias to project config.
         *     $ wp cli alias add @prod --set-ssh=login@host --set-path=/path/to/wordpress/install/ --set-user=wpcli --config=project
         *     Success: Added '@prod' alias.
         *
         *     # Add group of aliases.
         *     $ wp cli alias add @multiservers --grouping=servera,serverb
         *     Success: Added '@multiservers' alias.
         */
        public function add($args, $assoc_args)
        {
        }
        /**
         * Deletes an alias.
         *
         * ## OPTIONS
         *
         * <key>
         * : Key for the alias.
         *
         * [--config=<config>]
         * : Config file to be considered for operations.
         * ---
         * options:
         *   - global
         *   - project
         * ---
         *
         * ## EXAMPLES
         *
         *     # Delete alias.
         *     $ wp cli alias delete @prod
         *     Success: Deleted '@prod' alias.
         *
         *     # Delete project alias.
         *     $ wp cli alias delete @prod --config=project
         *     Success: Deleted '@prod' alias.
         */
        public function delete($args, $assoc_args)
        {
        }
        /**
         * Updates an alias.
         *
         * ## OPTIONS
         *
         * <key>
         * : Key for the alias.
         *
         * [--set-user=<user>]
         * : Set user for alias.
         *
         * [--set-url=<url>]
         * : Set url for alias.
         *
         * [--set-path=<path>]
         * : Set path for alias.
         *
         * [--set-ssh=<ssh>]
         * : Set ssh for alias.
         *
         * [--set-http=<http>]
         * : Set http for alias.
         *
         * [--grouping=<grouping>]
         * : For grouping multiple aliases.
         *
         * [--config=<config>]
         * : Config file to be considered for operations.
         * ---
         * options:
         *   - global
         *   - project
         * ---
         *
         * ## EXAMPLES
         *
         *     # Update alias.
         *     $ wp cli alias update @prod --set-user=newuser --set-path=/new/path/to/wordpress/install/
         *     Success: Updated 'prod' alias.
         *
         *     # Update project alias.
         *     $ wp cli alias update @prod --set-user=newuser --set-path=/new/path/to/wordpress/install/ --config=project
         *     Success: Updated 'prod' alias.
         */
        public function update($args, $assoc_args)
        {
        }
        /**
         * Get config path and aliases data based on config type.
         *
         * @param string $config Type of config to get data from.
         * @param string $alias  Alias to be used for Add/Update/Delete.
         *
         * @return array Config Path and Aliases in it.
         */
        private function get_aliases_data($config, $alias)
        {
        }
        /**
         * Check if the config file exists and is writable.
         *
         * @param string $config_path Path to config file.
         *
         * @return void
         */
        private function validate_config_file($config_path)
        {
        }
        /**
         * Return aliases array.
         *
         * @param array  $aliases     Current aliases data.
         * @param string $alias       Name of alias.
         * @param array  $key_args    Associative arguments.
         * @param bool   $is_grouping Check if its a grouping operation.
         * @param string $grouping    Grouping value.
         * @param bool   $is_update   Is this an update operation?
         *
         * @return mixed
         */
        private function build_aliases($aliases, $alias, $assoc_args, $is_grouping, $grouping = '', $is_update = \false)
        {
        }
        /**
         * Validate input of passed arguments.
         *
         * @param array  $assoc_args Arguments array.
         * @param string $grouping   Grouping argument value.
         *
         * @throws WP_CLI\ExitException
         */
        private function validate_input($assoc_args, $grouping)
        {
        }
        /**
         * Validate alias type before update.
         *
         * @param array  $aliases    Existing aliases data.
         * @param string $alias      Alias Name.
         * @param array  $assoc_args Arguments array.
         * @param string $grouping   Grouping argument value.
         *
         * @throws WP_CLI\ExitException
         */
        private function validate_alias_type($aliases, $alias, $assoc_args, $grouping)
        {
        }
        /**
         * Save aliases data to config file.
         *
         * @param array  $aliases     Current aliases data.
         * @param string $alias       Name of alias.
         * @param string $config_path Path to config file.
         * @param string $operation   Current operation string fro message.
         */
        private function process_aliases($aliases, $alias, $config_path, $operation = '')
        {
        }
    }
    class Help_Command extends \WP_CLI_Command
    {
        /**
         * Get help on WP-CLI, or on a specific command.
         *
         * ## OPTIONS
         *
         * [<command>...]
         * : Get help on a specific command.
         *
         * ## EXAMPLES
         *
         *     # get help for `core` command
         *     wp help core
         *
         *     # get help for `core download` subcommand
         *     wp help core download
         */
        public function __invoke($args, $assoc_args)
        {
        }
        private static function show_help($command)
        {
        }
        private static function rewrap_param_desc($matches)
        {
        }
        private static function indent($whitespace, $text)
        {
        }
        private static function pass_through_pager($out)
        {
        }
        private static function get_initial_markdown($command)
        {
        }
        private static function render_subcommands($command)
        {
        }
        private static function get_max_len($strings)
        {
        }
        /**
         * Parse reference links from longdescription.
         *
         * @param  string $longdesc The longdescription from the `$command->get_longdesc()`.
         * @return string The longdescription which has links as footnote.
         */
        private static function parse_reference_links($longdesc)
        {
        }
    }
    /**
     * Various utilities for WP-CLI commands.
     */
    class WP_CLI
    {
        private static $configurator;
        private static $logger;
        private static $hooks = array();
        private static $hooks_passed = array();
        private static $capture_exit = \false;
        private static $deferred_additions = array();
        /**
         * Set the logger instance.
         *
         * @param object $logger
         */
        public static function set_logger($logger)
        {
        }
        /**
         * Get the Configurator instance
         *
         * @return \WP_CLI\Configurator
         */
        public static function get_configurator()
        {
        }
        public static function get_root_command()
        {
        }
        public static function get_runner()
        {
        }
        /**
         * @return FileCache
         */
        public static function get_cache()
        {
        }
        /**
         * Set the context in which WP-CLI should be run
         */
        public static function set_url($url)
        {
        }
        private static function set_url_params($url_parts)
        {
        }
        /**
         * @return WpHttpCacheManager
         */
        public static function get_http_cache_manager()
        {
        }
        /**
         * Colorize a string for output.
         *
         * Yes, you can change the color of command line text too. For instance,
         * here's how `WP_CLI::success()` colorizes "Success: "
         *
         * ```
         * WP_CLI::colorize( "%GSuccess:%n " )
         * ```
         *
         * Uses `\cli\Colors::colorize()` to transform color tokens to display
         * settings. Choose from the following tokens (and note 'reset'):
         *
         * * %y => ['color' => 'yellow'],
         * * %g => ['color' => 'green'],
         * * %b => ['color' => 'blue'],
         * * %r => ['color' => 'red'],
         * * %p => ['color' => 'magenta'],
         * * %m => ['color' => 'magenta'],
         * * %c => ['color' => 'cyan'],
         * * %w => ['color' => 'grey'],
         * * %k => ['color' => 'black'],
         * * %n => ['color' => 'reset'],
         * * %Y => ['color' => 'yellow', 'style' => 'bright'],
         * * %G => ['color' => 'green', 'style' => 'bright'],
         * * %B => ['color' => 'blue', 'style' => 'bright'],
         * * %R => ['color' => 'red', 'style' => 'bright'],
         * * %P => ['color' => 'magenta', 'style' => 'bright'],
         * * %M => ['color' => 'magenta', 'style' => 'bright'],
         * * %C => ['color' => 'cyan', 'style' => 'bright'],
         * * %W => ['color' => 'grey', 'style' => 'bright'],
         * * %K => ['color' => 'black', 'style' => 'bright'],
         * * %N => ['color' => 'reset', 'style' => 'bright'],
         * * %3 => ['background' => 'yellow'],
         * * %2 => ['background' => 'green'],
         * * %4 => ['background' => 'blue'],
         * * %1 => ['background' => 'red'],
         * * %5 => ['background' => 'magenta'],
         * * %6 => ['background' => 'cyan'],
         * * %7 => ['background' => 'grey'],
         * * %0 => ['background' => 'black'],
         * * %F => ['style' => 'blink'],
         * * %U => ['style' => 'underline'],
         * * %8 => ['style' => 'inverse'],
         * * %9 => ['style' => 'bright'],
         * * %_ => ['style' => 'bright']
         *
         * @access public
         * @category Output
         *
         * @param string $string String to colorize for output, with color tokens.
         * @return string Colorized string.
         */
        public static function colorize($string)
        {
        }
        /**
         * Schedule a callback to be executed at a certain point.
         *
         * Hooks conceptually are very similar to WordPress actions. WP-CLI hooks
         * are typically called before WordPress is loaded.
         *
         * WP-CLI hooks include:
         *
         * * `before_add_command:<command>` - Before the command is added.
         * * `after_add_command:<command>` - After the command was added.
         * * `before_invoke:<command>` - Just before a command is invoked.
         * * `after_invoke:<command>` - Just after a command is invoked.
         * * `find_command_to_run_pre` - Just before WP-CLI finds the command to run.
         * * `before_wp_load` - Just before the WP load process begins.
         * * `before_wp_config_load` - After wp-config.php has been located.
         * * `after_wp_config_load` - After wp-config.php has been loaded into scope.
         * * `after_wp_load` - Just after the WP load process has completed.
         * * `before_run_command` - Just before the command is executed.
         *
         * WP-CLI commands can create their own hooks with `WP_CLI::do_hook()`.
         *
         * If additional arguments are passed through the `WP_CLI::do_hook()` call,
         * these will be passed on to the callback provided by `WP_CLI::add_hook()`.
         *
         * ```
         * # `wp network meta` confirms command is executing in multisite context.
         * WP_CLI::add_command( 'network meta', 'Network_Meta_Command', array(
         *    'before_invoke' => function () {
         *        if ( !is_multisite() ) {
         *            WP_CLI::error( 'This is not a multisite installation.' );
         *        }
         *    }
         * ) );
         * ```
         *
         * @access public
         * @category Registration
         *
         * @param string $when Identifier for the hook.
         * @param mixed $callback Callback to execute when hook is called.
         * @return null
         */
        public static function add_hook($when, $callback)
        {
        }
        /**
         * Execute callbacks registered to a given hook.
         *
         * See `WP_CLI::add_hook()` for details on WP-CLI's internal hook system.
         * Commands can provide and call their own hooks.
         *
         * @access public
         * @category Registration
         *
         * @param string $when Identifier for the hook.
         * @param mixed ... Optional. Arguments that will be passed onto the
         *                  callback provided by `WP_CLI::add_hook()`.
         * @return null
         */
        public static function do_hook($when)
        {
        }
        /**
         * Add a callback to a WordPress action or filter.
         *
         * `add_action()` without needing access to `add_action()`. If WordPress is
         * already loaded though, you should use `add_action()` (and `add_filter()`)
         * instead.
         *
         * @access public
         * @category Registration
         *
         * @param string $tag Named WordPress action or filter.
         * @param mixed $function_to_add Callable to execute when the action or filter is evaluated.
         * @param integer $priority Priority to add the callback as.
         * @param integer $accepted_args Number of arguments to pass to callback.
         * @return true
         */
        public static function add_wp_hook($tag, $function_to_add, $priority = 10, $accepted_args = 1)
        {
        }
        /**
         * Build Unique ID for storage and retrieval.
         *
         * Essentially _wp_filter_build_unique_id() without needing access to _wp_filter_build_unique_id()
         */
        private static function wp_hook_build_unique_id($tag, $function, $priority)
        {
        }
        /**
         * Register a command to WP-CLI.
         *
         * WP-CLI supports using any callable class, function, or closure as a
         * command. `WP_CLI::add_command()` is used for both internal and
         * third-party command registration.
         *
         * Command arguments are parsed from PHPDoc by default, but also can be
         * supplied as an optional third argument during registration.
         *
         * ```
         * # Register a custom 'foo' command to output a supplied positional param.
         * #
         * # $ wp foo bar --append=qux
         * # Success: bar qux
         *
         * /**
         *  * My awesome closure command
         *  *
         *  * <message>
         *  * : An awesome message to display
         *  *
         *  * --append=<message>
         *  * : An awesome message to append to the original message.
         *  *
         *  * @when before_wp_load
         *  *\/
         * $foo = function( $args, $assoc_args ) {
         *     WP_CLI::success( $args[0] . ' ' . $assoc_args['append'] );
         * };
         * WP_CLI::add_command( 'foo', $foo );
         * ```
         *
         * @access public
         * @category Registration
         *
         * @param string   $name Name for the command (e.g. "post list" or "site empty").
         * @param callable $callable Command implementation as a class, function or closure.
         * @param array    $args {
         *    Optional. An associative array with additional registration parameters.
         *
         *    @type callable $before_invoke Callback to execute before invoking the command.
         *    @type callable $after_invoke  Callback to execute after invoking the command.
         *    @type string   $shortdesc     Short description (80 char or less) for the command.
         *    @type string   $longdesc      Description of arbitrary length for examples, etc.
         *    @type string   $synopsis      The synopsis for the command (string or array).
         *    @type string   $when          Execute callback on a named WP-CLI hook (e.g. before_wp_load).
         *    @type bool     $is_deferred   Whether the command addition had already been deferred.
         * }
         * @return bool True on success, false if deferred, hard error if registration failed.
         */
        public static function add_command($name, $callable, $args = array())
        {
        }
        /**
         * Defer command addition for a sub-command if the parent command is not yet
         * registered.
         *
         * @param string $name     Name for the sub-command.
         * @param string $parent   Name for the parent command.
         * @param string $callable Command implementation as a class, function or closure.
         * @param array  $args     Optional. See `WP_CLI::add_command()` for details.
         */
        private static function defer_command_addition($name, $parent, $callable, $args = array())
        {
        }
        /**
         * Get the list of outstanding deferred command additions.
         *
         * @return array Array of outstanding command additions.
         */
        public static function get_deferred_additions()
        {
        }
        /**
         * Remove a command addition from the list of outstanding deferred additions.
         */
        public static function remove_deferred_addition($name)
        {
        }
        /**
         * Display informational message without prefix, and ignore `--quiet`.
         *
         * Message is written to STDOUT. `WP_CLI::log()` is typically recommended;
         * `WP_CLI::line()` is included for historical compat.
         *
         * @access public
         * @category Output
         *
         * @param string $message Message to display to the end user.
         * @return null
         */
        public static function line($message = '')
        {
        }
        /**
         * Display informational message without prefix.
         *
         * Message is written to STDOUT, or discarded when `--quiet` flag is supplied.
         *
         * ```
         * # `wp cli update` lets user know of each step in the update process.
         * WP_CLI::log( sprintf( 'Downloading from %s...', $download_url ) );
         * ```
         *
         * @access public
         * @category Output
         *
         * @param string $message Message to write to STDOUT.
         */
        public static function log($message)
        {
        }
        /**
         * Display success message prefixed with "Success: ".
         *
         * Success message is written to STDOUT.
         *
         * Typically recommended to inform user of successful script conclusion.
         *
         * ```
         * # wp rewrite flush expects 'rewrite_rules' option to be set after flush.
         * flush_rewrite_rules( \WP_CLI\Utils\get_flag_value( $assoc_args, 'hard' ) );
         * if ( ! get_option( 'rewrite_rules' ) ) {
         *     WP_CLI::warning( "Rewrite rules are empty." );
         * } else {
         *     WP_CLI::success( 'Rewrite rules flushed.' );
         * }
         * ```
         *
         * @access public
         * @category Output
         *
         * @param string $message Message to write to STDOUT.
         * @return null
         */
        public static function success($message)
        {
        }
        /**
         * Display debug message prefixed with "Debug: " when `--debug` is used.
         *
         * Debug message is written to STDERR, and includes script execution time.
         *
         * Helpful for optionally showing greater detail when needed. Used throughout
         * WP-CLI bootstrap process for easier debugging and profiling.
         *
         * ```
         * # Called in `WP_CLI\Runner::set_wp_root()`.
         * private static function set_wp_root( $path ) {
         *     define( 'ABSPATH', Utils\trailingslashit( $path ) );
         *     WP_CLI::debug( 'ABSPATH defined: ' . ABSPATH );
         *     $_SERVER['DOCUMENT_ROOT'] = realpath( $path );
         * }
         *
         * # Debug details only appear when `--debug` is used.
         * # $ wp --debug
         * # [...]
         * # Debug: ABSPATH defined: /srv/www/wordpress-develop.dev/src/ (0.225s)
         * ```
         *
         * @access public
         * @category Output
         *
         * @param string $message Message to write to STDERR.
         * @param string|bool $group Organize debug message to a specific group.
         * Use `false` to not group the message.
         * @return null
         */
        public static function debug($message, $group = \false)
        {
        }
        /**
         * Display warning message prefixed with "Warning: ".
         *
         * Warning message is written to STDERR.
         *
         * Use instead of `WP_CLI::debug()` when script execution should be permitted
         * to continue.
         *
         * ```
         * # `wp plugin activate` skips activation when plugin is network active.
         * $status = $this->get_status( $plugin->file );
         * // Network-active is the highest level of activation status
         * if ( 'active-network' === $status ) {
         *   WP_CLI::warning( "Plugin '{$plugin->name}' is already network active." );
         *   continue;
         * }
         * ```
         *
         * @access public
         * @category Output
         *
         * @param string $message Message to write to STDERR.
         * @return null
         */
        public static function warning($message)
        {
        }
        /**
         * Display error message prefixed with "Error: " and exit script.
         *
         * Error message is written to STDERR. Defaults to halting script execution
         * with return code 1.
         *
         * Use `WP_CLI::warning()` instead when script execution should be permitted
         * to continue.
         *
         * ```
         * # `wp cache flush` considers flush failure to be a fatal error.
         * if ( false === wp_cache_flush() ) {
         *     WP_CLI::error( 'The object cache could not be flushed.' );
         * }
         * ```
         *
         * @access public
         * @category Output
         *
         * @param string|WP_Error  $message Message to write to STDERR.
         * @param boolean|integer  $exit    True defaults to exit(1).
         * @return null
         */
        public static function error($message, $exit = \true)
        {
        }
        /**
         * Halt script execution with a specific return code.
         *
         * Permits script execution to be overloaded by `WP_CLI::runcommand()`
         *
         * @access public
         * @category Output
         *
         * @param integer $return_code
         */
        public static function halt($return_code)
        {
        }
        /**
         * Display a multi-line error message in a red box. Doesn't exit script.
         *
         * Error message is written to STDERR.
         *
         * @access public
         * @category Output
         *
         * @param array $message Multi-line error message to be displayed.
         */
        public static function error_multi_line($message_lines)
        {
        }
        /**
         * Ask for confirmation before running a destructive operation.
         *
         * If 'y' is provided to the question, the script execution continues. If
         * 'n' or any other response is provided to the question, script exits.
         *
         * ```
         * # `wp db drop` asks for confirmation before dropping the database.
         *
         * WP_CLI::confirm( "Are you sure you want to drop the database?", $assoc_args );
         * ```
         *
         * @access public
         * @category Input
         *
         * @param string $question Question to display before the prompt.
         * @param array $assoc_args Skips prompt if 'yes' is provided.
         */
        public static function confirm($question, $assoc_args = array())
        {
        }
        /**
         * Read value from a positional argument or from STDIN.
         *
         * @param array $args The list of positional arguments.
         * @param int $index At which position to check for the value.
         *
         * @return string
         */
        public static function get_value_from_arg_or_stdin($args, $index)
        {
        }
        /**
         * Read a value, from various formats.
         *
         * @access public
         * @category Input
         *
         * @param mixed $value
         * @param array $assoc_args
         */
        public static function read_value($raw_value, $assoc_args = array())
        {
        }
        /**
         * Display a value, in various formats
         *
         * @param mixed $value Value to display.
         * @param array $assoc_args Arguments passed to the command, determining format.
         */
        public static function print_value($value, $assoc_args = array())
        {
        }
        /**
         * Convert a wp_error into a string
         *
         * @param mixed $errors
         * @return string
         */
        public static function error_to_string($errors)
        {
        }
        /**
         * Launch an arbitrary external process that takes over I/O.
         *
         * ```
         * # `wp core download` falls back to the `tar` binary when PharData isn't available
         * if ( ! class_exists( 'PharData' ) ) {
         *     $cmd = "tar xz --strip-components=1 --directory=%s -f $tarball";
         *     WP_CLI::launch( Utils\esc_cmd( $cmd, $dest ) );
         *     return;
         * }
         * ```
         *
         * @access public
         * @category Execution
         *
         * @param string $command External process to launch.
         * @param boolean $exit_on_error Whether to exit if the command returns an elevated return code.
         * @param boolean $return_detailed Whether to return an exit status (default) or detailed execution results.
         * @return int|WP_CLI\ProcessRun The command exit status, or a ProcessRun object for full details.
         */
        public static function launch($command, $exit_on_error = \true, $return_detailed = \false)
        {
        }
        /**
         * Run a WP-CLI command in a new process reusing the current runtime arguments.
         *
         * Use `WP_CLI::runcommand()` instead, which is easier to use and works better.
         *
         * Note: While this command does persist a limited set of runtime arguments,
         * it *does not* persist environment variables. Practically speaking, WP-CLI
         * packages won't be loaded when using WP_CLI::launch_self() because the
         * launched process doesn't have access to the current process $HOME.
         *
         * @access public
         * @category Execution
         *
         * @param string $command WP-CLI command to call.
         * @param array $args Positional arguments to include when calling the command.
         * @param array $assoc_args Associative arguments to include when calling the command.
         * @param bool $exit_on_error Whether to exit if the command returns an elevated return code.
         * @param bool $return_detailed Whether to return an exit status (default) or detailed execution results.
         * @param array $runtime_args Override one or more global args (path,url,user,allow-root)
         * @return int|WP_CLI\ProcessRun The command exit status, or a ProcessRun instance
         */
        public static function launch_self($command, $args = array(), $assoc_args = array(), $exit_on_error = \true, $return_detailed = \false, $runtime_args = array())
        {
        }
        /**
         * Get the path to the PHP binary used when executing WP-CLI.
         *
         * Environment values permit specific binaries to be indicated.
         *
         * Note: moved to Utils, left for BC.
         *
         * @access public
         * @category System
         *
         * @return string
         */
        public static function get_php_binary()
        {
        }
        /**
         * Confirm that a global configuration parameter does exist.
         *
         * @access public
         * @category Input
         *
         * @param string $key Config parameter key to check.
         *
         * @return bool
         */
        public static function has_config($key)
        {
        }
        /**
         * Get values of global configuration parameters.
         *
         * Provides access to `--path=<path>`, `--url=<url>`, and other values of
         * the [global configuration parameters](https://wp-cli.org/config/).
         *
         * ```
         * WP_CLI::log( 'The --url=<url> value is: ' . WP_CLI::get_config( 'url' ) );
         * ```
         *
         * @access public
         * @category Input
         *
         * @param string $key Get value for a specific global configuration parameter.
         * @return mixed
         */
        public static function get_config($key = \null)
        {
        }
        /**
         * Run a WP-CLI command.
         *
         * Launches a new child process to run a specified WP-CLI command.
         * Optionally:
         *
         * * Run the command in an existing process.
         * * Prevent halting script execution on error.
         * * Capture and return STDOUT, or full details about command execution.
         * * Parse JSON output if the command rendered it.
         *
         * ```
         * $options = array(
         *   'return'     => true,   // Return 'STDOUT'; use 'all' for full object.
         *   'parse'      => 'json', // Parse captured STDOUT to JSON array.
         *   'launch'     => false,  // Reuse the current process.
         *   'exit_error' => true,   // Halt script execution on error.
         * );
         * $plugins = WP_CLI::runcommand( 'plugin list --format=json', $options );
         * ```
         *
         * @access public
         * @category Execution
         *
         * @param string $command WP-CLI command to run, including arguments.
         * @param array  $options Configuration options for command execution.
         * @return mixed
         */
        public static function runcommand($command, $options = array())
        {
        }
        /**
         * Run a given command within the current process using the same global
         * parameters.
         *
         * Use `WP_CLI::runcommand()` instead, which is easier to use and works better.
         *
         * To run a command using a new process with the same global parameters,
         * use WP_CLI::launch_self(). To run a command using a new process with
         * different global parameters, use WP_CLI::launch().
         *
         * ```
         * ob_start();
         * WP_CLI::run_command( array( 'cli', 'cmd-dump' ) );
         * $ret = ob_get_clean();
         * ```
         *
         * @access public
         * @category Execution
         *
         * @param array $args Positional arguments including command name.
         * @param array $assoc_args
         */
        public static function run_command($args, $assoc_args = array())
        {
        }
        // DEPRECATED STUFF
        public static function add_man_dir()
        {
        }
        // back-compat
        public static function out($str)
        {
        }
        // back-compat
        // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Deprecated method.
        public static function addCommand($name, $class)
        {
        }
    }
}
// Utilities that do NOT depend on WordPress code.
namespace WP_CLI\Utils {
    function inside_phar()
    {
    }
    // Files that need to be read by external programs have to be extracted from the Phar archive.
    function extract_from_phar($path)
    {
    }
    function load_dependencies()
    {
    }
    function get_vendor_paths()
    {
    }
    // Using require() directly inside a class grants access to private methods to the loaded code.
    function load_file($path)
    {
    }
    function load_command($name)
    {
    }
    /**
     * Like array_map(), except it returns a new iterator, instead of a modified array.
     *
     * Example:
     *
     *     $arr = array('Football', 'Socker');
     *
     *     $it = iterator_map($arr, 'strtolower', function($val) {
     *       return str_replace('foo', 'bar', $val);
     *     });
     *
     *     foreach ( $it as $val ) {
     *       var_dump($val);
     *     }
     *
     * @param array|object Either a plain array or another iterator.
     * @param callback     The function to apply to an element.
     * @return object An iterator that applies the given callback(s).
     */
    function iterator_map($it, $fn)
    {
    }
    /**
     * Search for file by walking up the directory tree until the first file is found or until $stop_check($dir) returns true.
     * @param string|array The files (or file) to search for.
     * @param string|null  The directory to start searching from; defaults to CWD.
     * @param callable     Function which is passed the current dir each time a directory level is traversed.
     * @return null|string Null if the file was not found.
     */
    function find_file_upward($files, $dir = null, $stop_check = null)
    {
    }
    function is_path_absolute($path)
    {
    }
    /**
     * Composes positional arguments into a command string.
     *
     * @param array
     * @return string
     */
    function args_to_str($args)
    {
    }
    /**
     * Composes associative arguments into a command string.
     *
     * @param array
     * @return string
     */
    function assoc_args_to_str($assoc_args)
    {
    }
    /**
     * Given a template string and an arbitrary number of arguments,
     * returns the final command, with the parameters escaped.
     */
    function esc_cmd($cmd)
    {
    }
    /**
     * Gets path to WordPress configuration.
     *
     * @return string
     */
    function locate_wp_config()
    {
    }
    function wp_version_compare($since, $operator)
    {
    }
    /**
     * Render a collection of items as an ASCII table, JSON, CSV, YAML, list of ids, or count.
     *
     * Given a collection of items with a consistent data structure:
     *
     * ```
     * $items = array(
     *     array(
     *         'key'   => 'foo',
     *         'value'  => 'bar',
     *     )
     * );
     * ```
     *
     * Render `$items` as an ASCII table:
     *
     * ```
     * WP_CLI\Utils\format_items( 'table', $items, array( 'key', 'value' ) );
     *
     * # +-----+-------+
     * # | key | value |
     * # +-----+-------+
     * # | foo | bar   |
     * # +-----+-------+
     * ```
     *
     * Or render `$items` as YAML:
     *
     * ```
     * WP_CLI\Utils\format_items( 'yaml', $items, array( 'key', 'value' ) );
     *
     * # ---
     * # -
     * #   key: foo
     * #   value: bar
     * ```
     *
     * @access public
     * @category Output
     *
     * @param string        $format     Format to use: 'table', 'json', 'csv', 'yaml', 'ids', 'count'
     * @param array         $items      An array of items to output.
     * @param array|string  $fields     Named fields for each item of data. Can be array or comma-separated list.
     * @return null
     */
    function format_items($format, $items, $fields)
    {
    }
    /**
     * Write data as CSV to a given file.
     *
     * @access public
     *
     * @param resource $fd         File descriptor
     * @param array    $rows       Array of rows to output
     * @param array    $headers    List of CSV columns (optional)
     */
    function write_csv($fd, $rows, $headers = array())
    {
    }
    /**
     * Pick fields from an associative array or object.
     *
     * @param  array|object Associative array or object to pick fields from.
     * @param  array List of fields to pick.
     * @return array
     */
    function pick_fields($item, $fields)
    {
    }
    /**
     * Launch system's $EDITOR for the user to edit some text.
     *
     * @access public
     * @category Input
     *
     * @param string  $content  Some form of text to edit (e.g. post content).
     * @param string  $title    Title to display in the editor.
     * @param string  $ext      Extension to use with the temp file.
     * @return string|bool       Edited text, if file is saved from editor; false, if no change to file.
     */
    function launch_editor_for_input($input, $title = 'WP-CLI', $ext = 'tmp')
    {
    }
    /**
     * @param string MySQL host string, as defined in wp-config.php
     *
     * @return array
     */
    function mysql_host_to_cli_args($raw_host)
    {
    }
    function run_mysql_command($cmd, $assoc_args, $descriptors = null)
    {
    }
    /**
     * Render PHP or other types of files using Mustache templates.
     *
     * IMPORTANT: Automatic HTML escaping is disabled!
     */
    function mustache_render($template_name, $data = array())
    {
    }
    /**
     * Create a progress bar to display percent completion of a given operation.
     *
     * Progress bar is written to STDOUT, and disabled when command is piped. Progress
     * advances with `$progress->tick()`, and completes with `$progress->finish()`.
     * Process bar also indicates elapsed time and expected total time.
     *
     * ```
     * # `wp user generate` ticks progress bar each time a new user is created.
     * #
     * # $ wp user generate --count=500
     * # Generating users  22 % [=======>                             ] 0:05 / 0:23
     *
     * $progress = \WP_CLI\Utils\make_progress_bar( 'Generating users', $count );
     * for ( $i = 0; $i < $count; $i++ ) {
     *     // uses wp_insert_user() to insert the user
     *     $progress->tick();
     * }
     * $progress->finish();
     * ```
     *
     * @access public
     * @category Output
     *
     * @param string  $message  Text to display before the progress bar.
     * @param integer $count    Total number of ticks to be performed.
     * @param int     $interval Optional. The interval in milliseconds between updates. Default 100.
     * @return \cli\progress\Bar|WP_CLI\NoOp
     */
    function make_progress_bar($message, $count, $interval = 100)
    {
    }
    /**
     * Helper function to use wp_parse_url when available or fall back to PHP's
     * parse_url if not.
     *
     * Additionally, this adds 'http://' to the URL if no scheme was found.
     *
     * @param string $url           The URL to parse.
     * @param int $component        Optional. The specific component to retrieve.
     *                              Use one of the PHP predefined constants to
     *                              specify which one. Defaults to -1 (= return
     *                              all parts as an array).
     * @param bool $auto_add_scheme Optional. Automatically add an http:// scheme if
     *                              none was found. Defaults to true.
     * @return mixed False on parse failure; Array of URL components on success;
     *               When a specific component has been requested: null if the
     *               component doesn't exist in the given URL; a string or - in the
     *               case of PHP_URL_PORT - integer when it does. See parse_url()'s
     *               return values.
     */
    function parse_url($url, $component = -1, $auto_add_scheme = true)
    {
    }
    /**
     * Check if we're running in a Windows environment (cmd.exe).
     *
     * @return bool
     */
    function is_windows()
    {
    }
    /**
     * Replace magic constants in some PHP source code.
     *
     * @param string $source The PHP code to manipulate.
     * @param string $path The path to use instead of the magic constants
     */
    function replace_path_consts($source, $path)
    {
    }
    /**
     * Make a HTTP request to a remote URL.
     *
     * Wraps the Requests HTTP library to ensure every request includes a cert.
     *
     * ```
     * # `wp core download` verifies the hash for a downloaded WordPress archive
     *
     * $md5_response = Utils\http_request( 'GET', $download_url . '.md5' );
     * if ( 20 != substr( $md5_response->status_code, 0, 2 ) ) {
     *      WP_CLI::error( "Couldn't access md5 hash for release (HTTP code {$response->status_code})" );
     * }
     * ```
     *
     * @access public
     *
     * @param string $method    HTTP method (GET, POST, DELETE, etc.)
     * @param string $url       URL to make the HTTP request to.
     * @param array $headers    Add specific headers to the request.
     * @param array $options
     * @return object
     */
    function http_request($method, $url, $data = null, $headers = array(), $options = array())
    {
    }
    /**
     * Increments a version string using the "x.y.z-pre" format.
     *
     * Can increment the major, minor or patch number by one.
     * If $new_version == "same" the version string is not changed.
     * If $new_version is not a known keyword, it will be used as the new version string directly.
     *
     * @param string $current_version
     * @param string $new_version
     * @return string
     */
    function increment_version($current_version, $new_version)
    {
    }
    /**
     * Compare two version strings to get the named semantic version.
     *
     * @access public
     *
     * @param string $new_version
     * @param string $original_version
     * @return string $name 'major', 'minor', 'patch'
     */
    function get_named_sem_ver($new_version, $original_version)
    {
    }
    /**
     * Return the flag value or, if it's not set, the $default value.
     *
     * Because flags can be negated (e.g. --no-quiet to negate --quiet), this
     * function provides a safer alternative to using
     * `isset( $assoc_args['quiet'] )` or similar.
     *
     * @access public
     * @category Input
     *
     * @param array  $assoc_args  Arguments array.
     * @param string $flag        Flag to get the value.
     * @param mixed  $default     Default value for the flag. Default: NULL
     * @return mixed
     */
    function get_flag_value($assoc_args, $flag, $default = null)
    {
    }
    /**
     * Get the home directory.
     *
     * @access public
     * @category System
     *
     * @return string
     */
    function get_home_dir()
    {
    }
    /**
     * Appends a trailing slash.
     *
     * @access public
     * @category System
     *
     * @param string $string What to add the trailing slash to.
     * @return string String with trailing slash added.
     */
    function trailingslashit($string)
    {
    }
    /**
     * Normalize a filesystem path.
     *
     * On Windows systems, replaces backslashes with forward slashes
     * and forces upper-case drive letters.
     * Allows for two leading slashes for Windows network shares, but
     * ensures that all other duplicate slashes are reduced to a single one.
     * Ensures upper-case drive letters on Windows systems.
     *
     * @access public
     * @category System
     *
     * @param string $path Path to normalize.
     * @return string Normalized path.
     */
    function normalize_path($path)
    {
    }
    /**
     * Convert Windows EOLs to *nix.
     *
     * @param string $str String to convert.
     * @return string String with carriage return / newline pairs reduced to newlines.
     */
    function normalize_eols($str)
    {
    }
    /**
     * Get the system's temp directory. Warns user if it isn't writable.
     *
     * @access public
     * @category System
     *
     * @return string
     */
    function get_temp_dir()
    {
    }
    /**
     * Parse a SSH url for its host, port, and path.
     *
     * Similar to parse_url(), but adds support for defined SSH aliases.
     *
     * ```
     * host OR host/path/to/wordpress OR host:port/path/to/wordpress
     * ```
     *
     * @access public
     *
     * @return mixed
     */
    function parse_ssh_url($url, $component = -1)
    {
    }
    /**
     * Report the results of the same operation against multiple resources.
     *
     * @access public
     * @category Input
     *
     * @param string       $noun      Resource being affected (e.g. plugin)
     * @param string       $verb      Type of action happening to the noun (e.g. activate)
     * @param integer      $total     Total number of resource being affected.
     * @param integer      $successes Number of successful operations.
     * @param integer      $failures  Number of failures.
     * @param null|integer $skips     Optional. Number of skipped operations. Default null (don't show skips).
     */
    function report_batch_operation_results($noun, $verb, $total, $successes, $failures, $skips = null)
    {
    }
    /**
     * Parse a string of command line arguments into an $argv-esqe variable.
     *
     * @access public
     * @category Input
     *
     * @param string $arguments
     * @return array
     */
    function parse_str_to_argv($arguments)
    {
    }
    /**
     * Locale-independent version of basename()
     *
     * @access public
     *
     * @param string $path
     * @param string $suffix
     * @return string
     */
    function basename($path, $suffix = '')
    {
    }
    /**
     * Checks whether the output of the current script is a TTY or a pipe / redirect
     *
     * Returns true if STDOUT output is being redirected to a pipe or a file; false is
     * output is being sent directly to the terminal.
     *
     * If an env variable SHELL_PIPE exists, returned result depends on its
     * value. Strings like 1, 0, yes, no, that validate to booleans are accepted.
     *
     * To enable ASCII formatting even when the shell is piped, use the
     * ENV variable SHELL_PIPE=0.
     *
     * @access public
     *
     * @return bool
     */
    function isPiped()
    {
    }
    /**
     * Expand within paths to their matching paths.
     *
     * Has no effect on paths which do not use glob patterns.
     *
     * @param string|array $paths Single path as a string, or an array of paths.
     * @param int          $flags Optional. Flags to pass to glob. Defaults to GLOB_BRACE.
     * @return array Expanded paths.
     */
    function expand_globs($paths, $flags = 'default')
    {
    }
    /**
     * Simulate a `glob()` with the `GLOB_BRACE` flag set. For systems (eg Alpine Linux) built against a libc library (eg https://www.musl-libc.org/) that lacks it.
     * Copied and adapted from Zend Framework's `Glob::fallbackGlob()` and Glob::nextBraceSub()`.
     *
     * Zend Framework (http://framework.zend.com/)
     *
     * @link      http://github.com/zendframework/zf2 for the canonical source repository
     * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     *
     * @param string $pattern     Filename pattern.
     * @param void   $dummy_flags Not used.
     * @return array Array of paths.
     */
    function glob_brace($pattern, $dummy_flags = null)
    {
    }
    /**
     * Get the closest suggestion for a mis-typed target term amongst a list of
     * options.
     *
     * Uses the Levenshtein algorithm to calculate the relative "distance" between
     * terms.
     *
     * If the "distance" to the closest term is higher than the threshold, an empty
     * string is returned.
     *
     * @param string $target    Target term to get a suggestion for.
     * @param array  $options   Array with possible options.
     * @param int    $threshold Threshold above which to return an empty string.
     * @return string
     */
    function get_suggestion($target, array $options, $threshold = 2)
    {
    }
    /**
     * Get a Phar-safe version of a path.
     *
     * For paths inside a Phar, this strips the outer filesystem's location to
     * reduce the path to what it needs to be within the Phar archive.
     *
     * Use the __FILE__ or __DIR__ constants as a starting point.
     *
     * @param string $path An absolute path that might be within a Phar.
     * @return string A Phar-safe version of the path.
     */
    function phar_safe_path($path)
    {
    }
    /**
     * Check whether a given Command object is part of the bundled set of
     * commands.
     *
     * This function accepts both a fully qualified class name as a string as
     * well as an object that extends `WP_CLI\Dispatcher\CompositeCommand`.
     *
     * @param \WP_CLI\Dispatcher\CompositeCommand|string $command
     * @return bool
     */
    function is_bundled_command($command)
    {
    }
    /**
     * Maybe prefix command string with "/usr/bin/env".
     * Removes (if there) if Windows, adds (if not there) if not.
     *
     * @param string $command
     * @return string
     */
    function force_env_on_nix_systems($command)
    {
    }
    /**
     * Check that `proc_open()` and `proc_close()` haven't been disabled.
     *
     * @param string $context Optional. If set will appear in error message. Default null.
     * @param bool   $return  Optional. If set will return false rather than error out. Default false.
     * @return bool
     */
    function check_proc_available($context = null, $return = false)
    {
    }
    /**
     * Returns past tense of verb, with limited accuracy. Only regular verbs catered for, apart from "reset".
     *
     * @param string $verb Verb to return past tense of.
     * @return string
     */
    function past_tense_verb($verb)
    {
    }
    /**
     * Get the path to the PHP binary used when executing WP-CLI.
     *
     * Environment values permit specific binaries to be indicated.
     *
     * @access public
     * @category System
     *
     * @return string
     */
    function get_php_binary()
    {
    }
    /**
     * Windows compatible `proc_open()`.
     * Works around bug in PHP, and also deals with *nix-like `ENV_VAR=blah cmd` environment variable prefixes.
     *
     * @access public
     *
     * @param string $command        Command to execute.
     * @param array  $descriptorspec Indexed array of descriptor numbers and their values.
     * @param array  &$pipes         Indexed array of file pointers that correspond to PHP's end of any pipes that are created.
     * @param string $cwd            Initial working directory for the command.
     * @param array  $env            Array of environment variables.
     * @param array  $other_options  Array of additional options (Windows only).
     * @return resource Command stripped of any environment variable settings.
     */
    function proc_open_compat($cmd, $descriptorspec, &$pipes, $cwd = null, $env = null, $other_options = null)
    {
    }
    /**
     * For use by `proc_open_compat()` only. Separated out for ease of testing. Windows only.
     * Turns *nix-like `ENV_VAR=blah command` environment variable prefixes into stripped `cmd` with prefixed environment variables added to passed in environment array.
     *
     * @access private
     *
     * @param string $command Command to execute.
     * @param array &$env Array of existing environment variables. Will be modified if any settings in command.
     * @return string Command stripped of any environment variable settings.
     */
    function _proc_open_compat_win_env($cmd, &$env)
    {
    }
    /**
     * First half of escaping for LIKE special characters % and _ before preparing for MySQL.
     *
     * Use this only before wpdb::prepare() or esc_sql().  Reversing the order is very bad for security.
     *
     * Copied from core "wp-includes/wp-db.php". Avoids dependency on WP 4.4 wpdb.
     *
     * @access public
     *
     * @param string $text The raw text to be escaped. The input typed by the user should have no
     *                     extra or deleted slashes.
     * @return string Text in the form of a LIKE phrase. The output is not SQL safe. Call $wpdb::prepare()
     *                or real_escape next.
     */
    function esc_like($text)
    {
    }
    /**
     * Escapes (backticks) MySQL identifiers (aka schema object names) - i.e. column names, table names, and database/index/alias/view etc names.
     * See https://dev.mysql.com/doc/refman/5.5/en/identifiers.html
     *
     * @param  string|array $idents A single identifier or an array of identifiers.
     * @return string|array An escaped string if given a string, or an array of escaped strings if given an array of strings.
     */
    function esc_sql_ident($idents)
    {
    }
    /**
     * Check whether a given string is a valid JSON representation.
     *
     * @param string $argument       String to evaluate.
     * @param bool   $ignore_scalars Optional. Whether to ignore scalar values.
     *                               Defaults to true.
     * @return bool Whether the provided string is a valid JSON representation.
     */
    function is_json($argument, $ignore_scalars = true)
    {
    }
    /**
     * Parse known shell arrays included in the $assoc_args array.
     *
     * @param array $assoc_args      Associative array of arguments.
     * @param array $array_arguments Array of argument keys that should receive an
     *                               array through the shell.
     * @return array
     */
    function parse_shell_arrays($assoc_args, $array_arguments)
    {
    }
    /**
     * Describe a callable as a string.
     *
     * @param callable $callable The callable to describe.
     * @return string String description of the callable.
     */
    function describe_callable($callable)
    {
    }
    /**
     * Pluralizes a noun in a grammatically correct way.
     *
     * @param string   $noun  Noun to be pluralized. Needs to be in singular form.
     * @param int|null $count Optional. Count of the nouns, to decide whether to
     *                        pluralize. Will pluralize unconditionally if none
     *                        provided.
     * @return string Pluralized noun.
     */
    function pluralize($noun, $count = null)
    {
    }
}
namespace WP_CLI {
    /**
     * Get the list of ordered steps that need to be processed to bootstrap WP-CLI.
     *
     * Each entry is a fully qualified class name for a class implementing the
     * `WP_CLI\Bootstrap\BootstrapStep` interface.
     *
     * @return string[]
     */
    function get_bootstrap_steps()
    {
    }
    /**
     * Register the classes needed for the bootstrap process.
     *
     * The Composer autoloader is not active yet at this point, so we need to use a
     * custom autoloader to fetch the bootstrap classes in a flexible way.
     */
    function prepare_bootstrap()
    {
    }
    /**
     * Initialize and return the bootstrap state to pass from step to step.
     *
     * @return BootstrapState
     */
    function initialize_bootstrap_state()
    {
    }
    /**
     * Process the bootstrapping steps.
     *
     * Loops over each of the provided steps, instantiates it and then calls its
     * `process()` method.
     */
    function bootstrap()
    {
    }
}
// Utilities that depend on WordPress code.
namespace WP_CLI\Utils {
    function wp_not_installed()
    {
    }
    // phpcs:disable WordPress.PHP.IniSet -- Intentional & correct usage.
    function wp_debug_mode()
    {
    }
    // phpcs:enable
    function replace_wp_die_handler()
    {
    }
    function wp_die_handler($message)
    {
    }
    /**
     * Clean HTML error message so suitable for text display.
     */
    function wp_clean_error_message($message)
    {
    }
    function wp_redirect_handler($url)
    {
    }
    function maybe_require($since, $path)
    {
    }
    function get_upgrader($class)
    {
    }
    /**
     * Converts a plugin basename back into a friendly slug.
     */
    function get_plugin_name($basename)
    {
    }
    function is_plugin_skipped($file)
    {
    }
    function get_theme_name($path)
    {
    }
    function is_theme_skipped($path)
    {
    }
    /**
     * Register the sidebar for unused widgets.
     * Core does this in /wp-admin/widgets.php, which isn't helpful.
     */
    function wp_register_unused_sidebar()
    {
    }
    /**
     * Attempts to determine which object cache is being used.
     *
     * Note that the guesses made by this function are based on the WP_Object_Cache classes
     * that define the 3rd party object cache extension. Changes to those classes could render
     * problems with this function's ability to determine which object cache is being used.
     *
     * @return string
     */
    function wp_get_cache_type()
    {
    }
    /**
     * Clear WordPress internal object caches.
     *
     * In long-running scripts, the internal caches on `$wp_object_cache` and `$wpdb`
     * can grow to consume gigabytes of memory. Periodically calling this utility
     * can help with memory management.
     *
     * @access public
     * @category System
     * @deprecated 1.5.0
     */
    function wp_clear_object_cache()
    {
    }
    /**
     * Get a set of tables in the database.
     *
     * Interprets common command-line options into a resolved set of table names.
     *
     * @param array $args Provided table names, or tables with wildcards.
     * @param array $assoc_args Optional flags for groups of tables (e.g. --network)
     * @return array $tables
     */
    function wp_get_table_names($args, $assoc_args = array())
    {
    }
    /**
     * Failsafe use of the WordPress wp_strip_all_tags() function.
     *
     * Automatically falls back to strip_tags() function if the WP function is not
     * available.
     *
     * @param string $string String to strip the tags from.
     * @return string String devoid of tags.
     */
    function strip_tags($string)
    {
    }
}
namespace WP_CLI\Dispatcher {
    /**
     * Get the path to a command, e.g. "core download"
     *
     * @param Subcommand|CompositeCommand $command
     * @return string[]
     */
    function get_path($command)
    {
    }
}