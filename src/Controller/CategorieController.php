<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class CategorieController extends AbstractController
{
    #[Route('/categories', name: 'categories', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $queryBuilder = $entityManager->getRepository(Categorie::class)->createQueryBuilder('c');

        // search
        $searchTerm = $request->query->get('search');
        if ($searchTerm) {
            $queryBuilder->andWhere('c.nomCategorie LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(), 
            $request->query->getInt('page', 1), // Page number
            5 // Limit per page
        );

        return $this->render('categorie/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/categories/new', name: 'categorie_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
   
    public function new(Request $request): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();

            return $this->redirectToRoute('categories');
        }

        return $this->render('categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories/{id}', name: 'categorie_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Route('/categories/{id}/edit', name: 'categorie_update', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Categorie $categorie): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('categories');
        }

        return $this->render('categorie/update.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories/delete/{id}', name: 'categorie_delete', methods: ['DELETE', 'GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, $id): Response
    
    {
        $categorie = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->find($id);
    
        if (!$categorie) {
            throw $this->createNotFoundException('No categorie found for id ' . $id);
        }
    
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($categorie);
        $entityManager->flush();
    
        return $this->redirectToRoute('categories');
    }
    
}
