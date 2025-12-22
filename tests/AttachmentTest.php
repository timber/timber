<?php

namespace Timber\Tests;

use Mantle\Testing\Attributes\PermalinkStructure;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\Attachment;
use Timber\Image;
use Timber\Timber;
use Timber\URLHelper;

#[Group('posts-api')]
#[Group('attachments')]
class AttachmentTest extends TimberAttachmentTestCase
{
    public function testGetAttachmentByUrl()
    {
        $pid = static::factory()->post->create();
        $iid = $this->createAttachmentWithImage($pid, 'dummy-pdf.pdf');
        $original = Timber::get_post($iid);
        $url = $original->src();

        $attachment = Timber::get_attachment_by('url', $url);

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals($iid, $attachment->ID);
    }

    public function testGetAttachmentByPath()
    {
        $pid = static::factory()->post->create();
        $iid = $this->createAttachmentWithImage($pid, 'dummy-pdf.pdf');
        $path = URLHelper::url_to_file_system(Timber::get_post($iid)->src());

        $attachment = Timber::get_attachment_by('path', $path);

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals($iid, $attachment->ID);
    }

    public function testGetAttachmentByPathRelative()
    {
        $pid = static::factory()->post->create();
        $iid = $this->createAttachmentWithImage($pid, 'dummy-pdf.pdf');
        $path = URLHelper::url_to_file_system(Timber::get_post($iid)->src());

        $attachment = Timber::get_attachment_by('path', \str_replace(ABSPATH, '/', $path));

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals($iid, $attachment->ID);
    }

    public function testGetAttachmentBy()
    {
        $pid = static::factory()->post->create();
        $iid = $this->createAttachmentWithImage($pid, 'dummy-pdf.pdf');
        $url = Timber::get_post($iid)->src();
        $path = URLHelper::url_to_file_system($url);

        $this->assertInstanceOf(Attachment::class, Timber::get_attachment_by($url));
        $this->assertInstanceOf(Attachment::class, Timber::get_attachment_by($path));
    }

