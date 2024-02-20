<?php

namespace App\Controller;


use App\Entity\Plan;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Topic;
use App\Form\UserType;
use App\Entity\Subscription;
use App\Form\ImagesUsersType;
use App\Form\UserPasswordType;
use App\Repository\AlerteRepository; 
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AccountController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/account', name: 'app_account')]
    public function index(ManagerRegistry $doctrine, AlerteRepository $alerteRepository): Response 
    {

        $plans = $this->entityManager->getRepository(Plan::class)->findAll();
        $activeSub = $this->entityManager->getRepository(Subscription::class)->findActiveSub($this->getUser()->getId());        
        $latestAlert = $alerteRepository->findLatestAlert();
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        $deleteForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete'))
            ->setMethod('POST')
            ->add('submit', SubmitType::class, ['label' => 'Supprimer mon compte'])
            ->getForm();
    
        return $this->render('account/index.html.twig', [
            'user' => $user,
            'plans' => $plans,
            'activeSub' => $activeSub,
            'latestAlert' => $latestAlert,
            'deleteForm' => $deleteForm->createView(),
        ]);
    }
    

    #[Route('/getPseudo/{id}', name: 'get_user_pseudo', methods: ['GET'])]
    public function getPseudo(User $user = null): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }
    
        return $this->json(['pseudo' => $user->getPseudo()]);
    }

    #[Route('/mes-posts', name: 'mes_posts')]
    public function mesPosts(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser(); // Obtenez l'utilisateur actuellement connecté
        // Trouvez tous les posts de cet utilisateur
        $posts = $doctrine->getRepository(Post::class)->findBy(['user' => $user]);
        return $this->render('account/mes_posts.html.twig', ['posts' => $posts]);
    }
    
    #[Route('/mes-topics', name: 'mes_topics')]
    public function mesTopics(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser(); // Obtenez l'utilisateur actuellement connecté
        // Trouvez tous les topics de cet utilisateur
        $topics = $doctrine->getRepository(Topic::class)->findBy(['user' => $user]);
        return $this->render('account/mes_topics.html.twig', ['topics' => $topics]);
    }

    #[Route('/user/delete', name: 'user_delete', methods: ['POST'])]
    public function deleteAccount(Request $request, Security $security): Response
    {
        // Récupérer l'utilisateur actuellement connecté.
        $user = $this->getUser();
            
        // Si aucun utilisateur n'est connecté, lever une exception d'accès refusé avec un message d'erreur.
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour supprimer votre compte.');
        }
            
        // Récupérer l'EntityManager pour effectuer des opérations sur la base de données.
        $entityManager = $this->entityManager;
            
        // Parcourir tous les posts de l'utilisateur et les anonymiser en mettant leur utilisateur à null.
        foreach ($user->getPost() as $post) {
            $post->setUser(null);
            $entityManager->persist($post); // Indiquer à Doctrine que cet objet doit être enregistré.
        }
            
        // Parcourir tous les topics de l'utilisateur et les anonymiser en mettant leur utilisateur à null.
        foreach ($user->getTopic() as $topic) {
            $topic->setUser(null);
            $entityManager->persist($topic); // Indiquer à Doctrine que cet objet doit être enregistré.
        }
            
        // Parcourir tous les markers de l'utilisateur et les supprimer.
        foreach ($user->getMarkers() as $marker) {
            $entityManager->remove($marker); // Indiquer à Doctrine que cet objet doit être supprimé.
        }
            
        // Supprimer l'objet utilisateur de la base de données.
        $entityManager->remove($user);
        $entityManager->flush(); // Exécuter toutes les requêtes en attente (persist et remove).
            
        // La ligne suivante est commentée, car la méthode logout() n'existe pas dans la classe Security.
        // $security->logout();
        
        // Invalider la session actuelle pour déconnecter l'utilisateur.
        $request->getSession()->invalidate();
        
        // Supprimer le token de sécurité actuel pour déconnecter complètement l'utilisateur.
        $this->container->get('security.token_storage')->setToken(null);
        
        // Rediriger l'utilisateur vers la route 'app_home' après la suppression de son compte.
        return $this->redirectToRoute('app_home');
    }
    
    

    public function profile(): Response
    {
        // Création d'un formulaire via le FormBuilder de Symfony.
        $deleteForm = $this->createFormBuilder()
            // Définition de l'action du formulaire, c'est-à-dire l'URL vers laquelle les données du formulaire seront envoyées.
            ->setAction($this->generateUrl('user_delete'))
            // Définition de la méthode HTTP utilisée pour soumettre le formulaire.
            ->setMethod('POST')
            // Ajout d'un bouton de soumission au formulaire avec le label 'Supprimer mon compte'.
            ->add('submit', SubmitType::class, ['label' => 'Supprimer mon compte'])
            // Ajout d'un champ caché '_token' au formulaire pour stocker le token CSRF.
            ->add('_token', HiddenType::class, [
                // Récupération de la valeur du token CSRF pour 'delete_account' et affectation à la propriété 'data' du champ caché '_token'.
                'data' => $this->get('security.csrf.token_manager')->getToken('delete_account')->getValue(),
            ])
            // Construction du formulaire et renvoi d'une instance de Form.
            ->getForm();
        
        // Renvoi d'une réponse HTML rendue par le template 'user/account.html.twig', 
        // et passage du formulaire créé à la vue afin qu'il puisse être rendu dans le HTML.
        return $this->render('user/account.html.twig', [
            'deleteForm' => $deleteForm->createView(),
        ]);
    }

    #[Route('/account/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {

        if(!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if($this->getUser() !== $user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            if($hasher->isPasswordValid($user, $form->get('plainPassword')->getData()   )) {


            $user = $form->getData();
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Les informations le votre compte ont bien été modifiés'
            );

            return $this->redirectToRoute('app_account');
        }else {
            $this->addFlash(
                'warning',
                'Le mot de passe renseigné est incorrecte'
            );
        }

    }
    
        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/account/edition-mot-de-passe/{id}', name: 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword(User $user, Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($user, $form->get('plainPassword')->getData())) {
                $user->setPassword(
                    $hasher->hashPassword($user, $form->get('newPassword')->getData())
                );
    
                $entityManager->persist($user);
                $entityManager->flush();
    
                $this->addFlash('success', 'Le mot de passe de votre compte a bien été modifié. Pas besoin de vous reconnecter. Mais pensez à stocker votre mot de passe en lieu sûr');
    
    
                // Redirect to login page
                return $this->redirectToRoute('app_home');
            } else {
                $this->addFlash('warning', 'Le mot de passe renseigné est incorrect');
            }
        }
    
        return $this->render('account/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit-profile-picture', name: 'edit_profile_picture', methods: ['GET', 'POST'])]
    public function editProfilePicture(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ImagesUsersType::class, $user->getImagesUsers());
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $user->getImagesUsers()->setImageFile(null);
    
            $this->addFlash('success', 'Photo de profil mise à jour avec succès !');
    
            return $this->redirectToRoute('app_account');
        }
    
        return $this->render('account/edit_profile_picture.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    
}
