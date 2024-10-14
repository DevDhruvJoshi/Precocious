<?php namespace System\Config;

use System\Preload\PromiseExc;

class Promise {
    private array $onResolve = [];
    private array $onReject = [];
    private string $state = 'pending';
    private mixed $value;
    private mixed $reason;

    public function __construct(callable $executor) {
        $resolve = function ($value) {
            $this->resolve($value);
        };
        $reject = function ($reason) {
            $this->reject($reason);
        };

        try {
            // Wrap the executor to ensure it receives the necessary arguments
            $executor($resolve, $reject);
        } catch (\Exception $e) {
            $reject($e->getMessage());
        }
    }

    private function resolve(mixed $value = null): void {
        if ($this->state === 'pending') {
            $this->state = 'fulfilled';
            $this->value = $value; // Now this can be null
            foreach ($this->onResolve as $callback) {
                $callback($value);
            }
        }
    }

    private function reject(mixed $reason): void {
        if ($this->state === 'pending') {
            $this->state = 'rejected';
            $this->reason = $reason;
            foreach ($this->onReject as $callback) {
                $callback($reason);
            }
        }
    }

    public function then(callable $onResolve): self {
        if ($this->state === 'fulfilled') {
            $onResolve($this->value);
        } elseif ($this->state === 'pending') {
            $this->onResolve[] = $onResolve;
        }
        return $this;
    }

    public function catch(callable $onReject): self {
        if ($this->state === 'rejected') {
            $onReject($this->reason);
        } elseif ($this->state === 'pending') {
            $this->onReject[] = $onReject;
        }
        return $this;
    }

    public function wait(): mixed {
        while ($this->state === 'pending') {
            usleep(100); // Sleep for 100 microseconds
        }
        if ($this->state === 'fulfilled') {
            return $this->value;
        } elseif ($this->state === 'rejected') {
            throw new PromiseExc($this->reason);
        }
        return null; // Ensures a return value in all code paths
    }
}
