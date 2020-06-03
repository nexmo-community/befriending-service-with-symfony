<?php

namespace App\Command;

use App\Entity\Match;
use App\Entity\User;
use App\Util\VonageCallUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MatchUsersCommand extends Command
{
    protected static $defaultName = 'app:match-users';

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
            ->setDescription('Match users together for a phone call')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $matchRepository = $this->entityManager->getRepository(Match::class);
        $userRepository = $this->entityManager->getRepository(User::class);
        $activeUsers = $userRepository->findByActive(true);

        foreach ($activeUsers as $key => &$callerOne) {
            // Retrieve all active users within a 30 mile radius of current callerOne
            $matches = $userRepository->findPossibleMatchesByDistance($callerOne, $activeUsers, 30);
            
            // If there are less than 5 users returned, increase the search to 100 mile radius.
            if (count($matches) < 5) {
                $matches = $userRepository->findPossibleMatchesByDistance($callerOne, $activeUsers, 100);
            }
            
            // If there are no users within a 100 mile radius return 0
            if (count($matches) === 0) {
                unset($activeUsers[$key]);
            
                continue;
            }

            // Shuffle returned matches.
            shuffle($matches);
            // Remove callerOne from list of active users
            unset($activeUsers[$key]);
            
            $callerTwo = $matches[0][0];
            
            // Remove callerTwo from list of active users
            $matchKey = array_search($callerTwo, $activeUsers);
            unset($activeUsers[$matchKey]);
            
            // Make a call to createConferenceBetween() inside VonageCallUtil to connect the two users via phone call.
            $match = $this->vonageCallUtil->createConferenceBetween($callerOne, $callerTwo);
            
            // If successful, save the Match to the database.
            if ($match instanceof Match) {
                $this->entityManager->persist($match);
                $this->entityManager->flush();
            }
        }

        return 0;
    }
}
