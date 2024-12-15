<?php

use Timber\Attachment;
use Timber\Image;
use Timber\Timber;
use Timber\URLHelper;

/**
 * @group posts-api
 * @group attachments
 */
class TestTimberAttachment extends TimberAttachment_UnitTestCase
{
    public function testGetAttachmentByUrl()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid, 'dummy-pdf.pdf');
        $url = Timber::get_post($iid)->src();

        $attachment = Timber::get_attachment_by('url', $url);

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals('dummy-pdf.pdf', basename($attachment->src()));
    }

    public function testGetAttachmentByPath()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid, 'dummy-pdf.pdf');
        $path = URLHelper::url_to_file_system(Timber::get_post($iid)->src());

        $attachment = Timber::get_attachment_by('path', $path);

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals('dummy-pdf.pdf', basename($attachment->src()));
    }

    public function testGetAttachmentByPathRelative()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid, 'dummy-pdf.pdf');
        $path = URLHelper::url_to_file_system(Timber::get_post($iid)->src());

        $attachment = Timber::get_attachment_by('path', str_replace(ABSPATH, '/', $path));

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals('dummy-pdf.pdf', basename($attachment->src()));
    }

    public function testGetAttachmentBy()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid, 'dummy-pdf.pdf');
        $url = Timber::get_post($iid)->src();
        $path = URLHelper::url_to_file_system($url);

        $this->assertInstanceOf(Attachment::class, Timber::get_attachment_by($url));
        $this->assertInstanceOf(Attachment::class, Timber::get_attachment_by($path));
    }

    public function testGetImageByUrl()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid, 'jarednova.jpeg');
        $url = Timber::get_post($iid)->src();

        $this->assertInstanceOf(Image::class, Timber::get_attachment_by($url));
    }

    public function testGetAttachmentByUrlNonsense()
    {
        // Nonsense URL
        $this->assertNull(Timber::get_attachment_by('url', 'life, uh, finds a way'));
        // Nonsense Path
        $this->assertNull(Timber::get_attachment_by('path', 'must go faster'));
        // Nonsense single arg
        $this->assertNull(Timber::get_attachment_by('you two, dig up, dig up dinosaurs'));
    }

    /**
     * @expectedIncorrectUsage Timber::get_attachment_by()
     */
    public function testGetAttachmentByUrlDoingItWrong()
    {
        $this->assertNull(Timber::get_attachment_by('url'));
    }

    /**
     * @expectedIncorrectUsage Timber::get_attachment_by()
     */
    public function testGetAttachmentByPathDoingItWrong()
    {
        $this->assertNull(Timber::get_attachment_by('path'));
    }

    public function testAttachmentByExtension()
    {
        // Add support for "uploading" WEBP images.
        $this->add_filter_temporarily('upload_mimes', function ($types) {
            return array_merge($types, [
                'webp' => 'image/webp',
            ]);
        });

        // Create 7 attachment posts with different extensions.
        $pids = $this->factory->post->create_many(7, [
            'post_type' => 'attachment',
        ]);
        $attachment_ids = array_map([self::class, 'get_attachment'], $pids, [
            'hebrew.jpg',
            'jarednova.jpeg',
            'robocop.gif',
            'flag.png',
            'mountains.webp',
            'dummy-pdf.pdf',
            'white-castle.tif',
        ]);

        // Instantiate our various attachment posts.
        $attachments = array_map([Timber::class, 'get_post'], $attachment_ids);

        $this->assertInstanceOf(Image::class, $attachments[0]); // hebrew.jpg
        $this->assertInstanceOf(Image::class, $attachments[1]); // jarednova.jpeg
        $this->assertInstanceOf(Image::class, $attachments[2]); // robocop.gif
        $this->assertInstanceOf(Image::class, $attachments[3]); // flag.png
        $this->assertInstanceOf(Image::class, $attachments[4]); // mountains.webp

        // PDFs and TIFs should be returned as Attachments but NOT images.
        $this->assertEquals(Attachment::class, get_class($attachments[5]));
        $this->assertEquals(Attachment::class, get_class($attachments[6]));
    }

    public function testAttachmentWithExtensionFilter()
    {
        // Add support for "uploading" WEBP images.
        $this->add_filter_temporarily('upload_mimes', function ($types) {
            return array_merge($types, [
                'tiff|tif' => 'image/tiff',
                'webp' => 'image/webp',
            ]);
        });

        // Create 7 attachment posts with different extensions.
        $pids = $this->factory->post->create_many(7, [
            'post_type' => 'attachment',
        ]);
        $attachment_ids = array_map([self::class, 'get_attachment'], $pids, [
            'hebrew.jpg',
            'jarednova.jpeg',
            'robocop.gif',
            'flag.png',
            'mountains.webp',
            'dummy-pdf.pdf',
            'white-castle.tif',
        ]);

        $this->add_filter_temporarily('timber/post/image_extensions', function () {
            // ONLY these extensions should be considered images.
            return ['webp', 'pdf', 'tif'];
        });

        // Instantiate our various attachment posts.
        $attachments = array_map([Timber::class, 'get_post'], $attachment_ids);

        $this->assertEquals(Attachment::class, get_class($attachments[0])); // hebrew.jpg
        $this->assertEquals(Attachment::class, get_class($attachments[1])); // jarednova.jpeg
        $this->assertEquals(Attachment::class, get_class($attachments[2])); // robocop.gif
        $this->assertEquals(Attachment::class, get_class($attachments[3])); // flag.png
        $this->assertEquals(Image::class, get_class($attachments[4])); // mountains.webp
        $this->assertEquals(Image::class, get_class($attachments[5])); // dummy-pdf.pdf
        $this->assertEquals(Image::class, get_class($attachments[6])); // white-castle.tif
    }

    public function testAttachmentLink()
    {
        self::setPermalinkStructure();
        $attach = self::get_attachment();
        $image = Timber::get_post($attach);
        $links = [];
        $links[] = 'http://example.org/' . $image->post_name . '/';
        $links[] = 'http://example.org/?attachment_id=' . $image->ID;
        $this->assertContains($image->link(), $links);
    }

    public function testAttachmentInitWithWP_Post()
    {
        $aid = self::get_attachment();
        $wp_post = get_post($aid);
        $attach = Timber::get_post($wp_post);
        $this->assertEquals($wp_post->ID, $attach->id);
    }

    public function testAttachmentAcfArray()
    {
        $post_id = $this->factory->post->create();
        $filename = self::copyTestAttachment('arch.jpg');

        $attachment = [
            'post_mime_type' => 'image/jpeg',
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
        $image = Timber::get_post([
            'ID' => $attach_id,
        ]);
        $path = explode('/', $image->file);

        $this->assertEquals('arch.jpg', $path[2]);
    }

    public function testInitFromID()
    {
        $pid = $this->factory->post->create();
        $filename = self::copyTestAttachment('arch.jpg');
        $attachment = [
            'post_title' => 'The Arch',
            'post_content' => '',
        ];
        $iid = wp_insert_attachment($attachment, $filename, $pid);
        $attachment = Timber::get_post($iid);
        $this->assertEquals('The Arch', $attachment->title());
    }

    public function testPathInfo()
    {
        $pid = $this->factory->post->create();
        $filename = self::copyTestAttachment('arch.jpg');
        $attachment = [
            'post_title' => 'The Arch',
            'post_content' => '',
        ];
        $iid = wp_insert_attachment($attachment, $filename, $pid);
        $image = Timber::get_attachment_by('path', $filename);
        $path_parts = $image->pathinfo();
        $this->assertEquals('jpg', $path_parts['extension']);
    }

    public function testTimberAttachmentSrc()
    {
        $iid = self::get_attachment();
        $attachment = Timber::get_post($iid);
        $post = get_post($iid);
        $str = '{{ get_post(post).src }}';
        $result = Timber::compile_string($str, [
            'post' => $post,
        ]);
        $this->assertEquals($attachment->src(), $result);
    }

    // Test document like pdf, docx
    public function testAttachmentSrc()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid, 'dummy-pdf.pdf');
        $str = '{{ get_post(post).src }}';
        $result = Timber::compile_string($str, [
            'post' => $iid,
        ]);
        $this->assertEquals('http://example.org/wp-content/uploads/' . date('Y/m') . '/dummy-pdf.pdf', $result);
    }

    public function testFileSize()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid, 'dummy-pdf.pdf');
        $attachment = Timber::get_post($iid);
        $this->assertSame(16555, $attachment->size());
    }

    public function testFilePath()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid, 'dummy-pdf.pdf');
        $attachment = Timber::get_post($iid);
        $this->assertEquals('wp-content/uploads/' . date('Y/m') . '/dummy-pdf.pdf', $attachment->path());
    }

    public function testFileSizeMissingInMetadata()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid, 'dummy-pdf.pdf', false);
        $attachment = Timber::get_post($iid);
        $this->assertSame(16555, $attachment->size());
    }

    public function testFileExtension()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid, 'dummy-pdf.pdf');
        $str = '{{ get_post(post).extension }}';
        $result = Timber::compile_string($str, [
            'post' => $iid,
        ]);
        $this->assertEquals('pdf', $result);
    }

    /**
     * @ticket https://github.com/timber/timber/issues/2607
     * @return void
     */
    public function testAttachmentCaption()
    {
        $caption = 'Hummingbirds can’t walk.';
        $post_id = $this->factory->post->create();
        $attachment_id = self::get_attachment($post_id, 'dummy-pdf.pdf');
        wp_update_post([
            'ID' => $attachment_id,
            'post_excerpt' => $caption,
        ]);
        $attachment = Timber::get_post($attachment_id);

        $this->assertEquals($caption, $attachment->caption());
    }
}
