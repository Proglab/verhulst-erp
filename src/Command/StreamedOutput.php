<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Output\StreamOutput;

class StreamedOutput extends StreamOutput
{
    protected function doWrite(string $message, bool $newline): void
    {
        if (
            false === @fwrite($this->getStream(), $message)
            || (
                $newline
                && (false === @fwrite($this->getStream(), \PHP_EOL))
            )
        ) {
            throw new \RuntimeException('Unable to write output.');
        }

        echo $message;

        ob_flush();
        flush();
        echo '<br>';
    }
}
