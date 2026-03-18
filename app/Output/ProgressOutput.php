<?php

namespace App\Output;

use App\Output\Concerns\InteractsWithSymbols;
use PhpCsFixer\Runner\Event\FileProcessed;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProgressOutput
{
    use InteractsWithSymbols;

    /**
     * Holds the current number of processed files.
     *
     * @var int
     */
    protected $processed = 0;

    /**
     * Holds the number of symbols on the current terminal line.
     */
    protected int $symbolsPerLine;

    /**
     * Creates a new Progress Output instance.
     *
     * @param  EventDispatcherInterface  $dispatcher
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     */
    public function __construct(
        protected $dispatcher,
        protected $input,
        protected $output,
    ) {
        $this->symbolsPerLine = (new Terminal)->getWidth() - 4;
    }

    /**
     * Subscribes for file processed events.
     */
    public function subscribe(): void
    {
        $this->dispatcher->addListener(FileProcessed::NAME, $this->handle(...));
    }

    /**
     * Stops the file processed event subscription.
     */
    public function unsubscribe(): void
    {
        $this->dispatcher->removeListener(FileProcessed::NAME, $this->handle(...));
    }

    /**
     * Handle the given processed file event.
     *
     * @param  FileProcessed  $event
     */
    public function handle($event): void
    {
        $symbolsOnCurrentLine = $this->processed % $this->symbolsPerLine;

        if ($symbolsOnCurrentLine >= (new Terminal)->getWidth() - 4) {
            $symbolsOnCurrentLine = 0;
        }

        if ($symbolsOnCurrentLine === 0) {
            $this->output->writeln('');
            $this->output->write('  ');
        }

        $this->output->write($this->getSymbol($event->getStatus()));

        $this->processed++;
    }
}
