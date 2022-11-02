<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command
{
    /**
     * Affiche une question dans le output pour récupérer la valeur d'une variable.
     */
    public function askForIntValue(SymfonyStyle $io, string $description, ?int $default = 0, bool $nullable = false): ?int
    {
        if ($nullable) {
            return $io->ask($description, (string) $default, function ($number) {
                if (!$number) {
                    return null;
                }

                if (!is_numeric($number)) {
                    throw new \RuntimeException('Vous devez saisir un nombre !');
                }

                return (int) $number;
            });
        }

        return $io->ask($description, (string) $default, function ($number) {
            if (!is_numeric($number)) {
                throw new \RuntimeException('Vous devez saisir un nombre !');
            }

            return (int) $number;
        });
    }

    /**
     * Affiche une question dans le output pour récupérer la valeur float d'une variable.
     */
    public function askForFloatValue(SymfonyStyle $io, string $description, ?int $default = 0, bool $nullable = false): ?float
    {
        if ($nullable) {
            return $io->ask($description, (string) $default, function ($number) {
                if (!$number) {
                    return null;
                }

                if (!is_numeric($number)) {
                    throw new \RuntimeException('Vous devez saisir un nombre !');
                }

                return (float) $number;
            });
        }

        return $io->ask($description, (string) $default, function ($number) {
            if (!is_numeric($number)) {
                throw new \RuntimeException('Vous devez saisir un nombre !');
            }

            return (float) $number;
        });
    }

    public function askForBooleanValue(SymfonyStyle $io, string $question, bool $default = true): mixed
    {
        return $io->confirm($question, $default);
    }

    /**
     * @param null $default
     */
    public function askForStringValue(SymfonyStyle $io, string $question, $default = null, bool $nullable = false): ?string
    {
        if ($nullable) {
            return $io->ask($question, (string) $default, function ($value) {
                if (!$value) {
                    return null;
                }

                return (string) $value;
            });
        }

        return $io->ask($question, $default, function ($value) {
            if (empty($value)) {
                throw new \RuntimeException('Vous devez saisir une valeur !');
            }

            return (string) $value;
        });
    }

    public function askForChoiceValue(SymfonyStyle $io, string $question, array $choices, ?string $default): mixed
    {
        return $io->choice($question, $choices, $default);
    }

    public function askForHiddenValue(SymfonyStyle $io, string $question): mixed
    {
        return $io->askHidden($question, function ($value) {
            if (empty($value)) {
                throw new \RuntimeException('Vous devez saisir une valeur !');
            }

            return $value;
        });
    }

    /**
     * Launch external command.
     *
     * @throws \Exception
     */
    public function runExternalCommand(string $commandName, OutputInterface $output, array $arguments = []): int
    {
        $command = $this->getApplication()->find($commandName);
        $arrayInput = new ArrayInput($arguments);

        return $command->run($arrayInput, $output);
    }
}
