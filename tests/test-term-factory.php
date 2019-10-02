<?php

use Timber\Term;
use Timber\Factory\TermFactory;

class MyTerm extends Term {}
class WhacknessLevel extends Term {}
class HellaWhackTerm extends Term {}

/**
 * @group factory
 */
class TestTermFactory extends Timber_UnitTestCase {
	public function testGetTerm() {
		$tag_id = $this->factory->term->create(['name' => 'Toyota',    'taxonomy' => 'post_tag']);
		$cat_id = $this->factory->term->create(['name' => 'Chevrolet', 'taxonomy' => 'category']);

		$termFactory = new TermFactory();
		$tag				 = $termFactory->get_term($tag_id);
		$cat				 = $termFactory->get_term($cat_id);

		// Assert that all instances are of Timber\Term
		$this->assertInstanceOf(Term::class, $tag);
		$this->assertInstanceOf(Term::class, $cat);
	}

	public function testGetTermWithOverrides() {
		register_taxonomy('whackness', 'post');
		$my_class_map = function() {
			return [
				'post_tag'  => MyTerm::class,
				'category'  => MyTerm::class,
				'whackness' => WhacknessLevel::class,
			];
		};
		add_filter( 'timber/term/classmap', $my_class_map );

		$tag_id       = $this->factory->term->create(['name' => 'Toyota',        'taxonomy' => 'post_tag']);
		$cat_id       = $this->factory->term->create(['name' => 'Chevrolet',     'taxonomy' => 'category']);
		$whackness_id = $this->factory->term->create(['name' => 'Wiggity-Whack', 'taxonomy' => 'whackness']);

		$termFactory = new TermFactory();
		$tag				 = $termFactory->get_term($tag_id);
		$cat				 = $termFactory->get_term($cat_id);
		$whackness   = $termFactory->get_term($whackness_id);

		$this->assertInstanceOf(MyTerm::class,         $tag);
		$this->assertInstanceOf(MyTerm::class,         $cat);
		$this->assertInstanceOf(WhacknessLevel::class, $whackness);

		remove_filter( 'timber/term/classmap', $my_class_map );
	}

	public function testGetTermWithCallable() {
		register_taxonomy('whackness', 'post');
		$my_class_map = function() {
			return [
				'category'  => function() {
					return MyTerm::class;
				},
				'whackness' => function(WP_Term $term) {
					// return a special class depending on the WP_Term name
					return ($term->name === 'Hella Whack')
						? HellaWhackTerm::class
						: WhacknessLevel::class;
				}
			];
		};
		add_filter( 'timber/term/classmap', $my_class_map );

		$tag_id       = $this->factory->term->create(['name' => 'Toyota',        'taxonomy' => 'post_tag']);
		$cat_id       = $this->factory->term->create(['name' => 'Chevrolet',     'taxonomy' => 'category']);
		$whackness_id = $this->factory->term->create(['name' => 'Wiggity-Whack', 'taxonomy' => 'whackness']);
		$hella_id     = $this->factory->term->create(['name' => 'Hella Whack',   'taxonomy' => 'whackness']);

		$termFactory = new TermFactory();
		$tag         = $termFactory->get_term($tag_id);
		$cat         = $termFactory->get_term($cat_id);
		$whackness   = $termFactory->get_term($whackness_id);
		$hellawhack  = $termFactory->get_term($hella_id);

		$this->assertInstanceOf(Term::class,           $tag);
		$this->assertInstanceOf(MyTerm::class,         $cat);
		$this->assertInstanceOf(WhacknessLevel::class, $whackness);
		$this->assertInstanceOf(HellaWhackTerm::class, $hellawhack);

		remove_filter( 'timber/term/classmap', $my_class_map );
	}

	public function testFromArray() {
		register_taxonomy('make', 'post');
		$my_class_map = function(array $map) {
			return array_merge($map, [
				'make'  => MyTerm::class,
			]);
		};
		add_filter( 'timber/term/classmap', $my_class_map );

		$toyota = $this->factory->term->create(['name' => 'Toyota',    'taxonomy' => 'make']);
		$chevy  = $this->factory->term->create(['name' => 'Chevrolet', 'taxonomy' => 'make']);

		$termFactory = new TermFactory();
		$res = $termFactory->from(get_terms([
			'taxonomy'   => 'make',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		]));

		$this->assertTrue(true, is_array($res));
		$this->assertCount(2, $res);
		$this->assertInstanceOf(MyTerm::class, $res[0]);
		$this->assertInstanceOf(MyTerm::class, $res[1]);

		remove_filter( 'timber/term/classmap', $my_class_map );
	}
}
