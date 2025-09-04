<?php
declare(strict_types=1);

namespace FBM\Tests\Support;

/**
 * Simple, serializable stand-in for WP_Screen.
 */
final class ScreenStub
{
    public string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
