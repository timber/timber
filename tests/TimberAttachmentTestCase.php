<?php

namespace Timber\Tests;

use Timber\Image;
use Timber\Timber;

/**
 * Base test case for attachment-related tests.
 *
 * Provides helper methods for creating attachments with real images
 * when image manipulation testing is required.
 */
class TimberAttachmentTestCase extends TimberIntegrationTestCase
{
    /**
     * Create an attachment with a real image file.
     *
     * Use this when you need actual image manipulation (resize, etc.).
     * For tests that just need an attachment ID, use:
     *   static::factory()->attachment->create()
     *
     * @param int $parent_id Parent post ID (0 for no parent)
     * @param string $file Filename from Fixtures/assets/
     * @return int Attachment ID
     */
    protected function createAttachmentWithImage(int $parent_id = 0, string $file = 'arch.jpg'): int
    {
        return static::factory()->attachment
            ->with_image($this->getFixtureAsset($file))
            ->create([
                'post_parent' => $parent_id,
            ]);
    }

    /**
     * Create a Timber Image object with a real image file.
     *
     * @param string $file Filename from Fixtures/assets/
     * @return Image
     */
    protected function createTimberImage(string $file = 'arch.jpg'): Image
    {
        $id = $this->createAttachmentWithImage(0, $file);
        return Timber::get_image($id);
    }

    /**
     * Replace one attachment's file with another's.
     *
     * @param int $targetId Attachment to update
     * @param int $sourceId Attachment to copy from (will be deleted)
     */
    protected function replaceAttachmentFile(int $targetId, int $sourceId): void
    {
        $uploadDir = \wp_get_upload_dir();
        $newFile = $uploadDir['basedir'] . '/' . \get_post_meta($sourceId, '_wp_attached_file', true);
        $oldFile = $uploadDir['basedir'] . '/' . \get_post_meta($targetId, '_wp_attached_file', true);

        if (!\file_exists(\dirname($oldFile))) {
            \mkdir(\dirname($oldFile), 0777, true);
        }

        \copy($newFile, $oldFile);
        $meta = \wp_generate_attachment_metadata($targetId, $oldFile);
        \wp_update_attachment_metadata($targetId, $meta);
        \wp_delete_post($sourceId, true);
    }

    /**
     * Filter callback to add language parameter to home URL.
     */
    public function addLangToHome($url, $path, $orig_scheme, $blog_id): string
    {
        return "$url?lang=en";
    }
}
