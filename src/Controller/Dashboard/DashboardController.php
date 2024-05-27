<?php

namespace App\Controller\Dashboard;

use App\Entity\Contacts;
use App\Entity\Messages;
use App\Repository\ContactsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Security\Core\Security;


#[Route('/dashboard')]
class DashboardController extends AbstractController {
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/index', 'dashboard_index', methods: ['GET'])]
    public function index(): Response {
        $user = $this->security->getUser();
        
        $pack = $user->getPack();
        
            
        // Compter le nombre de messages envoyés par l'utilisateur
        $messagesCount = $user->getMessages()->count();
        $contactsCount = $user->getContacts()->count();
        
        
        return $this->render("dashboard/index.html.twig",[
            'pack' => $pack,
            'msgsent' => $messagesCount,
            'nbrContact'=>$contactsCount
        ]);
    }
   

    #[Route('/send-msg', 'dashboard_send_msg')]
    public function get_form_send_sms(Request $request, EntityManagerInterface $em,ContactsRepository $contactRepo): Response
    {
     $user = $this->security->getUser();

        if ($request->isMethod('POST')) {
         
            $destinataires = $request->request->all('destinataires');
           
            
           
            $expediteur = $request->request->get('expediteur');
            $message = $request->request->get('message');
            
          
            if ($user->getPack() <= 0) {
                $this->addFlash('error', 'Vous n\'avez plus de crédits pour envoyer des messages.');
                return $this->redirectToRoute('dashboard_send_msg');
            }
            
           

            $messageEntity = new Messages();
            $messageEntity->setExpediteur($expediteur);
            $messageEntity->setRecepient($destinataires);
            $messageEntity->setContent($message);
            $messageEntity->setUser($user);

          
            
            $client = new Client();

            $url = 'https://3gywnv.api.infobip.com/sms/2/text/advanced';
            $apiKey = 'App ba03571bd164461dd60d8a912f11bc70-bec2f5ed-9961-4e0d-8fde-956349fad5b5';
            foreach ($destinataires as $destinataire) {
                
                if ($user->getPack() <= 0) {
                    $this->addFlash('error', 'Vous n\'avez plus de crédits pour envoyer des messages.');
                    return $this->redirectToRoute('dashboard_send_msg');
                }
          
            $body = [
                'messages' => [
                    [
                        'destinations' => [
                            ['to' => $destinataire]
                        ],
                        'from' => $expediteur,
                        'text' => $message
                    ]
                ]
            ];

            try {
                $response = $client->request('POST', $url, [
                    'headers' => [
                        'Authorization' => $apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'json' => $body
                ]);

                if ($response->getStatusCode() == 200) {
                    $responseBody = $response->getBody()->getContents();
                    
                    $this->addFlash('success', 'Message envoyé avec succès!');
                    $user->setPack($user->getPack() - 1);
                    $em->persist($messageEntity);
                    $em->flush();
                   
                }
            } catch (RequestException $e) {
                $errorMessage = $e->getMessage();
                
                $this->addFlash('error', 'Erreur lors de l\'envoi du message: ' . $errorMessage);
            }
        }
        }
        $contacts = $contactRepo->findBy(['user' => $user]);
        return $this->render("dashboard/send.html.twig",['contacts'=>$contacts]);
     }

    #[Route('/history-msg', 'dashboard_history', methods: ['GET'])]
    public function get_form_history(): Response {
        return $this->render("dashboard/history.html.twig");
    }

    #[Route('/purchase', 'dashboard_purchase', methods: ['GET'])]
    public function get_form_purchase(): Response {
        return $this->render("dashboard/purchase.html.twig");
    }

    #[Route('/list-contact', 'list_contact')]
    public function contact_list(ContactsRepository $contactRepo): Response {
        $user = $this->security->getUser();
        $contact = $contactRepo->findBy(['user' => $user]);
       
        return $this->render("dashboard/list_contact.html.twig",['contacts'=>$contact]);
    }

    #[Route('/add-contact', 'add_contact')]
    public function get_form_add_contact(Request $request, EntityManagerInterface $em): Response
    {
         
        if ($request->isMethod('POST')) {
             
            $nom = $request->request->get('nom');
            $numero = $request->request->get('numero');
            if(!empty($nom) && !empty($numero)){
                $user = $this->security->getUser();


                $contactManager = new Contacts();
                $contactManager->setNom($nom);
                $contactManager->setNumero($numero);
                $contactManager->setUser($user);

                $em->persist($contactManager);
                $em->flush();

                $this->addFlash('success', 'Ajout du contact effectué avec succès');
                return $this->redirectToRoute('list_contact');
            }else{
                $this->addFlash('error', 'Remplissez correctement les champs');
            }
            
            
        }
          
        // Affichez le formulaire (GET request)
        return $this->render("dashboard/add_contact.html.twig");
    }



}