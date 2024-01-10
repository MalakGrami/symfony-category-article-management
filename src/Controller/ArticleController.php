<?php

namespace App\Controller;
use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;




class ArticleController extends AbstractController
{
    #[Route('/article', name: 'articles')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $queryBuilder = $entityManager->getRepository(Article::class)->createQueryBuilder('a');

        // Handle search
        $searchTerm = $request->query->get('search');
        if ($searchTerm) {
            $queryBuilder->andWhere('a.Libelle LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        // Add any additional query logic, e.g., filtering

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(), // Doctrine Query
            $request->query->getInt('page', 1), // Page number
            5 // Limit per page
        );

        return $this->render('article/index.html.twig', [
            'articles' => $pagination,
        ]);
    }
    private function generateUniqueFileName(): string
    {
        return md5(uniqid());
    }
    
    #[Route('/article/{id}', name: 'articles_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/ajouter', name: 'article_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
    
                try {
                    $file->move(
                        $this->getParameter('article_images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // Handle the error
                }
    
                $article->setImage($fileName);
            }
    
            $entityManager->persist($article);
            $entityManager->flush();
    
            return $this->redirectToRoute('articles', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/article/{id}/edit', name: 'articles_update', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('article_images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // Handle the error
                }

                $article->setImage($fileName);
            }

            $entityManager->flush();

            return $this->redirectToRoute('articles', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/update.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/article/delete/{id}', name: 'articles_delete', methods: ['DELETE', 'GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, $id): Response
    {
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);

        if (!$article) {
            throw $this->createNotFoundException('No article found for id ' . $id);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('articles');
    }
}
