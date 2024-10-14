<?php

namespace System\Concurrency;

use Exception;

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
            throw new Exception("Maximum worker limit reached.");
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
    private $task; // Changed from callable to mixed
    private $process;

    public function __construct(callable $task)
    {
        $this->task = $task;
    }

    public function start(): void
    {
        // Use closure serialization if the task is callable
        $taskString = serialize($this->task);
        $this->process = popen('php -r ' . escapeshellarg("unserialize(" . var_export($taskString, true) . ");"), 'r');
    }

    public function join(): void
    {
        if ($this->process) {
            pclose($this->process);
        }
    }
}
