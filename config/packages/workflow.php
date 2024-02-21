<?php

declare(strict_types=1);

use App\Entity\Submission;
use Symfony\Config\FrameworkConfig;
use Survos\WorkflowHelperBundle\Attribute\Workflow;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Survos\WorkflowBundle\Service\ConfigureFromAttributesService;

return static function (FrameworkConfig $framework) {
//return static function (ContainerConfigurator $containerConfigurator): void {

    foreach ([
                 Submission::class,
             ] as $workflowClass) {
        ConfigureFromAttributesService::configureFramework($workflowClass, $framework, [$workflowClass]);
    }

};
