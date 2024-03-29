<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LogoutType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/main', name: 'app_main')]
    public function index(#[CurrentUser] ?User $user): Response
    {
        $form = $this->createForm(LogoutType::class);

        // no need to write redirect logic, symfony handles that automatically and redirects you to the login page

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'user_identifier' => $user->getUserIdentifier(),
            'logout_form' => $form->createView(),
        ]);
    }
}
