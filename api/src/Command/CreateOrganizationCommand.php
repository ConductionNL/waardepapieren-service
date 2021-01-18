<?php

// src/Command/CreateUserCommand.php

namespace App\Command;

use App\Service\ClaimService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateOrganizationCommand extends Command
{
    private $em;
    private $claimService;

    public function __construct(EntityManagerInterface $em, ClaimService $claimService)
    {
        $this->em = $em;
        $this->claimService = $claimService;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('create:organization')
            // the short description shown while running "php bin/console list"
            ->setDescription('Creation wizard for organizations');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('Rsin: ');
        $complete = false;

        $output->writeln([
            '<info>',
            '----------------------------',
            'Organization Creation Wizard',
            '----------------------------',
            'Please provide a valid Rsin.',
            '(9 digits number)',
            '----------------------------',
            '</info>',
        ]);

        while (!$complete) {
            $rsin = $helper->ask($input, $output, $question);
            if (!preg_match('/^[0-9]{9}$/', $rsin)) {
                $output->writeln([
                    '<error>',
                    'Invalid Rsin provided',
                    '</error>',
                ]);
            } else {
                $exists = $this->claimService->checkRsin($rsin);

                if ($exists) {
                    $output->writeln([
                        '<error>',
                        'Rsin already used please provide a new one',
                        '</error>',
                    ]);
                } else {
                    $complete = true;
                    $this->claimService->createOrganization($rsin);
                }
            }
        }

        $output->writeln([
            '<info>',
            'Organization Created',
            '</info>',
        ]);

        return Command::SUCCESS;
    }
}
