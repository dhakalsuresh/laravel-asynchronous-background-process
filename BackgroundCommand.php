<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class BackgroundCommand
{
    /**
     * The artisan command.
     *
     * @var string
     */
    private $command;

    /**
     * The command which should be executed before.
     *
     * @var string
     */
    private $before;

    /**
     * The command which should be executed after.
     *
     * @var string
     */
    private $after;

    /**
     * Create background command by the given parameters.
     *
     * @param string $command
     * @param string $before
     * @param string $after
     * @return \App\Services\BackgroundCommand
     */
    public function init(string $command, string $before = '', string $after = '')
    {
        $this->command = $command;
        $this->before = $before;
        $this->after = $after;

        return $this;
    }


    /**
     * Run the command in background.
     *
     * @return void
     */
    public function run()
    {
        $command = $this->composeCommand();
        $command = $this->is_windows_os()
            ? "start /B {$command}"
            : "({$command}) > /dev/null 2>&1 &";

        
        $process = $this->setProcess($command);
        $process->setTimeout(1200);
        
        if (!$process->isRunning()) {
            $process->run();
        }
        
        if (!$process->isSuccessful()) {
            throw new \Exception($process);
        }
    }

    /**
     * ser process with command
     *
     * @param string $command
     * @return Process
     */
    protected function setProcess(string $command): Process
    {
        return new Process($command);
    }

    /**
     * Compose the command.
     *
     * @return string
     */
    protected function composeCommand()
    {
        return collect()
            ->when($this->before, function (Collection $collection) {
                return $collection->push($this->before);
            })
            ->push("{$this->getPhpExecutable()} {$this->getArtisan()} {$this->command}")
            ->when($this->after, function (Collection $collection) {
                return $collection->push($this->after);
            })
            ->implode(' && ');
    }

    /**
     * Get the path to PHP executable.
     *
     * @return string
     */
    protected function getPhpExecutable()
    {
        return (new PhpExecutableFinder)->find();
    }

    /**
     * Get the path to artisan.
     *
     * @return string
     */
    protected function getArtisan()
    {
        return defined('ARTISAN_BINARY') ? ARTISAN_BINARY : base_path('artisan');
    }

    /**
     * Check whether the operating system is Windows or not.
     *
     * @return bool
     */
    protected function is_windows_os(): bool
    {
        return stripos(php_uname(), 'windows') === 0;
    }
}
