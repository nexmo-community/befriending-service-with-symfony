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

    /**
     * @Route("/webhooks/userFeedback", name="match_feedback")
     */
    public function getUserFeedback(Request $request)
    {
        $content = json_decode($request->getContent(), true);
    
        if (!in_array($content['dtmf'], ['1', '2'])) {
            $ncco = [
                [
                    'action' => 'talk',
                    'text' => 'Please only enter 1 or 2. Would you like to provide feedback for the service?',
                    'voiceName' => 'Amy',
                ],
                [
                    'action' => 'input',
                    'maxDigits' => 1,
                    'eventUrl' => [
                        $request->getScheme().'://'.$request->getHost().'/webhooks/userFeedback'
                    ],
                    'timeOut' => 10
                ]
            ];
        } else {
            // Find user by number.
            $user = $this->findUserByNumber($content['to']);
            // Find match by user and today.
            $match = $this->findMatchByUser($user)[0];
            // Determine if user is first or second caller.
            $isFirstOrSecond = $this->isFirstOrSecondCaller($match, $user);
    
            // Save entry to database.
            if ($isFirstOrSecond === 1) {
                $method = 'setCallerOneFeedbackAccepted';
            } elseif ($isFirstOrSecond === 2) {
                $method = 'setCallerTwoFeedbackAccepted';
            } else {
                return new JsonResponse([]);
            }
    
            $mapResponse = [
                '1' => true,
                '2' => false
            ];
    
            $match->$method($mapResponse[$content['dtmf']]);
            $this->entityManager->flush();
    
            // Make next request.
            $ncco = [
                [
                    'action' => 'talk',
                    'text' => 'Thank you. Was the call successful? Please enter 1 for yes, or 2 for no.',
                    'voiceName' => 'Amy',
                ],
                [
                    'action' => 'input',
                    'maxDigits' => 1,
                    'eventUrl' => [
                        $_ENV['NGROK_URL'] . '/webhooks/userFeedbackCallSuccess'
                    ],
                    'timeOut' => 10
                ]
            ];
        }
    
        return new JsonResponse($ncco);
    }

    /**
     * @Route("/webhooks/userFeedbackCallSuccess", name="match_call_success")
     */
    public function getUserFeedbackCallSuccess(Request $request)
    {
        $content = json_decode($request->getContent(), true);
    
        if (!in_array($content['dtmf'], ["1", "2"])) {
            $ncco = [
                [
                    'action' => 'talk',
                    'text' => 'Please only enter 1 or 2. Was the call successful? Please enter 1 for yes or 2 for no.',
                    'voiceName' => 'Amy',
                ],
                [
                    'action' => 'input',
                    'maxDigits' => 1,
                    'eventUrl' => [
                        $request->getScheme().'://'.$request->getHost().'/webhooks/userFeedbackCallSuccess'
                    ],
                    'timeOut' => 10
                ]
            ];
        } else {
            // Find user by number.
            $user = $this->findUserByNumber($content['to']);
            // Find match by user and today.
            $match = $this->findMatchByUser($user)[0];
            // Determine if user is first or second caller.
            $isFirstOrSecond = $this->isFirstOrSecondCaller($match, $user);
    
            // Save entry to database.
            if ($isFirstOrSecond === 1) {
                $method = 'setCallerOneCallSuccessful';
            } elseif ($isFirstOrSecond === 2) {
                $method = 'setCallerTwoCallSuccessful';
            } else {
                return new JsonResponse([]);
            }
    
            $mapResponse = [
                '1' => true,
                '2' => false
            ];
    
            $match->$method($mapResponse[$content['dtmf']]);
            $this->entityManager->flush();
    
            // Make next request.
            $ncco = [
                [
                    'action' => 'talk',
                    'text' => 'Would you like to have another call tomorrow? Please enter 1 for yes or 2 for no.',
                    'voiceName' => 'Amy',
                ],
                [
                    'action' => 'input',
                    'maxDigits' => 1,
                    'eventUrl' => [
                        $_ENV['NGROK_URL'] . '/webhooks/userFeedbackContinue'
                    ],
                    'timeOut' => 10
                ]
            ];
        }
    
        return new JsonResponse($ncco);
    }

    /**
     * @Route("/webhooks/userFeedbackContinue", name="match_user_continue")
     */
    public function getUserFeedbackContinue(Request $request)
    {
        $content = json_decode($request->getContent(), true);
    
        if (!in_array($content['dtmf'], ["1", "2"])) {
            $ncco = [
                [
                    'action' => 'talk',
                    'text' => 'Please only enter 1 or 2. Would you like to have another call tomorrow? Please enter 1 for yes or 2 for no.',
                    'voiceName' => 'Amy',
                ],
                [
                    'action' => 'input',
                    'maxDigits' => 1,
                    'eventUrl' => [
                        $_ENV['NGROK_URL'] . '/webhooks/userFeedbackContinue'
                    ],
                    'timeOut' => 10
                ]
            ];
        } else {
            // Find user by number.
            $user = $this->findUserByNumber($content['to']);
            // Find match by user and today.
            $match = $this->findMatchByUser($user);
    
            if (!$match) {
                return new JsonResponse([]);
            }
    
            $mapResponse = [
                '1' => true,
                '2' => false
            ];
    
            if ($content['dtmf'] === "2") {
                $user->setActive(false);
                $this->entityManager->flush();
    
                $ncco = [
                    [
                        'action' => 'talk',
                        'text' => 'Thank you for your feedback. Your number has been removed from the list.',
                        'voiceName' => 'Amy',
                    ]
                ];
            } else {
                $ncco = [
                    [
                        'action' => 'talk',
                        'text' => 'Thank you for your feedback. Goodbye.',
                        'voiceName' => 'Amy',
                    ],
                ];
            }
        }
    
        return new JsonResponse($ncco);
    }

    private function isFirstOrSecondCaller(Match $match, User $user): ?int
    {
        if ($match->getCallerOne() === $user) {
            return 1;
        }
    
        if ($match->getCallerTwo() === $user) {
            return 2;
        }
    
        return null;
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
