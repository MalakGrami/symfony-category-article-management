<?php

namespace App\Controller;
use App\Entity\Article;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    private $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }
    #[Route('/home', name: 'home')]
    public function index(Request $request): Response
{
    $entityManager = $this->getDoctrine()->getManager();

    // Get all articles
    $articleRepository = $entityManager->getRepository(Article::class);
    $articlesQuery = $articleRepository->findAllQuery();

    // Handle search
    $searchTerm = $request->query->get('search');
    if ($searchTerm) {
        $articlesQuery = $articleRepository->findByMultipleFieldsQuery($searchTerm);
    }

    // Paginate the results using dependency injection
    $pagination = $this->paginator->paginate(
        $articlesQuery,
        $request->query->getInt('page', 1),
        9 // Items per page
    );

    return $this->render('index.html.twig', [
        'pagination' => $pagination,
        'controller_name' => 'HomeController',
    ]);
}

    #[Route('/showArticle/{id}', name: 'Article', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(Article $article): Response
    {
        return $this->render('showArticle.html.twig', [
            'article' => $article,
        ]);
    }
}
