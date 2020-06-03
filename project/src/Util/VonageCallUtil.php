<?php
 
namespace App\Util;
 
use App\Entity\Match;
use App\Entity\User;
use App\Util\VonageVerifyUtil;
use Nexmo\Client as VonageClient;
use Nexmo\Client\Credentials\Keypair;
 
class VonageCallUtil
{
    /** @var VonageClient */
    protected $client;
 
    /** @var VonageVerifyUtil */
    protected $vonageVerifyUtil;
 
    public function __construct(VonageVerifyUtil $vonageVerifyUtil)
    {
        $keypair = new Keypair(
            file_get_contents($_ENV['VONAGE_APPLICATION_PRIVATE_KEY_PATH']),
            $_ENV['VONAGE_APPLICATION_ID']
        );
 
        $this->client = new VonageClient($keypair);
        $this->vonageVerifyUtil = $vonageVerifyUtil;
    }

    public function createConferenceBetween(User $callerOne, User $callerTwo): Match
    {
        $conferenceName = $callerOne->getName() . '_' . $callerTwo->getName();
     
        // Save conference name to the database, along with the callerOne and callerTwo.
        $match = (new Match())
            ->setCallerOne($callerOne)
            ->setCallerTwo($callerTwo)
            ->setConferenceName($conferenceName)
            ->setCreatedAt((new \DateTime()))
            ->setUpdatedAt((new \DateTime()));
     
        $ncco = [
            [
                'action' => 'talk',
                'voiceName' => 'Amy',
                'text' => 'You are on a Befriending service call, I will connect you to someone random from my database that shouldn\'t be far from your town. Please enjoy your call. If you would like to join now, enter 1 for yes, or 2 for no.'
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
     
        $this->makeCall($callerOne, $ncco);
        $this->makeCall($callerTwo, $ncco);
     
        return $match;
    }
    
    public function makeCall(User $caller, array $ncco)
    {
        try {
            $number = $this->vonageVerifyUtil->getInternationalizedNumber($caller);
     
            $call = $this->client->calls()->create([
                'to' => [[
                    'type' => 'phone',
                    'number' => preg_replace('/\s+/', '', $number)
                ]],
                'from' => [
                    'type' => 'phone',
                    'number' => $_ENV['VONAGE_NUMBER']
                ],
                'ncco' => $ncco
            ]);
        } catch (\Exception $e) {
            dump($e->getMessage());
        }
    }
}