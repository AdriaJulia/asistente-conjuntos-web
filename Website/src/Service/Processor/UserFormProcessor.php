<?php

namespace App\Service\Processor;

use App\Entity\User;
use App\Form\Model\UserDto;
use App\Form\Type\UserFormType;
use App\Service\Manager\UserManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;



class UserFormProcessor
{
    private $userManager;
    private $formFactory;

    public function __construct(
        UserManager $userManager,
        FormFactoryInterface $formFactory
    ) {
        $this->userManager = $userManager;
        $this->formFactory = $formFactory;
    }

    public function __invoke(User $user, 
                             Request $request): array
    {

        $userDto = UserDto::createFromUser($user);
        $form = $this->formFactory->create(UserFormType::class, $userDto);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return [null, 'Form is not submitted'];
        }
        if ($form->isValid()) {
            $user = $this->userManager->register($userDto->username,
                                                 $userDto->password,
                                                 $userDto->ldapToken);
            return [$user, null];
        }
        return [null, $form];
    }
}