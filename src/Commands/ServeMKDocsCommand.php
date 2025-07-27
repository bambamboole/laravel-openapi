<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class ServeMKDocsCommand extends Command
{
    protected $signature = 'mkdocs:serve {path? : The base path for the docs output directory}';

    protected bool $shouldKeepRunning = true;

    protected ?int $receivedSignal = null;

    public function handle(): int
    {
        $this->trap([SIGTERM, SIGQUIT], fn (int $signal) => ($this->shouldKeepRunning = false) && ($this->receivedSignal = $signal));

        $docsBaseDir = $this->hasArgument('path') ? $this->argument('path') : config('mkdocs.output');

        $this->call('mkdocs:generate', ['path' => $docsBaseDir]);

        // Note: The command below is commented out because it does not work as expected in the current context.
        // $cmd = "docker run --rm -it -p 9090:8000 -v {$docsBaseDir}:/docs squidfunk/mkdocs-material serve";
        $cmd = [
            'docker', 'run', '--rm', '-it',
            '-p', '9090:9090',
            '-v', "{$docsBaseDir}:/docs",
            '-e', 'ADD_MODULES=mkdocs-material pymdown-extensions',
            '-e', 'LIVE_RELOAD_SUPPORT=true',
            '-e', 'FAST_MODE=true',
            '-e', 'DOCS_DIRECTORY=/docs',
            '-e', 'AUTO_UPDATE=true',
            '-e', 'UPDATE_INTERVAL=1',
            '-e', 'DEV_ADDR=0.0.0.0:9090',
            'polinux/mkdocs',
        ];

        $process = Process::tty()
            ->timeout(0)
            ->idleTimeout(0)
            ->start($cmd);

        while ($process->running() && $this->shouldKeepRunning) {
            if ($output = trim($process->latestOutput())) {
                $this->output->write($output);
            }
            if ($errorOutput = trim($process->latestErrorOutput())) {
                $this->output->error($errorOutput);
            }
            sleep(1);
        }
        if ($process->running()) {
            $this->info('Stopping MKDocs server...');
            $process->stop();
        }

        return self::SUCCESS;
    }
}
