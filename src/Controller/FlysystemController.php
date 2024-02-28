<?php

namespace App\Controller;

use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

class FlysystemController extends AbstractController
{
    #[Route('/flysystem_default', name: 'flysystem_browse_default')]
    public function default(FilesystemOperator $defaultStorage, UploaderHelper $uploaderHelper): Response
    {
        $images = $defaultStorage->listContents('/', deep: false);
        return $this->render('flysystem/index.html.twig', [
            'images' => $images,
            'controller_name' => 'FlysystemController',
        ]);
    }

}
