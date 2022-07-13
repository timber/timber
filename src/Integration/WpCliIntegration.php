<?php

namespace Timber\Integration;

use Timber\Integration\CLI\TimberCommand;
use WP_CLI;

class WpCliIntegration implements IntegrationInterface
{
    public function should_init(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }

    public function init(): void
    {
        WP_CLI::add_command('timber', TimberCommand::class);
    }
}
