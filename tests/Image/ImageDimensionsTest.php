<?php

namespace Timber\Tests\Image;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Timber\ImageDimensions;
use Timber\Tests\TimberIntegrationTestCase;

class ImageDimensionsTestable extends ImageDimensions
{
    public function __construct($file_loc)
    {
        parent::__construct($file_loc);
    }

    public function set_dimensions($width, $height)
    {
        $this->dimensions = [$width, $height];
    }

    public function read_svg(string $file): object
    {
        return $this->get_dimensions_svg($file);
    }
}

/**
 * A stream wrapper that records every URL libxml asks PHP to resolve, so that a test can assert
 * a DOCTYPE or entity pointing at it was never fetched. PHP's libxml glue stats a URL before it
 * opens it, hence url_stat() records too.
 */
final class ExternalResourceProbe
{
    public const SCHEME = 'timber-test';

    /**
     * @var string[]
     */
    public static array $requested = [];

    public $context;

    public function url_stat(string $path, int $flags): array|false
    {
        self::$requested[] = $path;

        return false;
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        self::$requested[] = $path;

        return false;
    }
}

#[Group('image')]
class ImageDimensionsTest extends TimberIntegrationTestCase
{
    public static function ratioProvider()
    {
        return [
            [200, 100, 2],
            [100, 200, 0.5],
        ];
    }

    #[DataProvider('ratioProvider')]
    public function testRatio($w, $h, $r)
    {
        $imageDimensions = new ImageDimensionsTestable('');
        $imageDimensions->set_dimensions($w, $h);

        $this->assertEquals($r, $imageDimensions->aspect());
    }

    public function testDimensions()
    {
        $imageDimensions = new ImageDimensionsTestable('');
        $imageDimensions->set_dimensions(100, 200);

        $this->assertEquals(100, $imageDimensions->width());
        $this->assertEquals(200, $imageDimensions->height());
    }

    public function testDimensionsFromAttachmentMetadata()
    {
        $attachment_id = $this->createAttachmentWithImage();

        // Override the metadata with known dimensions to confirm they come from metadata, not file.
        $meta = \wp_get_attachment_metadata($attachment_id);
        $meta['width'] = 640;
        $meta['height'] = 480;
        \wp_update_attachment_metadata($attachment_id, $meta);

        $imageDimensions = new ImageDimensions('', $attachment_id);

        $this->assertEquals(640, $imageDimensions->width());
        $this->assertEquals(480, $imageDimensions->height());
    }

    public function testDimensionsFallbackToFileWhenMetadataMissingDimensions()
    {
        $attachment_id = $this->createAttachmentWithImage();
        $file_loc = \get_attached_file($attachment_id);

        // Remove width/height from metadata to trigger the file fallback.
        $meta = \wp_get_attachment_metadata($attachment_id);
        unset($meta['width'], $meta['height']);
        \wp_update_attachment_metadata($attachment_id, $meta);

        $imageDimensions = new ImageDimensions($file_loc, $attachment_id);

        // Dimensions should still be readable from the file itself.
        $this->assertIsInt($imageDimensions->width());
        $this->assertIsInt($imageDimensions->height());
        $this->assertGreaterThan(0, $imageDimensions->width());
        $this->assertGreaterThan(0, $imageDimensions->height());
    }

    public function testSvgDimensionsFromViewBox()
    {
        $imageDimensions = new ImageDimensions($this->getFixtureAsset('timber-logo.svg'));

        // viewBox="0 0 530.91 158"
        $this->assertSame(531, $imageDimensions->width());
        $this->assertSame(158, $imageDimensions->height());
    }

    public function testSvgDimensionsFromWidthAndHeightAttributes()
    {
        $imageDimensions = new ImageDimensions($this->getFixtureAsset('icon-twitter.svg'));

        $this->assertSame(23, $imageDimensions->width());
        $this->assertSame(20, $imageDimensions->height());
    }

