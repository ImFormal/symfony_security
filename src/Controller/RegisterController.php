<?php

namespace App\Controller;

use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Account;
use App\Repository\AccountRepository;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\EmailService;

final class RegisterController extends AbstractController
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hash,
        private readonly EmailService $emailService
    ) {}

    #[Route('/test', name:'app_test_testemail')]
    public function testEmail()  : Response{
        return new Response($this->emailService->test());
    }

    #[Route('active-account/{id}', name: 'app_active_account')]
    public function activeAccount(mixed $id, AccountRepository $accountRepository): Response{
        
        try{
            $account = $accountRepository->find($id);

            if($account && $account->isStatus() === false){
                $account->setStatus(true);
                $this->em->persist($account);
                $this->em->flush();
            }
        } catch(\Exception $e){
            $this->addFlash("warning", $e->getMessage());
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route('/register', name: 'app_register_addaccount')]
    public function addAccount(Request $request, Recaptcha3Validator $recaptcha3Validator): Response
    {
        $msg = "";
        $type = "";
        //Créer un objet Account
        $account = new Account();
        //Créer un objet RegisterType (formulaire)
        $form = $this->createForm(RegisterType::class, $account);
        //Récupérer le resultat de la requête
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            
            //test l'utilisateur est un bot
            if ($recaptcha3Validator->getLastResponse()->getScore() < 0.5) {
                $msg = "L'utilisateur est un bot";
                $type = "danger";
            } 
            else{
                $account->setPassword( $this->hash->hashPassword($account, $request->request->all("register")["password"]["first"]));
            
                //Test si le compte n'existe pas
                if(!$this->accountRepository->findOneBy(["email" => $account->getEmail()])) {
                    $account->setStatus(false);
                    $account->setRoles(["ROLE_USER"]);
                    $this->em->persist($account);
                    $this->em->flush();

                    // Envoi de l'email de confirmation
                    $subject = "Confirmation de votre inscription";
                    $body = "<h1>Bienvenue " . $account->getEmail() . "!</h1>
                            <p>Votre compte a été enregistré avec succès.</p>";

                    $msg = $this->emailService->sendEmail($account->getEmail(), $subject, $body);
                    $type = "success";
                } else {
                    $msg = "Les informations email et/ou mot de passe existent déjà.";
                    $type = "danger";
                }
            }
            $this->addFlash($type,$msg);
        }
        return $this->render('register/addaccount.html.twig', [
            'form' => $form
        ]);
    }
}
