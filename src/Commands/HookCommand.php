<?php

declare(strict_types=1);

namespace PHPCore\GitHooks\Commands;

use PHPCore\GitHooks\Hook;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HookCommand extends SymfonyCommand
{
    private string $hook;
    private array|string $contents;
    private string $composerDir;

    public function __construct(string $hook, array|string $contents, string $composerDir)
    {
        $this->hook = $hook;
        $this->contents = $contents;
        $this->composerDir = $composerDir;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName($this->hook)
            ->setDescription("Test your {$this->hook} hook")
            ->setHelp("This command allows you to test your {$this->hook} hook");
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $contents = Hook::getHookContents($this->composerDir, $this->contents, $this->hook);
        $outputMessage = [];
        $returnCode = SymfonyCommand::SUCCESS;
        exec($contents, $outputMessage, $returnCode);

        $output->writeln(implode(PHP_EOL, $outputMessage));

        return $returnCode;
    }
}
