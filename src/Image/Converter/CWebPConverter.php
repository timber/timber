<?php

namespace Timber\Image\Converter;

use Timber\Helper;

/**
 * cwebp CLI implementation
 */
class CWebPConverter implements IConverter
{
    private $binary_path;

    public function __construct(
        private $quality
    ) {
        $this->binary_path = \apply_filters('webp_cwebp_path', '/usr/local/bin/cwebp');
    }

    public function convert($load_filename, $save_filename)
    {
        if (!\file_exists($this->binary_path)) {
            Helper::error_log('cwebp binary not found at: ' . $this->binary_path);
            return false;
        }

        $command = \sprintf(
            '%s -q %d %s -o %s 2>&1',
            \escapeshellcmd($this->binary_path),
            $this->quality,
            \escapeshellarg((string) $load_filename),
            \escapeshellarg((string) $save_filename)
        );

        \exec($command, $output, $return_var);

        if ($return_var !== 0) {
            Helper::error_log('cwebp conversion failed: ' . \implode(' ', $output));
            return false;
        }

        return true;
    }
}
