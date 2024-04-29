<?php

namespace App\Controller;

use App\Entity\Movies;
use App\Repository\MoviesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('/api')]
class MovieController extends AbstractController
{
    private $movieRepo;
    private $em;
    public function __construct(MoviesRepository $movieRepo, EntityManagerInterface $em)
    {
        $this->movieRepo = $movieRepo;
        $this->em = $em;
    }

    #[Route('/movies', name: 'get_all_movies', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $movies = $this->movieRepo->findAll();
        return $this->json($movies);
    }

    #[Route('/movies/{id}', name:'get_movie', methods: ['GET'])]
    public function details($id): JsonResponse
    {
        if(!$id){
            return $this->json(['message' => 'Film non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $movie = $this->movieRepo->find($id);
        return $this->json($movie);
    }

    #[Route('/movies', name:'add_movie', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $movie = new Movies();
        
        $movie->setTitle($data['title']);
        $movie->setSynopsis($data['synopsis']);
        $movie->setReleaseDate($data['release_date']);
        $movie->setDirector($data['director']);
        $movie->setGenre($data['genre']);

        $this->em->persist($movie);
        $this->em->flush();

        return $this->json($movie);
    }

    #[Route('/movies/{id}', name:'update_movie', methods: ['PUT'])]
    public function update($id, Request $request): JsonResponse
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
        $movie->setGenre($data['genre'] ?? $movie->getGenre());

        $this->em->persist($movie);
        $this->em->flush();

        return $this->json($movie);
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
