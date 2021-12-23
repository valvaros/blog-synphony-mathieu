<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Category;
use App\Form\EditArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Pour limiter l'accés à tous le controller et les routes qu'il contient.
 * @IsGranted("ROLE_ADMIN")
 */
class DashboardController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/dashboard", name="dashboard")
     * @return Response
     */
    public function dashboard(): Response
    {
        $articles = $this->entityManager->getRepository(Article::class)->findAll();

        $categories = $this->entityManager->getRepository(Category::class)->findall();

        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->render('dashboard/dashboard.html.twig',
            [
                'articles' => $articles,
                'users' => $users,
                'categories'=>$categories
            ]
        );
    }

    /**
     * Pour limiterl'accès à une route ou action en particulier à un rôle.
     * @iSgranted("ROLE_SUPER_ADMIN")
     * @Route("/admin/supprimer/user/{id}", name="delete_user")
     * @param User $user
     * @return Response
     */
    public function deleteUser(User $user): Response
    {
          $this->entityManager->remove($user);
          $this->entityManager->flush();

          $this->addFlash('success','Utilisateur supprimé !');

          return $this->redirectToRoute('dashboard');
    }

}
