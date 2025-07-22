<?php
// src/Controller/CentreController.php
namespace App\Controller;

use App\Entity\Centre;
use App\Entity\User;
use App\Enum\UserRole;
use App\Form\CentreType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour la gestion des centres (CRUD).
 */
#[Route('/admin/centre')]
class CentreController extends AbstractController {
    /**
     * Affiche la liste de tous les centres.
     *
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @return Response La vue listant les centres.
     */
    #[Route('/', name: 'centre_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response {
        $centres = $em->getRepository(Centre::class)->findAll();

        return $this->render('centre/index.html.twig', [
            'centres' => $centres,
        ]);
    }

    /**
     * Crée un nouveau centre et son administrateur.
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $em Le gestionnaire d'entités Doctrine.
     * @param UserPasswordHasherInterface $passwordHasher Le service de hash de mot de passe.
     * @param MailerInterface $mailer Le service d'envoi d'emails.
     * @param LoggerInterface $logger Le logger d'erreurs.
     * @param string $logos_directory Le répertoire de stockage des logos.
     * @return Response La vue du formulaire ou la redirection après succès.
     */
    #[Route('/new', name: 'centre_new', methods: ['GET', 'POST'])]
    public function new (
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
        LoggerInterface $logger,
        #[Autowire('%logos_directory%')] string $logos_directory
    ): Response {
        $centre = new Centre();
        $form   = $this->createForm(CentreType::class, $centre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1) Upload du logo (avant transaction)
            /** @var UploadedFile|null $file */
            $file = $form->get('logoFile')->getData();
            if ($file instanceof UploadedFile) {
                $filename = uniqid('logo_') . '.' . $file->guessExtension();
                $file->move($logos_directory, $filename);
                $centre->setLogoUrl('uploads/logos/' . $filename);
            }

            // 2) Génération du mot de passe en clair
            $plainPassword = rtrim(strtr(base64_encode(random_bytes(6)), '+/', '-_'), '=');

            // 3) Démarrage de la transaction DBAL
            $conn = $em->getConnection();
            $conn->beginTransaction();
            try {
                // Persiste le Centre
                $em->persist($centre);

                // Création de l'utilisateur ADMIN_CENTRE
                $user = new User();
                $user->setEmail($centre->getEmail());
                $user->setRole(UserRole::ADMIN_CENTRE);
                $user->setNom('Admin');
                $user->setPrenom('Admin');
                $user->setCentre($centre);

                // Hash du mot de passe
                $hashed = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashed);

                $em->persist($user);

                // Flush + commit
                $em->flush();
                $conn->commit();

                // 4) Envoi de l’email en dehors de la transaction
                $message = (new Email())
                    ->from(new Address('no-reply@votre-domaine.tld', 'Support'))
                    ->to($centre->getEmail())
                    ->subject('Configuration de votre accès administrateur')
                    ->html($this->renderView('emails/centre_admin_setup.html.twig', [
                        'email'         => $centre->getEmail(),
                        'plainPassword' => $plainPassword,
                    ]));

                $mailer->send($message);

                $this->addFlash('success',
                    'Centre et compte admin créés. Consultez votre email pour finaliser l’accès.'
                );

                return $this->redirectToRoute('app_login');
            } catch (\Throwable $e) {
                $conn->rollBack();
                $logger->error('Erreur création centre/admin : ' . $e->getMessage());
                $this->addFlash('error', 'Une erreur est survenue lors de la création du centre.');
                // On retombe sur le formulaire
            }
        }

        return $this->render('centre/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
