<?php

namespace SmartGroup\BuildInfoBundle\Command;

use SmartGroup\BuildInfoBundle\Service\BuildInfoGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'build-info:generate',
    description: 'Generate application version and Git build metadata'
)]
final class GenerateBuildInfoCommand extends Command
{
    public function __construct(private readonly BuildInfoGenerator $generator)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $info = $this->generator->generate();

        (new SymfonyStyle($input, $output))->success(sprintf(
            'Generated build info: %s, %s - %s',
            $info['version'],
            $info['commit'],
            $info['committed_at']
        ));

        return Command::SUCCESS;
    }
}
