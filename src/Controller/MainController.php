<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\User;
use App\Enum\UserRole;
use App\Form\AddBlogType;
use App\Form\LogoutType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainController extends AbstractController
{
    #[IsGranted(UserRole::ROLE_USER)]
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

    #[IsGranted(UserRole::ROLE_USER)]
    #[Route('/blogs', name: 'user_blogs')]
    public function getUserBlogs(#[CurrentUser] ?User $user): Response
    {
        $blogs = $user->getBlogs();

        if ($blogs->count() > 0) {
            return $this->render('main/userblogs.html.twig', ['blogs' => $blogs]);
        }

        return $this->render('main/userblogs.html.twig', ['message' => 'There are currently no blogs to read. Go create some', 'blogs' => null]);
    }


    #[IsGranted(UserRole::ROLE_USER)]
    #[Route('/blogs/add', name: 'add_blog')]
    public function addBlog(Request $request, EntityManagerInterface $em): Response
    {
        $blog = new Blog();
        $form = $this->createForm(AddBlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnailFile = $form->get('thumbnail')->getData();

            if ($thumbnailFile) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $newFileName = uniqid() . '.' . $thumbnailFile->guessExtension();

                try {
                    $thumbnailFile->move(
                        $uploadsDirectory,
                        $newFileName
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                $blog->setThumbnailPath($newFileName);
            }

            $blog->setUser($this->getUser());
            $blog->setCreatedAt(new \DateTimeImmutable());

            $em->persist($blog);
            $em->flush();

            return $this->redirectToRoute('user_blogs');
        }


        return $this->render('main/addblog.html.twig', ['add_blog_form' => $form->createView()]);
    }
}
