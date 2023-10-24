<?php

use Timber\Factory\TermFactory;
use Timber\Term;

class MyTerm extends Term
{
}
class WhacknessLevel extends Term
{
}
class HellaWhackTerm extends Term
{
}

/**
 * @group factory
 * @group terms-api
 */
class TestTermFactory extends Timber_UnitTestCase
{
    public function tear_down()
    {
        unregister_taxonomy_for_object_type('make', 'post');
        parent::tear_down();
    }

    public function testGetTerm()
    {
        $tag_id = $this->factory->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'post_tag',
        ]);
        $cat_id = $this->factory->term->create([
            'name' => 'Chevrolet',
            'taxonomy' => 'category',
        ]);

        $termFactory = new TermFactory();
        $tag = $termFactory->from($tag_id);
        $cat = $termFactory->from($cat_id);

        // Assert that all instances are of Timber\Term
        $this->assertInstanceOf(Term::class, $tag);
        $this->assertInstanceOf(Term::class, $cat);
    }

    public function testGetTermFromInvalidId()
    {
        $termFactory = new TermFactory();
        $term = $termFactory->from(99999);

        $this->assertNull($term);
    }

    public function testGetTermFromIdString()
    {
        $term_id = $this->factory->term->create();

        $termFactory = new TermFactory();
        $term = $termFactory->from('' . $term_id);

        $this->assertInstanceOf(Term::class, $term);
        $this->assertEquals($term_id, $term->id);
    }

    public function testGetTermFromTaxonomyName()
    {
        $term_ids = $this->factory->term->create_many(3, [
            'taxonomy' => 'post_tag',
        ]);

        // by default hide_empty is true, so assign each term to a post
        wp_set_object_terms(
            $this->factory->post->create(),
            $term_ids,
            'post_tag'
        );

        $termFactory = new TermFactory();
        $terms = $termFactory->from('post_tag');

        $this->assertCount(3, $terms);
        foreach ($terms as $term) {
            $this->assertInstanceOf(Term::class, $term);
        }
    }

    public function testGetTermWithClassmapFilter()
    {
        register_taxonomy('whackness', 'post');
        $my_class_map = function () {
            return [
                'post_tag' => MyTerm::class,
                'category' => MyTerm::class,
                'whackness' => WhacknessLevel::class,
            ];
        };
        $this->add_filter_temporarily('timber/term/classmap', $my_class_map);

        $tag_id = $this->factory->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'post_tag',
        ]);
        $cat_id = $this->factory->term->create([
            'name' => 'Chevrolet',
            'taxonomy' => 'category',
        ]);
        $whackness_id = $this->factory->term->create([
            'name' => 'Wiggity-Whack',
            'taxonomy' => 'whackness',
        ]);

        $termFactory = new TermFactory();
        $tag = $termFactory->from($tag_id);
        $cat = $termFactory->from($cat_id);
        $whackness = $termFactory->from($whackness_id);

        $this->assertTrue(MyTerm::class === get_class($tag));
        $this->assertTrue(MyTerm::class === get_class($cat));
        $this->assertTrue(WhacknessLevel::class === get_class($whackness));
    }

    public function testGetTermWithClassFilter()
    {
        $my_class_filter = function () {
            return WhacknessLevel::class;
        };
        $this->add_filter_temporarily('timber/term/class', $my_class_filter, 10, 2);

        $cat_id = $this->factory->term->create([
            'name' => 'Chevrolet',
            'taxonomy' => 'category',
        ]);

        $termFactory = new TermFactory();
        $cat = $termFactory->from($cat_id);

        $this->assertTrue(WhacknessLevel::class === get_class($cat));
    }

    public function testGetTermWithCallable()
    {
        register_taxonomy('whackness', 'post');
        $my_class_map = function () {
            return [
                'category' => function () {
                    return MyTerm::class;
                },
                'whackness' => function (WP_Term $term) {
                    // return a special class depending on the WP_Term name
                    return ($term->name === 'Hella Whack')
                        ? HellaWhackTerm::class
                        : WhacknessLevel::class;
                },
            ];
        };
        $this->add_filter_temporarily('timber/term/classmap', $my_class_map);

        $tag_id = $this->factory->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'post_tag',
        ]);
        $cat_id = $this->factory->term->create([
            'name' => 'Chevrolet',
            'taxonomy' => 'category',
        ]);
        $whackness_id = $this->factory->term->create([
            'name' => 'Wiggity-Whack',
            'taxonomy' => 'whackness',
        ]);
        $hella_id = $this->factory->term->create([
            'name' => 'Hella Whack',
            'taxonomy' => 'whackness',
        ]);

        $termFactory = new TermFactory();
        $tag = $termFactory->from($tag_id);
        $cat = $termFactory->from($cat_id);
        $whackness = $termFactory->from($whackness_id);
        $hellawhack = $termFactory->from($hella_id);

        $this->assertTrue(Term::class === get_class($tag));
        $this->assertTrue(MyTerm::class === get_class($cat));
        $this->assertTrue(WhacknessLevel::class === get_class($whackness));
        $this->assertTrue(HellaWhackTerm::class === get_class($hellawhack));
    }

    public function testFromArray()
    {
        $a = $this->factory->term->create([
            'name' => 'A',
            'taxonomy' => 'post_tag',
        ]);
        $b = $this->factory->term->create([
            'name' => 'B',
            'taxonomy' => 'post_tag',
        ]);

        $termFactory = new TermFactory();
        $res = $termFactory->from(get_terms([
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]));

        $this->assertTrue(true, is_array($res));
        $this->assertCount(2, $res);
        $this->assertInstanceOf(Term::class, $res[0]);
        $this->assertInstanceOf(Term::class, $res[1]);
        $this->assertEquals('A', $res[0]->name);
        $this->assertEquals('B', $res[1]->name);
    }

    public function testFromArrayCustom()
    {
        register_taxonomy('make', 'post');
        $my_class_map = function (array $map) {
            return array_merge($map, [
                'make' => MyTerm::class,
            ]);
        };
        $this->add_filter_temporarily('timber/term/classmap', $my_class_map);

        $toyota = $this->factory->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'make',
        ]);
        $chevy = $this->factory->term->create([
            'name' => 'Chevrolet',
            'taxonomy' => 'make',
        ]);

        $termFactory = new TermFactory();
        $res = $termFactory->from(get_terms([
            'taxonomy' => 'make',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]));

        $this->assertTrue(true, is_array($res));
        $this->assertCount(2, $res);
        $this->assertTrue(MyTerm::class === get_class($res[0]));
        $this->assertTrue(MyTerm::class === get_class($res[1]));
    }

    public function testFromWpTermObject()
    {
        $my_class_map = function (array $map) {
            return array_merge($map, [
                'make' => MyTerm::class,
            ]);
        };
        $this->add_filter_temporarily('timber/term/classmap', $my_class_map);

        $cat_id = $this->factory->term->create([
            'name' => 'Red Herring',
            'taxonomy' => 'category',
        ]);
        $toyota_id = $this->factory->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'make',
        ]);

        $cat = get_term($cat_id);
        $toyota = get_term($toyota_id);

        $termFactory = new TermFactory();
        $this->assertTrue(MyTerm::class === get_class($termFactory->from($toyota)));
        $this->assertTrue(Term::class === get_class($termFactory->from($cat)));
    }

    public function testFromTermQuery()
    {
        register_taxonomy('make', 'post');
        $my_class_map = function (array $map) {
            return array_merge($map, [
                'make' => MyTerm::class,
            ]);
        };
        $this->add_filter_temporarily('timber/term/classmap', $my_class_map);

        $this->factory->term->create([
            'name' => 'Red Herring',
            'taxonomy' => 'category',
        ]);
        $toyota = $this->factory->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'make',
        ]);
        $chevy = $this->factory->term->create([
            'name' => 'Chevrolet',
            'taxonomy' => 'make',
        ]);

        $termFactory = new TermFactory();
        $termQuery = new WP_Term_Query([
            'taxonomy' => 'make',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $res = $termFactory->from($termQuery);

        $this->assertCount(2, $res);
        $this->assertTrue(MyTerm::class === get_class($res[0]));
        $this->assertTrue(MyTerm::class === get_class($res[1]));
    }

    public function testFromTermQueryWithFields()
    {
        $term_ids = $this->factory->term->create_many(4, [
            'taxonomy' => 'post_tag',
        ]);

        $post_id = $this->factory->post->create();
        wp_set_object_terms(
            $post_id,
            $term_ids[0],
            'post_tag'
        );
        wp_set_object_terms(
            $this->factory->post->create(),
            [$term_ids[1], $term_ids[2]],
            'post_tag'
        );

        $termFactory = new TermFactory();

        // all: array of used terms as Timber\Term object
        $termQuery = new WP_Term_Query([
            'taxonomy' => 'post_tag',
            'fields' => 'all',
        ]);
        $terms = $termFactory->from($termQuery);
        $this->assertCount(3, $terms);
        foreach ($terms as $term) {
            $this->assertInstanceOf(Term::class, $term);
        }

        // all_with_object_id: all terms used in a specific object as Timber\Term object
        $termQuery = new WP_Term_Query([
            'taxonomy' => 'post_tag',
            'fields' => 'all_with_object_id',
            'object_ids' => $post_id,
        ]);
        $terms = $termFactory->from($termQuery);
        $this->assertCount(1, $terms);
        $this->assertInstanceOf(Term::class, $terms[0]);
        $this->assertSame($terms[0]->id, $term_ids[0]);

        // register class map and repeat previous tests
        $my_class_map = function (array $map) {
            return array_merge($map, [
                'post_tag' => MyTerm::class,
            ]);
        };
        $this->add_filter_temporarily('timber/term/classmap', $my_class_map);

        // all_with_object_id: all terms used in a specific object as MyTerm object
        $termQuery = new WP_Term_Query([
            'taxonomy' => 'post_tag',
            'fields' => 'all',
        ]);
        $terms = $termFactory->from($termQuery);
        $this->assertCount(3, $terms);
        foreach ($terms as $term) {
            $this->assertInstanceOf(MyTerm::class, $term);
        }

        // all_with_object_id: all terms used in a specific object as MyTerm object
        $termQuery = new WP_Term_Query([
            'taxonomy' => 'post_tag',
            'fields' => 'all_with_object_id',
            'object_ids' => $post_id,
        ]);
        $terms = $termFactory->from($termQuery);
        $this->assertCount(1, $terms);
        $this->assertInstanceOf(MyTerm::class, $terms[0]);
        $this->assertSame($terms[0]->id, $term_ids[0]);

        // count: number of used terms as integer string value
        $termQuery = new WP_Term_Query([
            'taxonomy' => 'post_tag',
            'fields' => 'count',
        ]);
        $count = $termFactory->from($termQuery);
        $this->assertTrue(is_string($count));
        $this->assertTrue(is_numeric($count));
        $this->assertSame(intval($count), 3);

        // count: number of terms as integer string value
        $termQuery = new WP_Term_Query([
            'taxonomy' => 'post_tag',
            'fields' => 'count',
            'hide_empty' => false,
        ]);
        $count = $termFactory->from($termQuery);
        $this->assertTrue(is_string($count));
        $this->assertTrue(is_numeric($count));
        $this->assertSame(intval($count), 4);

        // ids: array of integer ids of used terms
        $termQuery = new WP_Term_Query([
            'taxonomy' => 'post_tag',
            'fields' => 'ids',
        ]);
        $ids = $termFactory->from($termQuery);
        $this->assertCount(3, $ids);
        foreach ($ids as $id) {
            $this->assertTrue(is_int($id));
            $this->assertTrue(in_array($id, $term_ids));
        }

        // names: array of strings
        $termQuery = new WP_Term_Query([
            'taxonomy' => 'post_tag',
            'fields' => 'names',
        ]);
        $names = $termFactory->from($termQuery);
        $this->assertCount(3, $names);
        foreach ($names as $name) {
            $this->assertTrue(is_string($name));
        }

        // id=>parent: array of numeric strings
        $termQuery = new WP_Term_Query([
            'taxonomy' => 'post_tag',
            'fields' => 'id=>parent',
        ]);
        $map = $termFactory->from($termQuery);
        $this->assertCount(3, $map);
        foreach ($map as $k => $v) {
            $this->assertTrue(is_int($k));
            $this->assertTrue(is_int(filter_var($v, FILTER_VALIDATE_INT)));
        }
    }

    public function testFromAssortedArray()
    {
        register_taxonomy('make', 'post');
        $my_class_map = function (array $map) {
            return array_merge($map, [
                'make' => MyTerm::class,
            ]);
        };
        $this->add_filter_temporarily('timber/term/classmap', $my_class_map);

        $geo_id = $this->factory->term->create([
            'name' => 'Geo',
            'taxonomy' => 'make',
        ]);
        $datsun_id = $this->factory->term->create([
            'name' => 'Datsun',
            'taxonomy' => 'make',
        ]);
        $studebaker_id = $this->factory->term->create([
            'name' => 'Studebaker',
            'taxonomy' => 'make',
        ]);

        $termFactory = new TermFactory();

        // pass an array with an ID, a WP_Term, and a Timber\Term instance
        $res = $termFactory->from([
            $geo_id,
            get_term($datsun_id),
            $termFactory->from($studebaker_id),
        ]);

        $this->assertCount(3, $res);
        $this->assertTrue(MyTerm::class === get_class($res[0]));
        $this->assertTrue(MyTerm::class === get_class($res[1]));
        $this->assertTrue(MyTerm::class === get_class($res[2]));
    }

    public function testFromTermQueryArray()
    {
        register_taxonomy('make', 'post');
        $my_class_map = function (array $map) {
            return array_merge($map, [
                'make' => MyTerm::class,
            ]);
        };
        $this->add_filter_temporarily('timber/term/classmap', $my_class_map);

        $this->factory->term->create([
            'name' => 'Red Herring',
            'taxonomy' => 'category',
        ]);
        $toyota = $this->factory->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'make',
        ]);
        $chevy = $this->factory->term->create([
            'name' => 'Chevrolet',
            'taxonomy' => 'make',
        ]);

        $termFactory = new TermFactory();

        $res = $termFactory->from([
            'taxonomy' => 'make',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $this->assertCount(2, $res);
        $this->assertTrue(MyTerm::class === get_class($res[0]));
        $this->assertTrue(MyTerm::class === get_class($res[1]));
    }

    public function testTermBy()
    {
        $post_tag_id = $this->factory->term->create([
            'name' => 'Security',
            'taxonomy' => 'post_tag',
        ]);
        $category_id = $this->factory->term->create([
            'name' => 'Security',
            'taxonomy' => 'category',
        ]);

        $term_post_tag = Timber::get_term_by('slug', 'security', 'post_tag');
        $this->assertEquals('post_tag', $term_post_tag->taxonomy);
        $this->assertEquals('Security', $term_post_tag->title());

        $term_category = Timber::get_term_by('name', 'Security', 'category');
        $this->assertEquals('category', $term_category->taxonomy);
        $this->assertEquals('Security', $term_category->title());
    }

    public function testTermByNoTaxonomy()
    {
        $category_id = $this->factory->term->create([
            'name' => 'Breaking News',
            'taxonomy' => 'category',
        ]);
        $terms = Timber::get_terms([
            'name' => 'Breaking News',
            'hide_empty' => false,
        ]);

        $term_category = Timber::get_term_by('name', 'Breaking News');
        $this->assertEquals('category', $term_category->taxonomy);
        $this->assertEquals('Breaking News', $term_category->title());
    }
}
