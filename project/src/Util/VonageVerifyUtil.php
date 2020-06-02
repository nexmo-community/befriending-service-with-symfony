<?php
 
namespace App\Util;
 
use App\Entity\User;
use Nexmo\Client as VonageClient;
use Nexmo\Client\Credentials\Basic;
use Nexmo\Verify\Verification;
 
class VonageVerifyUtil
{
    /** @var VonageClient */
    protected $client;
 
    public function __construct()
    {
        $this->client = new VonageClient(
            new Basic(
                $_ENV['VONAGE_API_KEY'],
                $_ENV['VONAGE_API_SECRET']
            )
        );     
    }

    public function getInternationalizedNumber(User $user): ?string
    {
        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
     
        $phoneNumberObject = $phoneNumberUtil->parse(
            $user->getPhoneNumber(),
            $user->getCountryCode()
        );
     
        if (!$phoneNumberUtil->isValidNumberForRegion(
            $phoneNumberObject,
            $user->getCountryCode())
        ) {
            return null;
        }
     
        return $phoneNumberUtil->format(
            $phoneNumberObject,
            \libphonenumber\PhoneNumberFormat::INTERNATIONAL
        );
    }
     
    public function getNationalizedNumber(string $phoneNumber)
    {
        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneNumberUtil->parse($phoneNumber);
     
        return '0' . $phoneNumberObject->getNationalNumber();
    }

    public function sendVerification(User $user)
    {
        $internationalizedNumber = $this->getInternationalizedNumber($user);
     
        if (!$internationalizedNumber) {
            return null;
        }
     
        $verification = new Verification(
            $internationalizedNumber,
            $_ENV['VONAGE_BRAND_NAME'],
            ['workflow_id' => 2]
        );
     
        return $this->client->verify()->start($verification);
    }

    public function verify(string $requestId, string $verificationCode)
    {
        $verification = new Verification($requestId);
     
        return $this->client->verify()->check($verification, $verificationCode);
    }

    public function getRequestId(Verification $verification): ?string
    {
        $responseData = $verification->getResponseData();
     
        if (empty($responseData)) {
            return null;
        }
     
        return $responseData['request_id'];
    }
}