    public function testGetImageByUrl()
    {
        $pid = static::factory()->post->create();
        $iid = $this->createAttachmentWithImage($pid, 'jarednova.jpeg');
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

    public function testGetAttachmentByUrlDoingItWrong()
    {
        $this->setExpectedIncorrectUsage('Timber::get_attachment_by()');
        $this->assertNull(Timber::get_attachment_by('url'));
    }

    public function testGetAttachmentByPathDoingItWrong()
    {
        $this->setExpectedIncorrectUsage('Timber::get_attachment_by()');
        $this->assertNull(Timber::get_attachment_by('path'));
    }

    public function testAttachmentByExtension()
    {
        // Add support for "uploading" WEBP images.
        $this->add_filter_temporarily('upload_mimes', fn ($types) => \array_merge($types, [
            'webp' => 'image/webp',
        ]));

        // Create attachments with different extensions.
        $files = [
            'hebrew.jpg',
            'jarednova.jpeg',
            'robocop.gif',
            'flag.png',
            'mountains.webp',
            'dummy-pdf.pdf',
            'white-castle.tif',
        ];
        $attachment_ids = \array_map(fn ($file) => $this->createAttachmentWithImage(0, $file), $files);

        // Instantiate our various attachment posts.
        $attachments = \array_map(Timber::get_post(...), $attachment_ids);

        $this->assertInstanceOf(Image::class, $attachments[0]); // hebrew.jpg
        $this->assertInstanceOf(Image::class, $attachments[1]); // jarednova.jpeg
        $this->assertInstanceOf(Image::class, $attachments[2]); // robocop.gif
        $this->assertInstanceOf(Image::class, $attachments[3]); // flag.png
        $this->assertInstanceOf(Image::class, $attachments[4]); // mountains.webp

        // PDFs and TIFs should be returned as Attachments but NOT images.
        $this->assertEquals(Attachment::class, $attachments[5] !== null ? $attachments[5]::class : self::class);
        $this->assertEquals(Attachment::class, $attachments[6] !== null ? $attachments[6]::class : self::class);
    }

    public function testAttachmentWithExtensionFilter()
    {
        // Add support for "uploading" WEBP images.
        $this->add_filter_temporarily('upload_mimes', fn ($types) => \array_merge($types, [
            'tiff|tif' => 'image/tiff',
            'webp' => 'image/webp',
        ]));

        // Create attachments with different extensions.
        $files = [
            'hebrew.jpg',
            'jarednova.jpeg',
            'robocop.gif',
            'flag.png',
            'mountains.webp',
            'dummy-pdf.pdf',
            'white-castle.tif',
        ];
        $attachment_ids = \array_map(fn ($file) => $this->createAttachmentWithImage(0, $file), $files);

        $this->add_filter_temporarily('timber/post/image_extensions', fn () => ['webp', 'pdf', 'tif']);

        // Instantiate our various attachment posts.
        $attachments = \array_map(Timber::get_post(...), $attachment_ids);

        $this->assertEquals(Attachment::class, $attachments[0] !== null ? $attachments[0]::class : self::class); // hebrew.jpg
        $this->assertEquals(Attachment::class, $attachments[1] !== null ? $attachments[1]::class : self::class); // jarednova.jpeg
        $this->assertEquals(Attachment::class, $attachments[2] !== null ? $attachments[2]::class : self::class); // robocop.gif
        $this->assertEquals(Attachment::class, $attachments[3] !== null ? $attachments[3]::class : self::class); // flag.png
        $this->assertEquals(Image::class, $attachments[4] !== null ? $attachments[4]::class : self::class); // mountains.webp
        $this->assertEquals(Image::class, $attachments[5] !== null ? $attachments[5]::class : self::class); // dummy-pdf.pdf
        $this->assertEquals(Image::class, $attachments[6] !== null ? $attachments[6]::class : self::class); // white-castle.tif
    }

    #[PermalinkStructure('/%postname%/')]
    public function testAttachmentLink()
    {
        $attach = $this->createAttachmentWithImage();
        $image = Timber::get_post($attach);
        $links = [];
        $links[] = 'http://example.org/' . $image->post_name . '/';
        $links[] = 'http://example.org/?attachment_id=' . $image->ID;
        $this->assertContains($image->link(), $links);
    }

    public function testAttachmentInitWithWP_Post()
    {
        $aid = $this->createAttachmentWithImage();
        $wp_post = \get_post($aid);
        $attach = Timber::get_post($wp_post);
        $this->assertEquals($wp_post->ID, $attach->id);
    }

    public function testAttachmentAcfArray()
    {
        $post_id = static::factory()->post->create();
        $filename = $this->copyImageToUploads('arch.jpg');

        $attachment = [
            'post_mime_type' => 'image/jpeg',
            'post_title' => \preg_replace('/\.[^.]+$/', '', \basename((string) $filename)),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attach_id = \wp_insert_attachment($attachment, $filename, $post_id);
        $image = Timber::get_post([
            'ID' => $attach_id,
        ]);
        $path = \explode('/', (string) $image->file);

        $this->assertEquals('arch.jpg', $path[2]);
    }

    public function testInitFromID()
    {
        $pid = static::factory()->post->create();
        $filename = $this->copyImageToUploads('arch.jpg');
        $attachment = [
            'post_title' => 'The Arch',
            'post_content' => '',
        ];
        $iid = \wp_insert_attachment($attachment, $filename, $pid);
        $attachment = Timber::get_post($iid);
        $this->assertEquals('The Arch', $attachment->title());
    }

    public function testPathInfo()
    {
        $pid = static::factory()->post->create();
        $filename = $this->copyImageToUploads('arch.jpg');
        $attachment = [
            'post_title' => 'The Arch',
            'post_content' => '',
        ];
        $iid = \wp_insert_attachment($attachment, $filename, $pid);
        $image = Timber::get_attachment_by('path', $filename);
        $path_parts = $image->pathinfo();
        $this->assertEquals('jpg', $path_parts['extension']);
    }

    public function testTimberAttachmentSrc()
    {
        $iid = $this->createAttachmentWithImage();
        $attachment = Timber::get_post($iid);
        $post = \get_post($iid);
        $str = '{{ get_post(post).src }}';
        $result = Timber::compile_string($str, [
            'post' => $post,
        ]);
        $this->assertEquals($attachment->src(), $result);
    }

    // Test document like pdf, docx
    public function testAttachmentSrc()
    {
        $pid = static::factory()->post->create();
        $iid = $this->createAttachmentWithImage($pid, 'dummy-pdf.pdf');
        $attachment = Timber::get_post($iid);
        $str = '{{ get_post(post).src }}';
        $result = Timber::compile_string($str, [
            'post' => $iid,
        ]);
        $this->assertEquals($attachment->src(), $result);
    }

    public function testFileSize()
    {
        $pid = static::factory()->post->create();
        $iid = $this->createAttachmentWithImage($pid, 'dummy-pdf.pdf');
        $attachment = Timber::get_post($iid);
        $this->assertSame(16555, $attachment->size());
    }

    public function testFilePath()
    {
        $pid = static::factory()->post->create();
        $iid = $this->createAttachmentWithImage($pid, 'dummy-pdf.pdf');
        $attachment = Timber::get_post($iid);
        // Path should be relative to ABSPATH and contain the uploads directory
        $this->assertStringStartsWith('wp-content/uploads/' . \date('Y/m') . '/dummy-pdf', $attachment->path());
        $this->assertStringEndsWith('.pdf', $attachment->path());
    }

    public function testFileSizeMissingInMetadata()
    {
        $pid = static::factory()->post->create();
        $iid = $this->createAttachmentWithImage($pid, 'dummy-pdf.pdf');
        // Remove metadata to test fallback behavior
        \delete_post_meta($iid, '_wp_attachment_metadata');
        $attachment = Timber::get_post($iid);
        $this->assertSame(16555, $attachment->size());
    }

    public function testFileExtension()
    {
        $pid = static::factory()->post->create();
        $iid = $this->createAttachmentWithImage($pid, 'dummy-pdf.pdf');
        $str = '{{ get_post(post).extension }}';
        $result = Timber::compile_string($str, [
            'post' => $iid,
        ]);
        $this->assertEquals('pdf', $result);
    }

    /**
     * @return void
     */
    #[Ticket('https://github.com/timber/timber/issues/2607')]
    public function testAttachmentCaption()
    {
        $caption = 'Hummingbirds can’t walk.';
        $post_id = static::factory()->post->create();
        $attachment_id = $this->createAttachmentWithImage($post_id, 'dummy-pdf.pdf');
        \wp_update_post([
            'ID' => $attachment_id,
            'post_excerpt' => $caption,
        ]);
        $attachment = Timber::get_post($attachment_id);

        $this->assertEquals($caption, $attachment->caption());
    }
}
