<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api')]

class SecurityController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $em;
    private UserRepository $userRepo;
    private JWTTokenManagerInterface $JWTManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, UserRepository $userRepo, JWTTokenManagerInterface $JWTManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->em = $em;
        $this->userRepo = $userRepo;
        $this->JWTManager = $JWTManager;
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $data['password']
            )
        );
        $user->setRoles(['ROLE_USER']);

        $this->em->persist($user);
        $this->em->flush();


        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'roles' => $user->getRoles(),
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/login', name:'app_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->userRepo->findOneBy(['email' => $data['email']]);
        if(!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])){
            return new JsonResponse(['message' => 'Email ou mot de passe incorrect'], JsonResponse::HTTP_UNAUTHORIZED);
        }


        $token = $this->JWTManager->create($user);
        $res = new JsonResponse([
            'message' => 'Connexion réussie',
        ]);

        $res->headers->setCookie(new Cookie('BEARER', $token, time() + 3600, '/', null, true, true));
        return $res;
    }

    #[Route('/logout', name:'app_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $res = new JsonResponse([
            'message' => 'Déconnexion réussie',
        ]);

        $res->headers->clearCookie('BEARER');
        return $res;
    }
}
