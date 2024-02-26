<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;

#[AsLiveComponent]
class InlineEditProfile extends AbstractController
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    /** This allows us to have a data-model="user.creditName" */
    #[LiveProp(writable: ['creditName'])]
    /** When we validate, we want to also validate the User object */
    #[Assert\Valid]
    public User $user;

    /** Tracks whether the component is in "edit" mode or not */
    #[LiveProp]
    public bool $isEditing = false;

    /**
     * A temporary message to show to the user.
     *
     * This is purposely not a LiveProp: this is a "temporary" value that
     * will only show one time.
     */
    public ?string $flashMessage = null;

    #[LiveAction]
    public function activateEditing()
    {
        $this->isEditing = true;
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager)
    {
        // if validation fails, this throws an exception & the component re-renders
        $this->validate();

        $this->isEditing = false;
        $this->flashMessage = 'Saved!).';

        $entityManager->flush();
    }
}
