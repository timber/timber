<?php

class TimberTermGetter {

	public static function get_term_query_from_query_string($query_string){
        $args = array();
        parse_str($query_string, $args);
        $ret = self::get_term_query_from_assoc_array($args);
        return $ret;
    }

    public static function get_term_query_from_string($taxs){
        $ret = new stdClass();
        $ret->args = array();
        if (is_string($taxs)){
            $taxs = array($taxs);
        }
        $ret->taxonomies = self::correct_taxonomy_names($taxs);
        return $ret;
    }

    public static function get_term_query_from_assoc_array($args){
        $ret = new stdClass();
        $ret->args = $args;
        if (isset($ret->args['tax'])){
            $ret->taxonomies = $ret->args['tax'];
        } else if (isset($ret->args['taxonomies'])){
            $ret->taxonomies = $ret->args['taxonomies'];
        } else if (isset($ret->args['taxs'])){
            $ret->taxonomies = $ret->args['taxs'];
        } else if (isset($ret->args['taxonomy'])){
            $ret->taxonomies = $ret->args['taxonomy'];
        }
        if (isset($ret->taxonomies)){
            if (is_string($ret->taxonomies)){
                $ret->taxonomies = array($ret->taxonomies);
            }
            $ret->taxonomies = self::correct_taxonomy_names($ret->taxonomies);
        }
        return $ret;
    }

    public static function get_term_query_from_array($args){
    	if (is_array($args) && !empty($args)){
    		//okay its an array with content
    		if (is_int($args[0])){
    			return self::get_term_query_from_array_of_ids($args);
    		} else if (is_string($args[0])){
    			return self::get_term_query_from_array_of_strings($args);
    		}
    	}
    }

    public static function get_term_query_from_array_of_ids($args){
    	$ret = new stdClass();
    	$ret->taxonomies = get_taxonomies();
    	$ret->args['include'] = $args;
    	return $ret;
    }

    public static function get_term_query_from_array_of_strings($args){
    	$ret = new stdClass();
    	$ret->taxonomies = self::correct_taxonomy_names($args);
    	$ret->args = array();
    	return $ret;
    }

    private static function correct_taxonomy_names($taxs){
        if (is_string($taxs)){
            $taxs = array($taxs);
        }
        foreach($taxs as &$tax){
            if ($tax == 'tags' || $tax == 'tag'){
                $tax = 'post_tag';
            } else if ($tax == 'categories'){
                $tax = 'category';
            }
        }
        return $taxs;
    }

}