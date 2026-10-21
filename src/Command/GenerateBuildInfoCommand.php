<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\Command;

use SmartGroup\BuildInfoBundle\Exception\BuildInfoGenerationException;
use SmartGroup\BuildInfoBundle\Exception\InvalidBuildInfoException;
use SmartGroup\BuildInfoBundle\Service\BuildInfoGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
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

    protected function configure(): void
    {
        $this
            ->addOption('build-version', null, InputOption::VALUE_REQUIRED, 'Build version')
            ->addOption('revision', null, InputOption::VALUE_REQUIRED, 'Build revision')
            ->addOption('built-at', null, InputOption::VALUE_REQUIRED, 'Build timestamp in ISO 8601 format');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        try {
            $info = $this->generator->generate(
                $this->optionOrEnvironment($input, 'build-version', 'BUILD_INFO_VERSION'),
                $this->optionOrEnvironment($input, 'revision', 'BUILD_INFO_REVISION'),
                $this->optionOrEnvironment($input, 'built-at', 'BUILD_INFO_BUILT_AT'),
            );
        } catch (InvalidBuildInfoException | BuildInfoGenerationException) {
            $style->error('Unable to generate build metadata.');

            return Command::FAILURE;
        }

        $style->success(sprintf(
            'Generated build info: %s, %s - %s',
            $info->version,
            $info->revision,
            $info->builtAt,
        ));

        return Command::SUCCESS;
    }

    private function optionOrEnvironment(InputInterface $input, string $option, string $environmentVariable): ?string
    {
        $value = $input->getOption($option);
        if (is_string($value)) {
            return $value;
        }

        $value = getenv($environmentVariable);

        return false === $value ? null : $value;
    }
}
