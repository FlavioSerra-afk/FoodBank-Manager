<?php
/**
 * CLI IO interface.
 *
 * @package FBM\CLI
 */

declare(strict_types=1);

namespace FBM\CLI;

interface IO {
    /** Output a line. */
    public function line(string $message): void;

    /** Output success message. */
    public function success(string $message): void;

    /** Output error message. */
    public function error(string $message): void;
}
