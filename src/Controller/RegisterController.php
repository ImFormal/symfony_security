<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Account;
use App\Repository\AccountRepository;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;

final class RegisterController extends AbstractController
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly EntityManagerInterface $em
    ) {}

    #[Route('/register', name: 'app_register_addaccount')]
    public function addAccount(Request $request): Response
    {
        $msg = "";
        $type = "";
        //Créer un objet Account
        $account = new Account();
        //Créer un objet RegisterType (formulaire)
        $form = $this->createForm(RegisterType::class, $account);
        //Récupérer le resultat de la requête
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            
            //Test si le compte n'existe pas
            if(!$this->accountRepository->findOneBy(["email" => $account->getEmail()])) {
                $account->setRoles(["ROLE_USER"]);
                $this->em->persist($account);
                $this->em->flush();
                $msg = "Le compte a été ajouté en BDD";
                $type = "success";
            }
            else {
                $msg = "Les informations email et ou password existe déja";
                $type = "danger";
            }
            $this->addFlash($type,$msg);
        }

        return $this->render('register/addaccount.html.twig', [
            'form' => $form
        ]);
    }
}