    /**
     * SVG documents and the dimensions Timber reads off their root element.
     *
     * @return iterable<string, array{string, int|null, int|null}>
     */
    public static function svgDocumentProvider(): iterable
    {
        // Size attributes.
        yield 'width and height' => ['<svg xmlns="http://www.w3.org/2000/svg" width="200" height="100"/>', 200, 100];
        yield 'width and height with px unit' => ['<svg xmlns="http://www.w3.org/2000/svg" width="200px" height="100px"/>', 200, 100];
        yield 'whitespace-padded width and height' => ['<svg xmlns="http://www.w3.org/2000/svg" width=" 200 " height=" 100 "/>', 200, 100];
        yield 'viewBox only' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"/>', 24, 24];
        // Pins the precedence Timber has applied since SVG support landed (#2421): the viewBox is
        // read first, width/height only when there is none. Changing it is a deliberate decision.
        yield 'viewBox takes precedence over width and height' => ['<svg xmlns="http://www.w3.org/2000/svg" width="200" height="100" viewBox="0 0 24 24"/>', 24, 24];
        yield 'width only falls back to viewBox' => ['<svg xmlns="http://www.w3.org/2000/svg" width="200" viewBox="0 0 50 20"/>', 50, 20];
        yield 'percent width and height fall back to viewBox' => ['<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 50 20"/>', 50, 20];
        yield 'fractional viewBox is rounded' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100.4 50.3"/>', 100, 50];
        yield 'no width, height or viewBox' => ['<svg xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1"/></svg>', 0, 0];

        // The viewBox is a list of four numbers separated by whitespace and/or commas.
        yield 'comma-separated viewBox' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0,0,40,40"/>', 40, 40];
        yield 'mixed-separator viewBox' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0, 0 40,30"/>', 40, 30];
        yield 'viewBox with runs of spaces' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0  100 50"/>', 100, 50];
        yield 'whitespace-padded viewBox' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="  0 0 40 40  "/>', 40, 40];

        // Root element. XML preserves tag case, so an uppercase or mixed-case root is still an
        // <svg>. The name is matched on its local part, so a namespace-prefixed root (as produced by
        // e.g. Wikimedia) is read too. Anything else is not an SVG, whatever attributes it carries.
        yield 'uppercase root' => ['<SVG xmlns="http://www.w3.org/2000/svg" width="200" height="100"/>', 200, 100];
        yield 'uppercase root with viewBox' => ['<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"/>', 24, 24];
        yield 'mixed-case root' => ['<Svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 8"/>', 12, 8];
        yield 'namespace-prefixed root' => ['<?xml version="1.0" encoding="UTF-8"?><svg:svg xmlns:svg="http://www.w3.org/2000/svg" width="120" height="60"/>', 120, 60];
        yield 'non-svg root with width and height' => ['<?xml version="1.0" encoding="UTF-8"?><html width="120" height="60"/>', 0, 0];
        yield 'root name merely starting with svg' => ['<svgfoo xmlns="http://www.w3.org/2000/svg" width="200" height="100"/>', 0, 0];
        yield 'svg nested in a non-svg root' => ['<html><svg xmlns="http://www.w3.org/2000/svg" width="200" height="100"/></html>', 0, 0];

        // Coarse markup uppercases attribute names too. They are matched by lower-cased local
        // name, so WIDTH/HEIGHT/VIEWBOX read like their canonical forms.
        yield 'uppercase width and height attributes' => ['<svg xmlns="http://www.w3.org/2000/svg" WIDTH="200" HEIGHT="100"/>', 200, 100];
        yield 'uppercase viewBox attribute' => ['<svg xmlns="http://www.w3.org/2000/svg" VIEWBOX="0 0 24 24"/>', 24, 24];
        yield 'fully uppercase svg' => ['<SVG XMLNS="http://www.w3.org/2000/svg" WIDTH="200" HEIGHT="100"/>', 200, 100];

        // Prolog. Everything before the root element is stepped over: declaration, byte order
        // mark, comments and a DOCTYPE, which is never fetched (LIBXML_NONET, no LIBXML_DTDLOAD).
        yield 'xml declaration' => ['<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 8"/>', 12, 8];
        yield 'utf-8 byte order mark' => ["\xEF\xBB\xBF<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 10 10\"/>", 10, 10];
        yield 'utf-16 with byte order mark' => ["\xFF\xFE" . \iconv('UTF-8', 'UTF-16LE', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"/>'), 10, 10];
        yield 'comment before root' => ['<!--<svg width="999" height="999"/>--><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 5 5"/>', 5, 5];
        yield 'public doctype' => ['<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5"/>', 10, 5];
        // The entity is referenced in the body, which is never parsed, let alone expanded.
        yield 'doctype with an internal entity' => ['<!DOCTYPE svg [<!ENTITY lol "AAAAAAAAAA">]><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><text>&lol;</text></svg>', 16, 16];

        // Unreadable documents. An empty file is caught before parsing (no size, no dimensions).
        yield 'empty file' => ['', null, null];
        yield 'plain text' => ['not an svg', 0, 0];
        yield 'malformed root' => ['<svg viewBox="0 0 broken', 0, 0];

        // Only the root is read: the (here 5000) children are never parsed.
        yield 'root with thousands of children' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40">' . \str_repeat('<rect x="1" y="1" width="2" height="2"/>', 5000) . '</svg>', 40, 40];
    }

    #[DataProvider('svgDocumentProvider')]
    public function testSvgDimensionsFromDocument(string $svg, ?int $width, ?int $height)
    {
        $file = $this->writeFileToUploads(\sanitize_title($this->dataName()) . '.svg', $svg);

        $imageDimensions = new ImageDimensions($file);

        $this->assertSame($width, $imageDimensions->width());
        $this->assertSame($height, $imageDimensions->height());
    }

    /**
     * SVG documents pointing libxml at an external resource, through the probe's scheme.
     *
     * @return iterable<string, array{string}>
     */
    public static function externalResourceProvider(): iterable
    {
        $url = ExternalResourceProbe::SCHEME . '://';

        yield 'external dtd' => ['<!DOCTYPE svg SYSTEM "' . $url . 'dtd"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5"/>'];
        yield 'external entity referenced in the body' => ['<!DOCTYPE svg [<!ENTITY xxe SYSTEM "' . $url . 'entity">]><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5">&xxe;</svg>'];
        yield 'external parameter entity' => ['<!DOCTYPE svg [<!ENTITY % xxe SYSTEM "' . $url . 'parameter"> %xxe;]><svg xmlns="http://www.w3.org/2000/svg" width="10" height="5"/>'];
    }

    /**
     * The reader must never load an external DTD or entity (XXE, SSRF). A stream wrapper stands
     * in for the resource: reading the dimensions must not touch it, while the document itself
     * is still read. Passing `LIBXML_DTDLOAD` or `LIBXML_NOENT` to the reader makes these fail.
     */
    #[DataProvider('externalResourceProvider')]
    public function testSvgReaderNeverLoadsExternalResources(string $svg)
    {
        $file = $this->writeFileToUploads(\sanitize_title($this->dataName()) . '.svg', $svg);

        ExternalResourceProbe::$requested = [];
        \stream_wrapper_register(ExternalResourceProbe::SCHEME, ExternalResourceProbe::class);

        try {
            $imageDimensions = new ImageDimensions($file);
            $width = $imageDimensions->width();
            $height = $imageDimensions->height();
        } finally {
            \stream_wrapper_unregister(ExternalResourceProbe::SCHEME);
        }

        $this->assertSame([], ExternalResourceProbe::$requested);
        $this->assertSame(10, $width);
        $this->assertSame(5, $height);
    }

    /**
     * `get_dimension()` never reaches the SVG reader with an empty path, but the reader is
     * `protected`, and `simplexml_load_file('')` used to answer false where `XMLReader::open('')`
     * throws a ValueError.
     */
    public function testSvgReaderAnswersNoDimensionsForAnEmptyPath()
    {
        $imageDimensions = new ImageDimensionsTestable('');

        $this->assertEquals((object) [
            'width' => 0.0,
            'height' => 0.0,
        ], $imageDimensions->read_svg(''));
    }

    /**
     * Only the root element is read, so a document whose body is truncated or corrupt further
     * down still reports its size.
     *
     * This holds as far as libxml's input buffering does: the parser reads the document in
     * chunks of a few hundred bytes, and an error that falls in the same chunk as the root
     * element is seen before that element is handed out. Hence the long body here.
     */
    public function testSvgDimensionsAreReadFromRootOfMalformedDocument()
    {
        $body = \str_repeat('<path d="M0 0 L1 1"/>', 1000);
        $svg = $this->writeFileToUploads(
            'malformed-body.svg',
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="40">'
            . $body . '<unclosed></svg>'
        );

        $imageDimensions = new ImageDimensions($svg);

        $this->assertSame(80, $imageDimensions->width());
        $this->assertSame(40, $imageDimensions->height());
    }

    public function testDimensionsMetadataTakesPrecedenceOverFile()
    {
        $attachment_id = $this->createAttachmentWithImage();
        $file_loc = \get_attached_file($attachment_id);

        // Set metadata dimensions that differ from the actual file dimensions.
        $meta = \wp_get_attachment_metadata($attachment_id);
        $meta['width'] = 9999;
        $meta['height'] = 8888;
        \wp_update_attachment_metadata($attachment_id, $meta);

        $imageDimensions = new ImageDimensions($file_loc, $attachment_id);

        // Should return metadata values, not actual file dimensions.
        $this->assertEquals(9999, $imageDimensions->width());
        $this->assertEquals(8888, $imageDimensions->height());
    }
}
