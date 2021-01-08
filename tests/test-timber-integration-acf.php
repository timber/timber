<?php

use Timber\Integrations\ACF;

/**
 * @group users-api
 * @group comments-api
 * @group integrations
 * @group posts-api
 */
class TestTimberIntegrationACF extends Timber_UnitTestCase {
	function testACFInit() {
		$acf = new ACF();
		$this->assertInstanceOf( 'Timber\Integrations\ACF', $acf );
	}

	function testACFGetFieldPost() {
		$pid = $this->factory->post->create();
		update_field( 'subhead', 'foobar', $pid );
		$str = '{{post.meta("subhead")}}';
		$post = Timber::get_post( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals( 'foobar', $str );
	}

	function testACFHasFieldPostFalse() {
		$pid = $this->factory->post->create();
		$str = '{% if post.has_field("heythisdoesntexist") %}FAILED{% else %}WORKS{% endif %}';
		$post = Timber::get_post( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals('WORKS', $str);
	}

	function testACFHasFieldPostTrue() {
		$pid = $this->factory->post->create();
		update_post_meta($pid, 'best_radiohead_album', 'in_rainbows');
		$str = '{% if post.has_field("best_radiohead_album") %}In Rainbows{% else %}OK Computer{% endif %}';
		$post = Timber::get_post( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals('In Rainbows', $str);
	}

	function testACFGetFieldTermCategory() {
		$tid = $this->factory->term->create();
		update_field( 'color', 'blue', "category_${tid}" );
		$cat = Timber::get_term( $tid );
		$this->assertEquals( 'blue', $cat->color );
		$str = '{{term.color}}';
		$this->assertEquals( 'blue', Timber::compile_string( $str, array( 'term' => $cat ) ) );
	}

	function testACFCustomFieldTermTag() {
		$tid = $this->factory->term->create();
		update_field( 'color', 'green', 'post_tag_'.$tid );
		$term = Timber::get_term( $tid );
		$str = '{{term.color}}';
		$this->assertEquals( 'green', Timber::compile_string( $str, array( 'term' => $term ) ) );
	}

	function testACFGetFieldTermTag() {
		$tid = $this->factory->term->create();
		update_field( 'color', 'blue', 'post_tag_'.$tid );
		$term = Timber::get_term( $tid );
		$str = '{{term.meta("color")}}';
		$this->assertEquals( 'blue', Timber::compile_string( $str, array( 'term' => $term ) ) );
	}

	function testACFFieldObject() {
		$key = 'field_5ba2c660ed26d';

		$fp_id = $this->factory->post->create( [
			'post_content' => 'a:10:{s:4:"type";s:4:"text";s:12:"instructions";s:0:"";s:8:"required";i:0;s:17:"conditional_logic";i:0;s:7:"wrapper";a:3:{s:5:"width";s:0:"";s:5:"class";s:0:"";s:2:"id";s:0:"";}s:13:"default_value";s:0:"";s:11:"placeholder";s:0:"";s:7:"prepend";s:0:"";s:6:"append";s:0:"";s:9:"maxlength";s:0:"";}',
			'post_title'   => 'Thinger',
			'post_name'    => $key,
			'post_type'    => 'acf-field',
		] );

		$pid = $this->factory->post->create();

		update_field( 'thinger', 'foo', $pid );
		update_field( '_thinger', $key, $pid );

		$post     = Timber::get_post($pid);
		$template = '{{ post.meta("thinger") }} / {{ post.field_object("thinger").key }}';
		$str      = Timber::compile_string($template, array( 'post' => $post ));

		$this->assertEquals('foo / '.$key, $str);
	}

	function testACFFormatValue() {
		acf_add_local_field_group( array(
			'key'      => 'group_1',
			'title'    => 'Group 1',
			'fields'   => [
				[
					'key'   => 'field_1',
					'label' => 'Lead',
					'name'  => 'lead',
					'type'  => 'wysiwyg',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					],
				],
			],
		) );

		$post_id = $this->factory->post->create();
		$post    = Timber::get_post( $post_id );
		update_field( 'lead', 'Murder Spagurders are dangerous sneks.', $post_id );

		$string = trim( Timber::compile_string( "{{ post.meta('lead') }}", [ 'post' => $post ] ) );
		$this->assertEquals( '<p>Murder Spagurders are dangerous sneks.</p>', $string );

		$string = trim( Timber::compile_string( "{{ post.meta('lead', { format_value: false }) }}", [ 'post' => $post ] ) );
		$this->assertEquals( 'Murder Spagurders are dangerous sneks.', $string );
	}

	function testACFFormatImage() {

		$this->register_field('my_image', 'image');

		$pid = $this->factory->post->create();
		$image_id = TimberAttachment_UnitTestCase::get_attachment();
		update_field( 'my_image', $image_id, $pid );
		$post = Timber::get_post( $pid );

		$image = $post->meta('my_image');
		$this->assertInstanceOf('Timber\Image', $image);
		$this->assertEquals($image_id, $image->ID);
	}

	function testACFFormatImageNoConvert() {

		$this->register_field('my_image_no_convert', 'image');

		$pid = $this->factory->post->create();
		$image_id = TimberAttachment_UnitTestCase::get_attachment();
		update_field( 'my_image_no_convert', $image_id, $pid );
		$post = Timber::get_post( $pid );

		$image = $post->meta('my_image_no_convert', ['convert_value' => false]);
		$this->assertTrue(is_array($image));
		$this->assertEquals($image['id'], $image_id);
	}

	function testACFFormatImageCustomReturnFormat() {

		$this->register_field('my_image_custom_return_format', 'image', ['return_format' => 'id']);

		$pid = $this->factory->post->create();
		$image_id = TimberAttachment_UnitTestCase::get_attachment();
		update_field( 'my_image_custom_return_format', $image_id, $pid );
		$post = Timber::get_post( $pid );

		$image = $post->meta('my_image_custom_return_format', ['convert_value' => false]);

		$this->assertTrue(is_numeric($image));
		$this->assertEquals($image, $image_id);
	}

	function testACFFormatDatePicker() {

		$this->register_field('my_date', 'date_picker');

		$pid = $this->factory->post->create();
		update_field( 'my_date', '20210222', $pid );
		$post = Timber::get_post( $pid );

		$date = $post->meta('my_date');
		$this->assertInstanceOf('DateTimeImmutable', $date);
		$this->assertEquals('2021-02-22', $date->format('Y-m-d'));
	}

	function testACFFormatDateTimePicker() {

		$this->register_field('my_date_time', 'date_time_picker');

		$pid = $this->factory->post->create();
		update_field( 'my_date_time', '2021-02-22 17:30:25', $pid );
		$post = Timber::get_post( $pid );

		$date_time = $post->meta('my_date_time');
		$this->assertInstanceOf('DateTimeImmutable', $date_time);
		$this->assertEquals('2021-02-22 17:30:25', $date_time->format('Y-m-d H:i:s'));
	}

	/**
	 * @expectedDeprecated {{ post.get_field('field_name') }}
	 */
	function testPostGetFieldDeprecated() {
		$post_id = $this->factory->post->create();
		$post    = Timber::get_post( $post_id );

		$post->get_field( 'field_name' );
	}

	/**
	 * @expectedDeprecated {{ term.get_field('field_name') }}
	 */
	function testTermGetFieldDeprecated() {
		$term_id = $this->factory->term->create();
		$term    = Timber::get_term( $term_id );

		$term->get_field( 'field_name' );
	}

	/**
	 * @expectedDeprecated {{ user.get_field('field_name') }}
	 */
	function testUserGetFieldDeprecated() {
		$user_id = $this->factory->user->create();
		$user    = Timber::get_user( $user_id );

		$user->get_field( 'field_name' );
	}

	/**
	 * @expectedDeprecated {{ comment.get_field('field_name') }}
	 */
	function testCommentGetFieldDeprecated() {
		$comment_id = $this->factory->comment->create();
		$comment    = Timber\Timber::get_comment( $comment_id );

		$comment->get_field( 'field_name' );
	}

	function testACFContentField() {
		$pid = $this->factory->post->create(array('post_content' => 'Cool content bro!'));
		update_field( 'content', 'I am custom content', $pid );
		update_field( '_content', 'I am also custom content', $pid );
		$str = '{{ post.content }}';
		$post = Timber::get_post( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals( '<p>Cool content bro!</p>', trim($str) );
	}

	private function register_field( $field_name, $field_type, $field_args = [] ) {

		$group_key = sprintf('group_%s', uniqid());

		$field = array_merge([
			'key'   => 'field_2',
			'label' => 'Field',
			'name'  => $field_name,
			'type'  => $field_type,
		], $field_args);

		acf_add_local_field_group( array(
			'key'      => $group_key,
			'title'    => 'Group',
			'fields'   => [
				$field,
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					],
				],
			],
		) );
	}
}
