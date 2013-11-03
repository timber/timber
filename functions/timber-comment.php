<?php

class TimberComment extends TimberCore {

    var $PostClass = 'TimberPost';

    public static $representation = 'comment';

    function __construct($cid) {
        $this->init($cid);
    }

    function init($cid) {
        $comment_data = $cid;
        if (is_integer($cid)) {
            $comment_data = get_comment($cid);
        }
        $this->import($comment_data);
        $this->ID = $this->comment_ID;
        $comment_meta_data = $this->get_meta_fields($this->ID);
        $this->import($comment_meta_data);
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

    function content() {
        return $this->comment_content;
    }

    function date() {
        return $this->comment_date;
    }

    private function get_meta_fields($comment_id = null){
        if ($comment_id === null){
            $comment_id = $this->ID;
        }
        //Could not find a WP function to fetch all comment meta data, so I made one.
        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM $wpdb->commentmeta WHERE comment_id = %d", $comment_id);
        $metas = $wpdb->get_results($query);
        $customs = array();
        foreach($metas as $meta_row){
            $customs[$meta_row->meta_key] = maybe_unserialize($meta_row->meta_value);
        }
        return $customs;
    }

    private function get_meta_field($field_name){

    }

    public function meta($field_name){
        return $this->get_meta_field($field_name);
    }
    

   

}