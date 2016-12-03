<?php

namespace Timber;

use Timber\Comment;

class CommentThread extends \ArrayObject {

	var $CommentClass = 'Timber\Comment';
	var $post_id;
	var $_orderby = '';
	var $_order = 'ASC';

	function __construct( $post_id ) {
		parent::__construct();
		$this->post_id = $post_id;
		$this->init($post_id);
	}

	protected function fetch_comments() {
		$comments = get_comments( array('post_id' => $this->post_id, 'orderby' => $this->_orderby, 'order' => $this->_order) );
		return $comments;
	}

	public function orderby( $orderby = 'wp' ) {
		$this->_orderby = $orderby;
		$this->init();
	}

	public function order( $order = 'DESC' ) {
		$this->_order = $order;
		$this->init();
	}

	function init() {
		$overridden_cpage = false;
		$comments = $this->fetch_comments();
		$tcs = array();
		if ( '' == get_query_var('cpage') && get_option('page_comments') ) {
			set_query_var('cpage', 'newest' == get_option('default_comments_page') ? get_comment_pages_count() : 1);
			$overridden_cpage = true;
		}

		foreach ( $comments as $key => &$comment ) {
			$timber_comment = new $this->CommentClass($comment);
			$tcs[$timber_comment->id] = $timber_comment;
		}

		$parents = array();
		$children = array();

		foreach( $tcs as $comment ) {
			if ( $comment->is_child() ) {
				$children[$comment->ID] = $comment;
			} else {
				$parents[$comment->ID] = $comment;
			}
		}

		foreach ( $children as &$comment ) {
			$parent_id = $comment->comment_parent;
			if ( isset($parents[$parent_id]) ) {
				$parents[$parent_id]->add_child( $comment );
			}
			if ( isset($children[$parent_id]) ) {
				$children[$parent_id]->add_child( $comment );
			}
		}

		foreach ( $parents as $comment ) {
			$comment->update_depth();
		}
		
		$this->import_comments($parents);
	}

	protected function clear() {
		foreach ( $this as $item ) {
			unset($item);
		}
	}

	protected function import_comments( $arr ) {
		$this->clear();
		foreach ( $arr as $comment ) {
			$this[] = $comment;
		}
	}

}