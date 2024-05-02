<?php

namespace App\Controller;

use App\Entity\Movies;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]

class LikeController extends AbstractController
{
#[Route('/movies/{id}/like', name: 'like_movie', methods: ['POST'])]
    public function like(EntityManagerInterface $em, Movies $movie): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'You must be logged in to like a movie',
            ], 403);
        }
    
        if($user->hasLikedMovie($movie)){
            $user->removeMovieLike($movie);
        } else {
            $user->addMovieLike($movie);
        }
    
        $em->persist($user);
        $em->flush();
    
        return $this->json([
            'message' => 'Like status changed',
        ]);
    }

    #[Route('/movies/{id}/likes', name: 'see_likes_amount', methods: ['GET'])]
    public function seeLikesAmount(Movies $movie): JsonResponse
    {
        $likes = $movie->getLikesCOunt();
    
        return $this->json([
            'likes' => $likes,
        ]);
    }
}
