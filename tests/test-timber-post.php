<?php

require_once 'php/timber-term-subclass.php';

/**
 * @group posts-api
 * @group users-api
 * @group terms-api
 * @group post-terms
 */
class TestTimberPost extends Timber_UnitTestCase
{
    public function testGetPostWithNoPosts()
    {
        $this->assertNull(Timber::get_post());
    }

    public function testPostObject()
    {
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $this->assertEquals('Timber\Post', get_class($post));
        $this->assertEquals($post_id, $post->ID);
    }

    public function testIDDataType()
    {
        $uid = $this->factory->post->create([
            'title' => 'Air Force Once',
        ]);
        $post = Timber::get_post($uid);
        $this->assertEquals('integer', gettype($post->id));
        $this->assertEquals('integer', gettype($post->ID));
    }

    public function testPostPasswordReqd()
    {
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $this->assertFalse($post->password_required());

        $post_id = $this->factory->post->create([
            'post_password' => 'jiggypoof',
        ]);
        $post = Timber::get_post($post_id);
        $this->assertTrue($post->password_required());
    }

    public function testNameMethod()
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'Battlestar Galactica',
        ]);
        $post = Timber::get_post($post_id);
        $this->assertEquals('Battlestar Galactica', $post->name());
    }

    public function testGetImageViaPostMeta()
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'St. Louis History',
        ]);
        $filename = TestTimberImage::copyTestAttachment('arch.jpg');
        $attachment = [
            'post_title' => 'The Arch',
            'post_content' => '',
        ];
        $iid = wp_insert_attachment($attachment, $filename, $post_id);
        update_post_meta($post_id, 'landmark', $iid);
        $post = Timber::get_post($post_id);

        $image = Timber::get_post($post->meta('landmark'));
        $this->assertEquals('The Arch', $image->title());
    }

    public function testPostString()
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'Gobbles',
        ]);
        $post = Timber::get_post($post_id);
        $str = Timber::compile_string('<h1>{{post}}</h1>', [
            'post' => $post,
        ]);
        $this->assertEquals('<h1>Gobbles</h1>', $str);
    }

    public function testFalseParent()
    {
        $pid = $this->factory->post->create();
        $filename = TestTimberImage::copyTestAttachment('arch.jpg');
        $attachment = [
            'post_title' => 'The Arch',
            'post_content' => '',
        ];
        $iid = wp_insert_attachment($attachment, $filename, $pid);
        update_post_meta($iid, 'architect', 'Eero Saarinen');
        $image = Timber::get_post($iid);
        $parent = $image->parent();
        $this->assertEquals($pid, $parent->ID);
        $this->assertFalse($parent->parent());
    }

    public function testPostOnSingle()
    {
        $post_id = $this->factory->post->create();
        $this->go_to(home_url('/?p=' . $post_id));
        $post = Timber::get_post();
        $this->assertEquals($post_id, $post->ID);
    }

    public function testPostOnSingleQuery()
    {
        $this->factory->post->create();
        $post_id = $this->factory->post->create();
        $this->go_to(home_url('/?p=' . $post_id));

        $post = Timber::get_post($post_id);
        $this->assertEquals($post_id, $post->ID);
    }

    public function testPostOnSingleQueryNoParams()
    {
        $post_id = $this->factory->post->create();
        $this->go_to(home_url('/?p=' . $post_id));
        $post = Timber::get_post();
        $this->assertEquals($post_id, $post->ID);
        $this->assertEquals($post_id, get_the_ID());
    }

    public function testNonexistentProperty()
    {
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $this->assertFalse($post->zebra);
    }

    public function testNonexistentMethod()
    {
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $template = '{{post.donkey}}';
        $str = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertSame('', $str);
    }

    public function testNext()
    {
        $posts = [];
        for ($i = 0; $i < 2; $i++) {
            $j = $i + 1;
            $posts[] = $this->factory->post->create([
                'post_date' => '2014-02-0' . $j . ' 12:00:00',
            ]);
        }
        $firstPost = Timber::get_post($posts[0]);
        $nextPost = Timber::get_post($posts[1]);
        $this->assertEquals($firstPost->next()->ID, $nextPost->ID);
    }

    public function testNextCategory()
    {
        $posts = [];
        for ($i = 0; $i < 4; $i++) {
            $j = $i + 1;
            $posts[] = $this->factory->post->create([
                'post_date' => '2014-02-0' . $j . ' 12:00:00',
            ]);
        }
        wp_set_object_terms($posts[0], 'TestMe', 'category', false);
        wp_set_object_terms($posts[2], 'TestMe', 'category', false);
        $firstPost = Timber::get_post($posts[0]);
        $nextPost = Timber::get_post($posts[2]);
        $this->assertEquals($firstPost->next('category')->ID, $nextPost->ID);
    }

    public function testNextCustomTax()
    {
        register_taxonomy('pizza', 'post');
        $posts = [];
        for ($i = 0; $i < 4; $i++) {
            $j = $i + 1;
            $posts[] = $this->factory->post->create([
                'post_date' => '2014-02-0' . $j . ' 12:00:00',
            ]);
        }
        wp_set_object_terms($posts[0], 'Cheese', 'pizza', false);
        wp_set_object_terms($posts[2], 'Cheese', 'pizza', false);
        wp_set_object_terms($posts[3], 'Mushroom', 'pizza', false);
        $firstPost = Timber::get_post($posts[0]);
        $nextPost = Timber::get_post($posts[2]);
        $this->assertEquals($firstPost->next('pizza')->ID, $nextPost->ID);
    }

    public function testPrev()
    {
        $posts = [];
        for ($i = 0; $i < 2; $i++) {
            $j = $i + 1;
            $posts[] = $this->factory->post->create([
                'post_date' => '2014-02-0' . $j . ' 12:00:00',
            ]);
        }
        $lastPost = Timber::get_post($posts[1]);
        $prevPost = Timber::get_post($posts[0]);
        $this->assertEquals($lastPost->prev()->ID, $prevPost->ID);
    }

    public function testPrevCustomTax()
    {
        register_taxonomy('pizza', 'post');
        $posts = [];
        for ($i = 0; $i < 3; $i++) {
            $j = $i + 1;
            $posts[] = $this->factory->post->create([
                'post_date' => '2014-02-0' . $j . ' 12:00:00',
                'post_title' => "Pizza $j is so good!",
            ]);
        }
        $cat = wp_insert_term('Cheese', 'pizza');
        self::set_object_terms($posts[0], $cat, 'pizza', false);
        self::set_object_terms($posts[2], $cat, 'pizza', false);
        $lastPost = Timber::get_post($posts[2]);
        $this->assertEquals($posts[0], $lastPost->prev('pizza')->ID);
    }

    public function testPrevCategory()
    {
        $posts = [];
        for ($i = 0; $i < 3; $i++) {
            $j = $i + 1;
            $posts[] = $this->factory->post->create([
                'post_date' => '2014-02-0' . $j . ' 12:00:00',
            ]);
        }
        $cat = wp_insert_term('TestMe', 'category');
        self::set_object_terms($posts[0], $cat, 'category', false);
        self::set_object_terms($posts[2], $cat, 'category', false);
        $lastPost = Timber::get_post($posts[2]);
        $prevPost = Timber::get_post($posts[0]);
        $this->assertEquals($lastPost->prev('category')->ID, $prevPost->ID);
    }

    public function testNextWithDraftAndFallover()
    {
        $posts = [];
        for ($i = 0; $i < 3; $i++) {
            $j = $i + 1;
            $posts[] = $this->factory->post->create([
                'post_date' => '2014-02-0' . $j . ' 12:00:00',
            ]);
        }
        $firstPost = Timber::get_post($posts[0]);
        $nextPost = Timber::get_post($posts[1]);
        $nextPostAfter = Timber::get_post($posts[2]);
        wp_update_post([
            'ID' => $nextPost->ID,
            'post_status' => 'draft',
        ]);
        $this->assertEquals($nextPostAfter->ID, $firstPost->next()->ID);
    }

    public function testNextWithDraft()
    {
        $posts = [];
        for ($i = 0; $i < 2; $i++) {
            $j = $i + 1;
            $posts[] = $this->factory->post->create([
                'post_date' => '2014-02-0' . $j . ' 12:00:00',
            ]);
        }
        $firstPost = Timber::get_post($posts[0]);
        $nextPost = Timber::get_post($posts[1]);
        $nextPost->post_status = 'draft';
        wp_update_post($nextPost);
        $nextPostTest = $firstPost->next();
        // because $nextPost has a status of "draft" now (and thus isn't public)
        // it should not be returned when we call $firstPost->next();
        $this->assertFalse($nextPostTest);
    }

    public function testPostInitObject()
    {
        $post_id = $this->factory->post->create();
        $post = get_post($post_id);
        $post = Timber::get_post($post);
        $this->assertEquals($post->ID, $post_id);
    }

    public function testPostBySlug()
    {
        $post_id = $this->factory->post->create();
        $slug = Timber::get_post($post_id)->post_name;

        $postFromSlug = Timber::get_post_by('slug', $slug);

        $this->assertEquals($postFromSlug->id, $post_id);
    }

    public function testCanEdit()
    {
        $subscriber_id = $this->factory->user->create([
            'display_name' => 'Subscriber Sam',
            'user_login' => 'subsam',
            'role' => 'subscriber',
        ]);

        // Test admin role.
        wp_set_current_user(1);
        $post_id = $this->factory->post->create([
            'post_author' => 1,
        ]);
        $post = Timber::get_post($post_id);
        $this->assertTrue($post->can_edit());

        // Test subscriber role.
        wp_set_current_user($subscriber_id);
        $this->assertFalse($post->can_edit());

        wp_set_current_user(0);
    }

    public function testTitle()
    {
        $title = 'Fifteen Million Merits';
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $post->post_title = $title;
        wp_update_post($post);
        $this->assertEquals($title, trim(strip_tags($post->title())));
    }

    public function testCustomFieldExcerptRevision()
    {
        global $current_user;
        global $wp_query;

        $post_id = $this->factory->post->create([
            'post_author' => 5,
        ]);
        update_post_meta($post_id, 'test_field', 'The custom field content');

        $assertCustomFieldVal = 'This has been revised';
        $revision_id = $this->factory->post->create([
            'post_type' => 'revision',
            'post_status' => 'inherit',
            'post_parent' => $post_id,
        ]);
        update_post_meta($revision_id, 'test_field', $assertCustomFieldVal);

        $uid = $this->factory->user->create([
            'user_login' => 'timber',
            'user_pass' => 'timber',
        ]);
        $user = wp_set_current_user($uid);
        $user->add_role('administrator');

        $wp_query->queried_object_id = $post_id;
        $wp_query->queried_object = get_post($post_id);
        $_GET['preview'] = true;
        $_GET['preview_nonce'] = wp_create_nonce('post_preview_' . $post_id);
        $post = Timber::get_post($post_id);
        $str_direct = Timber::compile_string('{{post.test_field}}', [
            'post' => $post,
        ]);
        $str_getfield = Timber::compile_string('{{post.meta(\'test_field\')}}', [
            'post' => $post,
        ]);

        $this->assertEquals($assertCustomFieldVal, $str_direct);
        $this->assertEquals($assertCustomFieldVal, $str_getfield);
    }

    public function testContent()
    {
        $quote = 'The way to do well is to do well.';
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $post->post_content = $quote;
        wp_update_post($post);
        $this->assertEquals($quote, trim(strip_tags($post->content())));
    }

    public function testContentPaged()
    {
        $quote = $page1 = 'The way to do well is to do well.';
        $quote .= '<!--nextpage-->';
        $quote .= $page2 = "And do not let your tongue get ahead of your mind.";

        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $post->post_content = $quote;
        wp_update_post($post);

        $this->assertEquals($page1, trim(strip_tags($post->content(1))));
        $this->assertEquals($page2, trim(strip_tags($post->content(2))));
    }

    public function testPagedContent()
    {
        $quote = $page1 = 'Named must your fear be before banish it you can.';
        $quote .= '<!--nextpage-->';
        $quote .= $page2 = "No, try not. Do or do not. There is no try.";

        $post_id = $this->factory->post->create([
            'post_content' => $quote,
        ]);

        $this->go_to(get_permalink($post_id));

        setup_postdata(get_post($post_id));

        $post = Timber::get_post();
        $this->assertEquals($page1, trim(strip_tags($post->paged_content())));

        $pagination = $post->pagination();
        $this->go_to($pagination['pages'][1]['link']);

        setup_postdata(get_post($post_id));
        $post = Timber::get_post();

        $this->assertEquals($page2, trim(strip_tags($post->paged_content())));
    }

    public function testPostParent()
    {
        $parent_id = $this->factory->post->create();
        $child_id = $this->factory->post->create([
            'post_parent' => $parent_id,
        ]);
        $child_post = Timber::get_post($child_id);
        $this->assertEquals($parent_id, $child_post->parent()->ID);
    }

    public function testPostSlug()
    {
        $pid = $this->factory->post->create([
            'post_name' => 'the-adventures-of-tom-sawyer',
        ]);
        $post = Timber::get_post($pid);
        $this->assertEquals('the-adventures-of-tom-sawyer', $post->slug);
    }

    public function testPostAuthor()
    {
        $author_id = $this->factory->user->create([
            'display_name' => 'Jared Novack',
            'user_login' => 'jared-novack',
        ]);
        $pid = $this->factory->post->create([
            'post_author' => $author_id,
        ]);
        $post = Timber::get_post($pid);
        $this->assertEquals('jared-novack', $post->author()->slug());
        $this->assertEquals('Jared Novack', $post->author()->name());
        $template = 'By {{post.author}}';
        $authorCompile = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $template = 'By {{post.author.name}}';
        $authorNameCompile = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertEquals($authorCompile, $authorNameCompile);
        $this->assertEquals('By Jared Novack', $authorCompile);
    }

    public function testPostAuthorInTwig()
    {
        $author_id = $this->factory->user->create([
            'display_name' => 'Jon Stewart',
            'user_login' => 'jon-stewart',
        ]);
        $pid = $this->factory->post->create([
            'post_author' => $author_id,
        ]);
        $post = Timber::get_post($pid);
        $this->assertEquals('jon-stewart', $post->author()->slug());
        $this->assertEquals('Jon Stewart', $post->author()->name());
        $template = 'By {{post.author}}';
        $authorCompile = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $template = 'By {{post.author.name}}';
        $authorNameCompile = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertEquals($authorCompile, $authorNameCompile);
        $this->assertEquals('By Jon Stewart', $authorCompile);
    }

    public function testPostModifiedAuthor()
    {
        $author_id = $this->factory->user->create([
            'display_name' => 'Woodward',
            'user_login' => 'bob-woodward',
        ]);
        $mod_author_id = $this->factory->user->create([
            'display_name' => 'Bernstein',
            'user_login' => 'carl-bernstein',
        ]);
        $pid = $this->factory->post->create([
            'post_author' => $author_id,
        ]);
        $post = Timber::get_post($pid);
        $this->assertEquals('bob-woodward', $post->author()->slug());
        $this->assertEquals('bob-woodward', $post->modified_author()->slug());
        $this->assertEquals('Woodward', $post->author()->name());
        $this->assertEquals('Woodward', $post->modified_author()->name());
        update_post_meta($pid, '_edit_last', $mod_author_id);
        $this->assertEquals('bob-woodward', $post->author()->slug());
        $this->assertEquals('carl-bernstein', $post->modified_author()->slug());
        $this->assertEquals('Woodward', $post->author()->name());
        $this->assertEquals('Bernstein', $post->modified_author()->name());
    }

    public function tear_down()
    {
        global $wpdb;
        $query = "DELETE from $wpdb->users WHERE ID > 1";
        $wpdb->query($query);
        $this->truncate('term_relationships');
        $this->truncate('term_taxonomy');
        $this->truncate('terms');
        $this->truncate('termmeta');
        $this->truncate('posts');
    }

    public function testPostFormat()
    {
        add_theme_support('post-formats', ['aside', 'gallery']);
        $pid = $this->factory->post->create();
        set_post_format($pid, 'aside');
        $post = Timber::get_post($pid);
        $this->assertEquals('aside', $post->format());
    }

    public function testPostClassInTwig()
    {
        $pid = $this->factory->post->create();
        $category = wp_insert_term('Uncategorized', 'category');
        self::set_object_terms($pid, $category, 'category', true);
        $post = Timber::get_post($pid);
        $str = Timber::compile_string("{{ post.class }}", [
            'post' => $post,
        ]);
        $this->assertEquals('post-' . $pid . ' post type-post status-publish format-standard hentry category-uncategorized', $str);
    }

    public function testPostClass()
    {
        $pid = $this->factory->post->create();
        $category = wp_insert_term('Uncategorized', 'category');
        self::set_object_terms($pid, $category, 'category', true);
        $post = Timber::get_post($pid);
        $this->assertEquals('post-' . $pid . ' post type-post status-publish format-standard hentry category-uncategorized', $post->post_class());
    }

    public function testCssClass()
    {
        $pid = $this->factory->post->create();
        $category = wp_insert_term('Uncategorized', 'category');
        self::set_object_terms($pid, $category, 'category', true);
        $post = Timber::get_post($pid);
        $this->assertEquals('post-' . $pid . ' post type-post status-publish format-standard hentry category-uncategorized', $post->css_class());
        $this->assertEquals('post-' . $pid . ' post type-post status-publish format-standard hentry category-uncategorized additional-css-class', $post->css_class('additional-css-class'));
    }

    public function testCssClassMagicCall()
    {
        $pid = $this->factory->post->create();
        $category = wp_insert_term('Uncategorized', 'category');
        self::set_object_terms($pid, $category, 'category', true);
        $post = Timber::get_post($pid);
        $this->assertEquals('post-' . $pid . ' post type-post status-publish format-standard hentry category-uncategorized', $post->class());
        $this->assertEquals('post-' . $pid . ' post type-post status-publish format-standard hentry category-uncategorized additional-css-class', $post->class('additional-css-class'));
    }

    public function testCssClassMagicGet()
    {
        $pid = $this->factory->post->create();
        $category = wp_insert_term('Uncategorized', 'category');
        self::set_object_terms($pid, $category, 'category', true);
        $post = Timber::get_post($pid);
        $this->assertEquals('post-' . $pid . ' post type-post status-publish format-standard hentry category-uncategorized', $post->class);
    }

    public function testPostChildren()
    {
        $parent_id = $this->factory->post->create();
        $children = $this->factory->post->create_many(8, [
            'post_parent' => $parent_id,
        ]);
        $parent = Timber::get_post($parent_id);
        $this->assertSame(8, count($parent->children()));
    }

    public function testPostChildrenOfInheritStatus()
    {
        $parent_id = $this->factory->post->create();
        $children = $this->factory->post->create_many(4, [
            'post_parent' => $parent_id,
        ]);
        $children = $this->factory->post->create_many(4, [
            'post_parent' => $parent_id,
            'post_status' => 'inherit',
        ]);
        $parent = Timber::get_post($parent_id);
        $this->assertSame(8, count($parent->children()));
    }

    public function testPostChildrenOfParentType()
    {
        $parent_id = $this->factory->post->create([
            'post_type' => 'foo',
        ]);
        $children = $this->factory->post->create_many(8, [
            'post_parent' => $parent_id,
        ]);
        $children = $this->factory->post->create_many(4, [
            'post_parent' => $parent_id,
            'post_type' => 'foo',
        ]);
        $parent = Timber::get_post($parent_id);
        $this->assertSame(4, count($parent->children('parent')));
    }

    public function testPostChildrenWithArray()
    {
        $parent_id = $this->factory->post->create([
            'post_type' => 'foo',
        ]);
        $children = $this->factory->post->create_many(8, [
            'post_parent' => $parent_id,
            'post_type' => 'bar',
        ]);
        $children = $this->factory->post->create_many(4, [
            'post_parent' => $parent_id,
            'post_type' => 'foo',
        ]);
        $parent = Timber::get_post($parent_id);
        $this->assertSame(12, count($parent->children(['foo', 'bar'])));
    }

    public function testPostChildrenWithArguments()
    {
        $parent_id = $this->factory->post->create([
            'post_type' => 'foo',
        ]);
        $children = $this->factory->post->create_many(4, [
            'post_parent' => $parent_id,
            'post_type' => 'foo',
            'post_status' => 'private',
        ]);
        $children = $this->factory->post->create_many(8, [
            'post_parent' => $parent_id,
            'post_type' => 'foo',
        ]);
        $parent = Timber::get_post($parent_id);
        $this->assertSame(4, count($parent->children([
            'post_type' => 'foo',
            'post_status' => 'private',
        ])));
    }

    public function testPostNoConstructorArgument()
    {
        $pid = $this->factory->post->create();
        $this->go_to('?p=' . $pid);
        $post = Timber::get_post();
        $this->assertEquals($pid, $post->ID);
    }

    public function testPostPathUglyPermalinks()
    {
        update_option('permalink_structure', '');
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $this->assertEquals('http://example.org/?p=' . $pid, $post->link());
        $this->assertEquals('/?p=' . $pid, $post->path());
    }

    public function testPostPathPrettyPermalinks()
    {
        $struc = '/blog/%year%/%monthnum%/%postname%/';
        update_option('permalink_structure', $struc);
        $pid = $this->factory->post->create([
            'post_date' => '2014-05-28',
        ]);
        $post = Timber::get_post($pid);
        $this->assertStringStartsWith('http://example.org/blog/2014/05/post-title', $post->link());
        $this->assertStringStartsWith('/blog/2014/05/post-title', $post->path());
    }

    public function testPostCategory()
    {
        $cat = wp_insert_term('News', 'category');
        $pid = $this->factory->post->create();
        self::set_object_terms($pid, $cat, 'category');
        $post = Timber::get_post($pid);
        $this->assertEquals('News', $post->category()->name);
    }

    public function testPostCategories()
    {
        $pid = $this->factory->post->create();
        $cat = wp_insert_term('Uncategorized', 'category');
        self::set_object_terms($pid, $cat, 'category');
        $post = Timber::get_post($pid);
        $category_names = ['News', 'Sports', 'Obits'];

        // Uncategorized is applied by default
        $default_categories = $post->categories();
        $this->assertEquals('uncategorized', $default_categories[0]->slug);
        foreach ($category_names as $category_name) {
            $category_name = wp_insert_term($category_name, 'category');
            self::set_object_terms($pid, $category_name, 'category');
        }

        $this->assertEquals(count($default_categories) + count($category_names), count($post->categories()));
    }

    public function testPostTags()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $tag_names = ['News', 'Sports', 'Obits'];

        foreach ($tag_names as $tag_name) {
            $tag = wp_insert_term($tag_name, 'post_tag');
            wp_set_object_terms($pid, $tag['term_id'], 'post_tag', true);
        }

        $this->assertEquals(count($tag_names), count($post->tags()));
    }

    public function testPostTerms()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $category = wp_insert_term('Uncategorized', 'category');
        self::set_object_terms($pid, $category, 'category');

        // create a new tag and associate it with the post
        $dummy_tag = wp_insert_term('whatever', 'post_tag');
        self::set_object_terms($pid, $dummy_tag, 'post_tag');

        // test expected tags
        $timber_tags = $post->terms('post_tag');
        $dummy_timber_tag = Timber::get_term($dummy_tag['term_id'], 'post_tag');
        $this->assertEquals('whatever', $timber_tags[0]->slug);
        $this->assertEquals($dummy_timber_tag, $timber_tags[0]);

        // register a custom taxonomy, create some terms in it and associate to post
        register_taxonomy('team', 'post');
        $team_names = ['Patriots', 'Bills', 'Dolphins', 'Jets'];

        foreach ($team_names as $team_name) {
            $team_term = wp_insert_term($team_name, 'team');
            self::set_object_terms($pid, $team_term, 'team');
        }

        $this->assertEquals(count($team_names), count($post->terms('team')));

        // check presence of specific terms
        $this->assertTrue($post->has_term('Uncategorized'));
        $this->assertTrue($post->has_term('whatever'));
        $this->assertTrue($post->has_term('Dolphins'));
        $this->assertTrue($post->has_term('Patriots', 'team'));

        // 4 teams + 1 tag + default category (Uncategorized)
        $this->assertSame(6, count($post->terms()));

        // test tags method - wrapper for $this->get_terms('tags')
        $this->assertEquals($post->tags(), $post->terms('post_tag'));

        // test categories method - wrapper for $this->get_terms('category')
        $this->assertEquals($post->categories(), $post->terms('category'));

        // test using an array of taxonomies
        $post_tag_terms = $post->terms(['post_tag']);
        $this->assertSame(1, count($post_tag_terms));
        $post_team_terms = $post->terms(['team']);
        $this->assertEquals(count($team_names), count($post_team_terms));

        // test multiple taxonomies
        $post_tag_and_team_terms = $post->terms(['post_tag', 'team']);
        $this->assertEquals(count($post_tag_terms) + count($post_team_terms), count($post_tag_and_team_terms));
    }

    public function testPostTermsArgumentStyle()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $category = wp_insert_term('Uncategorized', 'category');
        self::set_object_terms($pid, $category, 'category');

        // create a new tag and associate it with the post
        $dummy_tag = wp_insert_term('whatever', 'post_tag');
        self::set_object_terms($pid, $dummy_tag, 'post_tag');

        // test expected tags
        $timber_tags = $post->terms([
            'query' => [
                'taxonomy' => 'post_tag',
            ],
        ]);
        $dummy_timber_tag = Timber::get_term($dummy_tag['term_id'], 'post_tag');
        $this->assertEquals('whatever', $timber_tags[0]->slug);
        $this->assertEquals($dummy_timber_tag, $timber_tags[0]);

        // register a custom taxonomy, create some terms in it and associate to post
        register_taxonomy('team', 'post');
        $team_names = ['Patriots', 'Bills', 'Dolphins', 'Jets'];

        foreach ($team_names as $team_name) {
            $team_term = wp_insert_term($team_name, 'team');
            self::set_object_terms($pid, $team_term, 'team');
        }

        $this->assertEquals(count($team_names), count($post->terms([
            'query' => [
                'taxonomy' => 'team',
            ],
        ])));

        // test tags method - wrapper for $this->get_terms('tags')
        $this->assertEquals($post->tags(), $post->terms([
            'query' => [
                'taxonomy' => 'post_tag',
            ],
        ]));

        // test categories method - wrapper for $this->get_terms('category')
        $this->assertEquals($post->categories(), $post->terms([
            'query' => [
                'taxonomy' => 'category',
            ],
        ]));

        // test using an array of taxonomies
        $post_tag_terms = $post->terms([
            'query' => [
                'taxonomy' => ['post_tag'],
            ],
        ]);
        $this->assertSame(1, count($post_tag_terms));
        $post_team_terms = $post->terms([
            'query' => [
                'taxonomy' => ['team'],
            ],
        ]);
        $this->assertEquals(count($team_names), count($post_team_terms));

        // test multiple taxonomies
        $post_tag_and_team_terms = $post->terms([
            'query' => [
                'taxonomy' => ['post_tag', 'team'],
            ],
        ]);
        $this->assertEquals(count($post_tag_terms) + count($post_team_terms), count($post_tag_and_team_terms));
    }

    public function testPostTermsMerge()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);

        // register a custom taxonomy, create some terms in it and associate to post
        register_taxonomy('team', 'post');
        $team_names = ['Patriots', 'Bills', 'Dolphins', 'Jets'];

        foreach ($team_names as $team_name) {
            $team_term = wp_insert_term($team_name, 'team');
            self::set_object_terms($pid, $team_term, 'team');
        }

        register_taxonomy('book', 'post');
        $book_names = ['Fall of Giants', 'Winter of the World', 'Edge of Eternity'];

        foreach ($book_names as $book_name) {
            $book_term = wp_insert_term($book_name, 'book');
            self::set_object_terms($pid, $book_term, 'book');
        }

        $team_and_book_terms = $post->terms([
            'query' => [
                'taxonomy' => ['team', 'book'],
            ],
            'merge' => false,
        ]);
        $this->assertSame(4, count($team_and_book_terms['team']));
        $this->assertSame(3, count($team_and_book_terms['book']));
    }

    public function testPostTermQueryArgs()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);

        // register a custom taxonomy, create some terms in it and associate to post
        register_taxonomy('team', 'post');
        $team_names = ['Patriots', 'Bills', 'Dolphins', 'Jets'];

        foreach ($team_names as $team_name) {
            $team_term = wp_insert_term($team_name, 'team');
            self::set_object_terms($pid, $team_term, 'team');
        }

        register_taxonomy('book', 'post');
        $book_names = ['Fall of Giants', 'Winter of the World', 'Edge of Eternity'];

        foreach ($book_names as $book_name) {
            $book_term = wp_insert_term($book_name, 'book');
            self::set_object_terms($pid, $book_term, 'book');
        }

        // Test order.
        $team_and_book_terms = $post->terms([
            'query' => [
                'taxonomy' => ['team', 'book'],
                'orderby' => 'name',
            ],
        ]);

        $this->assertEquals('Bills', $team_and_book_terms[0]->title);
        $this->assertEquals('Edge of Eternity', $team_and_book_terms[2]->title);

        // Test number of terms
        $team_and_book_terms = $post->terms([
            'query' => [
                'taxonomy' => ['team', 'book'],
                'number' => 3,
            ],
        ]);

        $this->assertCount(3, $team_and_book_terms);

        // Test query in Twig
        $string = Timber::compile_string("{{
                post.terms({
                        query: {
                                taxonomy: ['team', 'book'],
                                number: 3,
                                orderby: 'name'
                        }
            })|join(', ') }}", [
            'post' => $post,
        ]);

        $this->assertEquals('Bills, Dolphins, Edge of Eternity', $string);
    }

    public function set_object_terms($pid, $term_info, $taxonomy = 'post_tag', $append = true)
    {
        $term_id = 0;
        if (is_array($term_info)) {
            $term_id = $term_info['term_id'];
        } elseif (is_object($term_info) && get_class($term_info) == 'WP_Error') {
            $term_id = $term_info->error_data['term_exists'];
        }
        if ($term_id) {
            wp_set_object_terms($pid, $term_id, $taxonomy, $append);
        }
    }

    public function testPostTermClass()
    {
        // create new post
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);

        // create a new tag, associate with post
        $dummy_tag = wp_insert_term('whatever', 'post_tag');
        self::set_object_terms($pid, $dummy_tag, 'post_tag');

        $this->add_filter_temporarily('timber/term/classmap', function () {
            return [
                'post_tag' => TimberTermSubclass::class,
            ];
        });

        // Test argument style.
        $terms = $post->terms([
            'query' => [
                'taxonomy' => 'post_tag',
            ],
        ]);

        $this->assertInstanceOf(TimberTermSubclass::class, $terms[0]);
    }

    public function testPostContentLength()
    {
        $crawl = "The evil leaders of Planet Spaceball having foolishly spuandered their precious atmosphere, have devised a secret plan to take every breath of air away from their peace-loving neighbor, Planet Druidia. Today is Princess Vespa's wedding day. Unbeknownest to the princess, but knowest to us, danger lurks in the stars above...";
        $pid = $this->factory->post->create([
            'post_content' => $crawl,
        ]);
        $post = Timber::get_post($pid);
        $content = trim(strip_tags($post->content(0, 6)));
        $this->assertEquals("The evil leaders of Planet Spaceball&hellip;", $content);
    }

    public function testPostTypeObject()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $pto = $post->type();
        $this->assertEquals('Posts', $pto->label);
    }

    public function testPage()
    {
        $pid = $this->factory->post->create([
            'post_type' => 'page',
            'post_title' => 'My Page',
        ]);
        $post = Timber::get_post($pid);
        $this->assertEquals($pid, $post->ID);
        $this->assertEquals('My Page', $post->title());
    }

    public function testCommentFormOnPost()
    {
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $GLOBALS['post'] = $post; // ACF throws an error if not
        $form = $post->comment_form();
        $this->assertStringStartsWith('<div id="respond"', trim($form));
    }

    public function testPostWithoutGallery()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);

        $this->assertSame(false, $post->gallery());
    }

    public function testPathAndLinkWithPort()
    {
        /* setUp */
        update_option('siteurl', 'http://example.org:3000', true);
        update_option('home', 'http://example.org:3000', true);
        self::setPermalinkStructure();
        $old_port = $_SERVER['SERVER_PORT'];
        $_SERVER['SERVER_PORT'] = 3000;
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = 'example.org';
        }

        /* test */
        $pid = $this->factory->post->create([
            'post_name' => 'my-cool-post',
        ]);
        $post = Timber::get_post($pid);
        $this->assertEquals('http://example.org:3000/my-cool-post/', $post->link());
        $this->assertEquals('/my-cool-post/', $post->path());

        /* tearDown */
        $_SERVER['SERVER_PORT'] = $old_port;
        update_option('siteurl', 'http://example.org', true);
        update_option('home', 'http://example.org', true);
    }

    public function testEditLink()
    {
        global $current_user;
        $current_user = [];

        $uid = $this->factory->user->create([
            'display_name' => 'Franklin Delano Roosevelt',
            'user_login' => 'fdr',
        ]);
        $pid = $this->factory->post->create([
            'post_author' => $uid,
        ]);
        $post = Timber::get_post($pid);
        $edit_url = $post->edit_link();
        $this->assertFalse($post->can_edit());
        $this->assertNull($edit_url);

        $user = wp_set_current_user($uid);
        $user->add_role('administrator');

        $this->assertTrue($post->can_edit());
        $this->assertEquals(
            'http://example.org/wp-admin/post.php?post=' . $pid . '&amp;action=edit',
            $post->edit_link()
        );
    }

    public function testPostThumbnailId()
    {
        // Add attachment to post.
        $post_id = $this->factory->post->create();
        $attachment_id = TestTimberImage::get_attachment($post_id);
        add_post_meta($post_id, '_thumbnail_id', $attachment_id, true);

        $post = Timber::get_post($post_id);

        $this->assertEquals($attachment_id, $post->thumbnail_id());
    }

    /**
     * @expectedIncorrectUsage Accessing the thumbnail ID through {{ post._thumbnail_id }}
     */
    public function testDeprecatedPostThumbnailIdProperty()
    {
        // Add attachment to post.
        $post_id = $this->factory->post->create();
        $attachment_id = TestTimberImage::get_attachment($post_id);
        add_post_meta($post_id, '_thumbnail_id', $attachment_id, true);

        $post = Timber::get_post($post_id);

        $this->assertEquals($attachment_id, $post->_thumbnail_id);
    }

    public function testWPObject()
    {
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);

        $this->assertInstanceOf('WP_Post', $post->wp_object());
    }
}
