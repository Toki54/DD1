<?php

namespace App\Command;

use App\Entity\PromotionCampaign;
use App\Repository\PromotionCampaignRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:premium-men-promotion:reset',
    description: 'Redémarre le compteur de l’offre Premium réservée aux hommes.'
)]
class ResetMenPremiumPromotionCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PromotionCampaignRepository $promotionCampaignRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Confirme la remise à zéro du compteur.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('force')) {
            $output->writeln('Ajoute --force pour confirmer la remise à zéro.');

            return Command::INVALID;
        }

        $campaign = $this->promotionCampaignRepository->find(PromotionCampaign::MEN_PREMIUM)
            ?? new PromotionCampaign();
        $campaign->setStartUserId($this->userRepository->getHighestId());

        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

        $output->writeln(sprintf(
            'Compteur remis à zéro : les utilisateurs avec un identifiant supérieur à %d seront pris en compte.',
            $campaign->getStartUserId()
        ));

        return Command::SUCCESS;
    }
}
