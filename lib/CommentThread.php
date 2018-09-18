<?php

namespace Timber;

use Timber\Comment;

class CommentThread extends \ArrayObject {

	var $CommentClass = 'Timber\Comment';
	var $post_id;
	var $_orderby = '';
	var $_order = 'ASC';

	/**
	 * @param int $post_id
	 * @param array|boolean $args an array of arguments
	 * 						or false if to skip initialization
	 */
	public function __construct( $post_id, $args = array() ) {
		parent::__construct();
		$this->post_id = $post_id;
		if ( $args || is_array($args) ) {
			$this->init($args);
		}
	}

	protected function fetch_comments( $args = array() ) {
		$args['post_id'] = $this->post_id;
		$comments = get_comments($args);
		return $comments;
	}

	/**
	 * @experimental
	 */
	public function orderby( $orderby = 'wp' ) {
		$this->_orderby = $orderby;
		$this->init();
		return $this;
	}

	protected function merge_args( $args ) {
		$base = array('status' => 'approve');
		$overrides = array('order' => $this->_order);
		return array_merge($base, $args, $overrides);
	}

	/**
	 * @experimental
	 */
	public function order( $order = 'ASC' ) {
		$this->_order = $order;
		$this->init();
		return $this;
	}

	public function init( $args = array() ) {
		global $overridden_cpage;
		$args = self::merge_args($args);
		$comments = $this->fetch_comments($args);
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
				$parents[$parent_id]->add_child($comment);
			}
			if ( isset($children[$parent_id]) ) {
				$children[$parent_id]->add_child($comment);
			}
		}
		//there's something in update_depth that breaks order?

		foreach ( $parents as $comment ) {
			$comment->update_depth();
		}
		$this->import_comments($parents);
	}

	protected function clear() {
		$this->exchangeArray(array());
	}

	protected function import_comments( $arr ) {
		$this->clear();
		$i = 0;
		foreach ( $arr as $comment ) {
			$this[$i] = $comment;
			$i++;
		}
	}

}