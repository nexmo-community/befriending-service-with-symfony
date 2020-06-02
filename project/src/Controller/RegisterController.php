<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\VerifyType;
use App\Util\MapQuestUtil;
use App\Util\VonageVerifyUtil;
use Nexmo\Verify\Verification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    /** @var VonageVerifyUtil */
    protected $vonageVerifyUtil;
     
    /** @var MapQuestUtil */
    protected $mapQuestUtil;

    public function __construct(
        VonageVerifyUtil $vonageVerifyUtil,
        MapQuestUtil $mapQuestUtil
    ) {
        $this->vonageVerifyUtil = $vonageVerifyUtil;
        $this->mapQuestUtil = $mapQuestUtil;
    }

    /**
     * @Route("/register", name="register")
     */
    public function index(Request $request)
    {
        $user = new User();

        $form = $this->createForm(
            UserType::class,
            $user
        );
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $latLng = $this->mapQuestUtil->getLatLongByAddress($user);

            if (null !== $latLng) {
                $user
                  ->setLatitude($latLng['lat'])
                  ->setLongitude($latLng['lng']);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $verification = $this->vonageVerifyUtil->sendVerification($user);
            $requestId = $this->vonageVerifyUtil->getRequestId($verification);

            if ($requestId) {
                $user->setVerificationRequestId($requestId);
                $entityManager->flush();

                return $this->redirectToRoute('app_register_verify', ['user' => $user->getId()]);
            }
        }

        return $this->render('register/index.html.twig', [
            'controller_name' => 'RegisterController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register/verify/{user}", name="app_register_verify")
     * @ParamConverter("user", class="App:User")
     */
    public function verify(Request $request, User $user): Response
    {
        $form = $this->createForm(VerifyType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $verify = $this->vonageVerifyUtil->verify(
                $user->getVerificationRequestId(),
                $form->get('verificationCode')->getData()
            );
    
            if ($verify instanceof Verification) {
                $user->setVerificationRequestId(null);
                $user->setVerified(true);
                $user->setActive(true);
    
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();

                return $this->redirectToRoute('app_register_success');
            }
        }
    
        return $this->render('register/verify.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register/success", name="app_register_success")
     */
    public function registerSuccess(): Response
    {
        return $this->render('register/success.html.twig');
    }
}
