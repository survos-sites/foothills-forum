<?php

namespace App\Command;

use App\Entity\Field\Field;
use App\Entity\Instance;
use App\Entity\Invitation;
use App\Entity\LabelInterface;
use App\Entity\Member;
use App\Entity\Project;
use App\Entity\ProjectInterface;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Service\LibreTranslateService;
use App\Service\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\ConfigureWithAttributes;
use Zenstruck\Console\IO;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('app:backup', 'Backup and restore system tables')]
final class SystemBackupCommand extends InvokableServiceCommand
{
    use ConfigureWithAttributes, RunsCommands, RunsProcesses;


    public function __construct()
    {
//        assert($this->translatableListener, "not initialized");
        parent::__construct();
    }

    public function __invoke(
        IO                        $io,
        EntityManagerInterface    $entityManager,
        PropertyAccessorInterface $accessor,
        SerializerInterface       $serializer,

        #[Argument(description: 'import or export')]
        string                    $action = 'export',

        #[Argument(description: 'format: csv, json, etc.')]
        string                    $format = 'csv',

    ): void
    {
        $path = Path::normalize('../data/backup/');
        $classes = [User::class];

        switch ($action) {
            case 'export':
                foreach ($classes as $classToExport) {
                    $fileCode = strtolower((new \ReflectionClass($classToExport))->getShortName());
                    $fileName = "{$path}{$fileCode}.{$format}";
                    $io->info(sprintf("Backing up %s as %s", $classToExport, $fileName));
                    $data = $entityManager->getRepository($classToExport)->findAll();
                    $serializedData = $serializer->serialize($data, $format, [
                        AbstractNormalizer::GROUPS => ['export', "$fileCode.read"]
                    ]);

                    $filesystem = new Filesystem();
                    $filesystem->mkdir($path);
                    $filesystem->dumpFile($fileName, $serializedData);
                }
                break;
            default:
                $io->error($action . ' must be import or export');

            case 'import':
                foreach ($classes as $classToExport) {
                    $fileName = strtolower((new \ReflectionClass($classToExport))->getShortName());
                    $filePath = "{$path}{$fileName}.{$format}";

                    if(file_exists($filePath)) {
                        $io->info(sprintf("Start import %s as %s", $classToExport, $format));
                        $fileData = file_get_contents($filePath);

                        $deserializedData = $serializer->deserialize($fileData, $classToExport . "[]", $format, [
                            AbstractNormalizer::GROUPS => ['export'],
                        ]);

                        foreach ($deserializedData as $deserialized) {
                            $entityManager->persist($deserialized);
                        }
                    }
                }

                $entityManager->flush();
                break;
        }
        $io->success($action . ' success.');

    }

}
