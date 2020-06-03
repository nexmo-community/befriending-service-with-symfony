<?php

namespace App\Command;

use App\Entity\Match;
use App\Util\VonageCallUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetFeedbackCommand extends Command
{
    protected static $defaultName = 'app:get-feedback';

    /** @var VonageCallUtil */
    protected $vonageCallUtil;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(
        VonageCallUtil $vonageCallUtil,
        EntityManagerInterface $entityManager
    ) {
        $this->vonageCallUtil = $vonageCallUtil;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Contact all previous matches to get their feedback.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $matchRepository = $this->entityManager->getRepository(Match::class);
 
        // Get matches that haven't had any feedback.
        $matches = $matchRepository->getTodaysMatches();
         
        if (empty($matches)) {
            return 0;
        }

        // Loop through all retrieved matches
        foreach ($matches as $match) {
            if (null === $match->getCallerOneCallSuccessful()) {
                // Call callerOne as we do not have feedback from them.
                $this->vonageCallUtil->makeFeedbackCall(
                    $match->getCallerOne()
                );
            }
         
            if (null === $match->getCallerTwoCallSuccessful()) {
                // Call callerTwo as we do not have feedback from them.
                $this->vonageCallUtil->makeFeedbackCall(
                    $match->getCallerTwo()
                );
            }
         
            // Save changes to the database.
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
         
        return 0;
    }
}
