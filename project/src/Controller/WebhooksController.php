<?php

namespace App\Controller;

use App\Entity\Match;
use App\Entity\User;
use App\Util\VonageCallUtil;
use App\Util\VonageVerifyUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class WebhooksController extends AbstractController
{
    /** @var VonageVerifyUtil */
    protected $vonageVerifyUtil;

    /** @var VonageCallUtil */
    protected $vonageCallUtil;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(
        VonageCallUtil $vonageCallUtil,
        VonageVerifyUtil $vonageVerifyUtil,
        EntityManagerInterface $entityManager
    ) {
        $this->vonageCallUtil = $vonageCallUtil;
        $this->vonageVerifyUtil = $vonageVerifyUtil;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/webhooks", name="webhooks")
     */
    public function index()
    {
        return $this->render('webhooks/index.html.twig', [
            'controller_name' => 'WebhooksController',
        ]);
    }

    /**
     * @Route("/webhooks/joinConference", name="match_join")
     */
    public function joinConference(Request $request)
    {
        $content = json_decode($request->getContent(), true);
    
        if (!in_array($content['dtmf'], ['1', '2'])) {
            $ncco = [
                [
                    'action' => 'talk',
                    'text' => 'Please only enter 1 or 2. Would you like to join a call with someone?',
                    'voiceName' => 'Amy',
                ],
                [
                    'action' => 'input',
                    'maxDigits' => 1,
                    'eventUrl' => [
                        $_ENV['NGROK_URL'] . '/webhooks/joinConference'
                    ],
                    'timeOut' => 10
                ]
            ];
        } elseif ($content['dtmf'] === '1') {
            // Find user by number.
            $user = $this->findUserByNumber($content['to']);
            // Find match by user and today.
            $match = $this->findMatchByUser($user)[0];
    
            // Make next request.
            $ncco = [
                [
                    'action' => 'talk',
                    'text' => 'Thank you. I will now connect you.',
                    'voiceName' => 'Amy',
                ],
                [
                    'action' => 'conversation',
                    'name' => $match->getConferenceName()
                ]
            ];
        } else {
            $ncco = [
                [
                    'action' => 'talk',
                    'text' => 'Ok, we will not put you in a call with someone at this time. Goodbye.',
                    'voiceName' => 'Amy',
                ]
            ];
        }
    
        return new JsonResponse($ncco);
    }

    private function findUserByNumber(string $phoneNumber)
    {
        return $this->entityManager->getRepository(User::class)->findOneByPhoneNumber(
            $this->vonageVerifyUtil->getNationalizedNumber('+' . $phoneNumber)
        );
    }
     
    private function findMatchByUser(User $user)
    {
        return $this->entityManager
            ->getRepository(Match::class)
            ->findByDateUser($user, (new \DateTime()));
    }
}
