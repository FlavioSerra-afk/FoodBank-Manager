<?php
/**
 * CLI command to output plugin version.
 *
 * @package FBM\CLI
 */

declare(strict_types=1);

namespace FBM\CLI;

use FoodBankManager\Core\Plugin;

final class VersionCommand {
    private IO $io;

    public function __construct(?IO $io = null) {
        $this->io = $io ?? new WpCliIO();
    }

    /**
     * Invoke the command.
     *
     * @param array $args Positional arguments.
     * @param array $assoc_args Associative arguments.
     */
    public function __invoke(array $args, array $assoc_args): void {
        $this->io->line(Plugin::VERSION);
    }
}
