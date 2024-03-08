<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\ConfigureWithAttributes;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('app:upload', 'Upload a local file via ApiPlatform to submission')]
final class AppUploadCommand extends InvokableServiceCommand
{
    use ConfigureWithAttributes;
    use RunsCommands;
    use RunsProcesses;

    public function __invoke(
        IO $io,
        #[Argument(description: 'The path to the image')]
        string $filename = '',
        #[Argument(description: 'email of the user (required)')]
        string $email = 'tacman@gmail.com',

        #[Option(description: 'force upload even if file exists')]
        bool $force = false,
    ): void {
        $io->success('app:upload success.');
    }
}
