<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class CategoryController extends AbstractController
{

    private $categRepo;
    private $em;

    public function __construct(CategoriesRepository $categRepo, EntityManagerInterface $em)
    {
        $this->categRepo = $categRepo;
        $this->em = $em;
    }

    #[Route('/categories', name: 'get_all_categories', methods: ['GET'])]
    public function getCategs(): JsonResponse
    {
        $categs = $this->categRepo->findAll();
        $categsArray = [];
        foreach($categs as $categ){
            $categsArray[] = [
                'id' => $categ->getId(),
                'name' => $categ->getName()
            ];
        }
    
        return $this->json($categsArray);
    }

    #[Route('/categories/{id}', name:'get_category', methods: ['GET'])]
    public function getCateg($id): JsonResponse
    {
        if(!$id){
            return $this->json(['message' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $categ = $this->categRepo->find($id);

        if(!$categ){
            return $this->json(['message' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $movies = $categ->getMovies();
        $moviesArray = [];

        foreach($movies as $movie){
            $moviesArray[] = [
                'id' => $movie->getId(),
                'title' => $movie->getTitle(),
                'synopsis' => $movie->getSynopsis(),
                'release_date' => $movie->getReleaseDate(),
                'director' => $movie->getDirector(),
                'categories' => $movie->getCategories()->map(function($categories){
                    return [
                        'id' => $categories->getId(),
                        'name' => $categories->getName()
                    ];
                })->toArray()
            ];
        }

        return $this->json($moviesArray);

    }

    #[Route('/categories', name:'add_category', methods: ['POST'])]
    public function addCateg(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $categ = new Categories();
        $categ->setName($data['name']);

        $this->em->persist($categ);
        $this->em->flush();

        return $this->json($categ);
    }

    #[Route('/categories/{id}', name:'update_category', methods: ['PUT'])]
    public function updateCateg($id, Request $request): JsonResponse
    {
        $categ = $this->categRepo->find($id);
        $data = json_decode($request->getContent(), true);

        $categ->setName($data['name']);

        $this->em->persist($categ);
        $this->em->flush();

        return $this->json($categ);
    }

    #[Route('/categories/{id}', name:'delete_category', methods: ['DELETE'])]
    public function deleteCateg($id): JsonResponse
    {
        $categ = $this->categRepo->find($id);

        if(!$categ){
            return $this->json(['message' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($categ);
        $this->em->flush();

        return $this->json(['message' => 'Catégorie supprimée']);
    }
}
