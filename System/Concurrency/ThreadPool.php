<?php
namespace System\Concurrency;

use System\Preload\SystemExc;

class ThreadPool
{
    private array $workers = [];
    private int $maxWorkers;

    public function __construct(int $maxWorkers)
    {
        $this->maxWorkers = $maxWorkers;
    }

    public function submit(callable $task): void
    {
        if (count($this->workers) >= $this->maxWorkers) {
            throw new SystemExc(__CLASS__ . " - Maximum worker limit reached.");
        }

        $this->workers[] = new Worker($task);
        $this->workers[count($this->workers) - 1]->start();
    }

    public function wait(): void
    {
        foreach ($this->workers as $worker) {
            $worker->join();
        }
    }
}

class Worker
{
    private $task;
    private $process;

    public function __construct(callable $task)
    {
        $this->task = $task;
    }

    public function start(): void
    {
        // Use the task as a callable reference
        $this->process = popen('php -r ' . escapeshellarg('call_user_func(' . get_class($this->task[0]) . '::' . $this->task[1] . ');'), 'r');
    }

    public function join(): void
    {
        if ($this->process) {
            pclose($this->process);
        }
    }
}
