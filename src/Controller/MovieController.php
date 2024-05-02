<?php

namespace App\Controller;

use App\Entity\Movies;
use App\Entity\Categories;
use App\Repository\MoviesRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]
class MovieController extends AbstractController
{
    private $movieRepo;
    private $categRepo;
    private $em;
    public function __construct(MoviesRepository $movieRepo, EntityManagerInterface $em, CategoriesRepository $categRepo)
    {
        $this->movieRepo = $movieRepo;
        $this->em = $em;
        $this->categRepo = $categRepo;
    }

    #[Route('/movies', name: 'get_all_movies', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $movies = $this->movieRepo->findAll();

        $moviesArray = [];
        foreach ($movies as $movie) {
            $categories = $movie->getCategories();
            $categoriesArray = [];
    
            foreach($categories as $categ){
                $categoriesArray[] = [
                    'id' => $categ->getId(),
                    'name' => $categ->getName()
                ];
            }
    
            $moviesArray[] = [
                'id' => $movie->getId(),
                'title' => $movie->getTitle(),
                'synopsis' => $movie->getSynopsis(),
                'release_date' => $movie->getReleaseDate(),
                'director' => $movie->getDirector(),
                'categories' => $categoriesArray,
            ];
        }
    
        return $this->json($moviesArray);
    }

    #[Route('/movies/{id}', name:'get_movie', methods: ['GET'])]
    public function details($id): JsonResponse
    {
        if(!$id){
            return $this->json(['message' => 'Film non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $movie = $this->movieRepo->find($id);
        if(!$movie){
            return $this->json(['message' => 'Film non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $categories = $movie->getCategories();
        $categoriesArray = [];

        foreach($categories as $categ){
            $categoriesArray[] = [
                'id' => $categ->getId(),
                'name' => $categ->getName()
            ];
        }

        $movieArray = [
            'id' => $movie->getId(),
            'title' => $movie->getTitle(),
            'synopsis' => $movie->getSynopsis(),
            'release_date' => $movie->getReleaseDate(),
            'director' => $movie->getDirector(),
            'categories' => $categoriesArray,
        ];

        return $this->json($movieArray);
    }

    #[Route('/movies', name:'add_movie', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $categs = $doctrine->getRepository(Categories::class)->findAll();

        $data = json_decode($request->getContent(), true);
        $movie = new Movies();
        
        $movie->setTitle($data['title']);
        $movie->setSynopsis($data['synopsis']);
        $movie->setReleaseDate($data['release_date']);
        $movie->setDirector($data['director']);
        $categs = $data['categories'];
        foreach($categs as $categId){
            $categ = $doctrine->getRepository(Categories::class)->find($categId);
            if($categ){
                $movie->addCategory($categ);
            }
        }

        $this->em->persist($movie);
        $this->em->flush();

        return $this->json($movie, 200, [], ['groups' => 'movie:read']);
    }

    #[Route('/movies/{id}', name:'update_movie', methods: ['PUT'])]
    public function update($id, Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        $movie = $this->movieRepo->find($id);
    
        if(!$movie){
            return $this->json(['message' => 'Film non trouvé'], Response::HTTP_NOT_FOUND);
        }
    
        $movie->setTitle($data['title'] ?? $movie->getTitle());
        $movie->setSynopsis($data['synopsis'] ?? $movie->getSynopsis());
        $movie->setReleaseDate($data['release_date'] ?? $movie->getReleaseDate());
        $movie->setDirector($data['director'] ?? $movie->getDirector());
        $categoryIds = $data['categories'] ?? [];
        $categories = $doctrine->getRepository(Categories::class)->findBy(['id' => $categoryIds]);
        foreach ($movie->getCategories() as $category) {
            $movie->removeCategory($category);
        }
        foreach ($categories as $category) {
            $movie->addCategory($category);
        }
    
        $this->em->persist($movie);
        $this->em->flush();
    
        return $this->json($movie, 200, [], ['groups' => 'movie:read']);
    }

    #[Route('/movies/{id}', name:'delete_movie', methods: ['DELETE'])]
    public function delete($id): JsonResponse
    {
        $movie = $this->movieRepo->find($id);

        if(!$movie){
            return $this->json(['message' => 'Film non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($movie);
        $this->em->flush();

        return $this->json(['message' => 'Film supprimé'], Response::HTTP_OK);
    }
}
