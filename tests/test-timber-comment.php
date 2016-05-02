<?php

class TestTimberComment extends Timber_UnitTestCase {

	function testComment(){
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id));
		$comment = new TimberComment($comment_id);
		$this->assertEquals('Timber\Comment', get_class($comment));
		$this->assertEquals($comment_id, $comment->ID);
	}

	function testCommentWithMeta(){
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id));
		update_comment_meta( $comment_id, 'rebney', 'Winnebago Man');
		update_comment_meta( $comment_id, 'quote', 'Will you do me a kindness?');
		$comment = new TimberComment($comment_id);
		$this->assertEquals('Winnebago Man', $comment->rebney);
	}

	function testCommentToString(){
		$quote = 'Jerry, just remember, it’s not a lie if you believe it.';
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => $quote));
		$comment = new TimberComment($comment_id);
		$str = Timber::compile_string('{{comment}}', array('comment' => $comment));
		$this->assertEquals($quote, $str);
	}

	function testCommentContent(){
		$costanza_quote = "Divorce is always hard. Especially on the kids. ‘Course I am the result of my parents having stayed together so ya never know.";
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => $costanza_quote));
		$comment = new TimberComment($comment_id);
		$this->assertEquals($costanza_quote, $comment->content());
	}

	function testCommentApproval(){
		$kramer_quote = "Oh, you gotta eat before surgery. You need your strength.";
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => $kramer_quote));
		$comment = new TimberComment($comment_id);
		$comment->assertTrue($comment->approved());

		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => 'You ever dream in 3-D? It’s like the Boogie Man is coming RIGHT AT YOU.', 'comment_approved' => false));
		$comment = new TimberComment($comment_id);
		$comment->assertFalse($comment->approved());
	}

	function testCommentDate(){
		$quote = "So he just shaves his head for no reason? That’s like using a wheelchair for the fun of it!";
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => $quote, 'comment_date' => '2015-08-21 03:24:07'));
		$comment = new TimberComment($comment_id);
		$comment->assertEquals('August 21, 2015', $comment->date());
	}

	function testCommentTime(){
		$quote = "My grandmother used to swear by this, but personally I was always skeptical.";
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => $quote, 'comment_date' => '2015-08-21 03:24:07'));
		$comment = new TimberComment($comment_id);
		$comment->assertEquals('3:24 am', $comment->time());
	}

	function testCommentReplyLink() {
		$comment_text = "Try the soup";
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => $comment_text, 'comment_date' => '2015-08-21 03:24:07'));
		$comment = new TimberComment($comment_id);
		$link = $comment->reply_link('Respond');
		$this->assertEquals('Respond', strip_tags($link));
	}

	function testAnonymousComment() {
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => 'Mystery', 'user_id' => 0, 'comment_author' => false));
		$comment = new TimberComment($comment_id);
		$twig_string = '{{comment.author.name}}';
		$result = Timber::compile_string($twig_string, array('comment' => $comment));
		$this->assertEquals('Anonymous', $result);
	}

	function testAnonymousCommentWithName() {
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => 'Mystery', 'user_id' => 0, 'comment_author' => 'Milhouse Van Houten'));
		$comment = new TimberComment($comment_id);
		$twig_string = '{{comment.author.name}}';
		$result = Timber::compile_string($twig_string, array('comment' => $comment));
		$this->assertEquals('Milhouse Van Houten', $result);
	}

	function testCommentWithChildren() {
		$kramer = $this->factory->user->create(array('display_name' => 'Cosmo Kramer'));
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => 'These pretzels are making me thirsty.', 'user_id' => $kramer));
		sleep(2);
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => 'Perhaps there’s more to Newman than meets the eye.'));
		$child_id = $this->factory->comment->create(array('comment_post_ID' => $post_id, 'comment_content' => 'No, there’s less.', 'comment_parent' => $comment_id));
		$post = new TimberPost($post_id);
		$comments = $post->get_comments();
		$this->assertEquals(2, count($comments));
		$this->assertEquals(1, count($comments[1]->children));
		$twig_string = '{{comment.author.name}}';
		$result = Timber::compile_string($twig_string, array('comment' => $comments[0]));
		$this->assertEquals('Cosmo Kramer', $result);
	}




}
