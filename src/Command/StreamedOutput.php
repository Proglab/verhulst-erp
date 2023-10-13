<?php

namespace App\Command;

use RuntimeException;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Symfony\Component\Console\Output\StreamOutput;

class StreamedOutput extends StreamOutput
{
    protected function doWrite($message, $newline)
    {
        if (
            false === @fwrite($this->getStream(), $message) ||
            (
                $newline &&
                (false === @fwrite($this->getStream(), PHP_EOL))
            )
        ) {
            throw new RuntimeException('Unable to write output.');
        }

        echo $message;

        ob_flush();
        flush();
        echo '<br>';
    }
}