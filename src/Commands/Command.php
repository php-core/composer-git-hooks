<?php

declare(strict_types=1);

namespace PHPCore\GitHooks\Commands;

use PHPCore\GitHooks\Hook;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    private OutputInterface $output;

    protected string $dir;
    protected string $composerDir;
    protected array $hooks;
    protected string $gitDir;
    protected ?string $lockDir;
    protected bool $global;
    protected string $lockFile;

    abstract protected function init(InputInterface $input);

    abstract protected function command();

    /**
     * @throws \Exception
     */
    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->gitDir = $input->getOption('git-dir') ?: git_dir();
        $this->lockDir = $input->getOption('lock-dir');
        $this->global = (bool)$input->getOption('global');
        $this->dir = trim(
            $this->global && $this->gitDir === git_dir()
                ? dirname(global_hook_dir())
                : $this->gitDir
        );
        if ($this->global) {
            if (empty($this->dir)) {
                $this->global_dir_fallback();
            }
        }
        if ($this->gitDir === false) {
            $output->writeln('Git is not initialized. Skip setting hooks...');
            return SymfonyCommand::SUCCESS;
        }
        $this->lockFile = (null !== $this->lockDir ? ($this->lockDir . '/') : '') . Hook::LOCK_FILE;

        $dir = $this->global ? $this->dir : getcwd();

        $this->hooks = Hook::getValidHooks($dir);

        $this->init($input);
        $this->command();

        return SymfonyCommand::SUCCESS;
    }

    protected function global_dir_fallback()
    {
    }

    protected function info($info)
    {
        $info = str_replace('[', '<info>', $info);
        $info = str_replace(']', '</info>', $info);

        $this->output->writeln($info);
    }

    protected function debug($debug)
    {
        $debug = str_replace('[', '<comment>', $debug);
        $debug = str_replace(']', '</comment>', $debug);

        $this->output->writeln($debug, OutputInterface::VERBOSITY_VERBOSE);
    }

    protected function error($error)
    {
        $this->output->writeln("<fg=red>{$error}</>");
    }
}
