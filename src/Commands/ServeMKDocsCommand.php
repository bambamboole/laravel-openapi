<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class ServeMKDocsCommand extends Command
{
    protected $signature = 'mkdocs:serve {--path : The base path for the docs output directory} {--port=9090 : The port to serve the documentation on}';

    protected bool $shouldKeepRunning = true;

    protected ?int $receivedSignal = null;

    public function handle(): int
    {
        $this->trap([SIGTERM, SIGQUIT], function (int $signal) {
            $this->receivedSignal = $signal;
            $this->shouldKeepRunning = false;
            $this->info('Stopping MKDocs server...');
        });

        $docsBaseDir = $this->option('path') ?: config('mkdocs.output');
        $this->call('mkdocs:generate', ['--path' => $docsBaseDir]);

        $port = $this->option('port');
        $cmd = config('mkdocs.commands.serve', [
            'docker', 'run', '--rm', '-it',
            '-p', '{port}:{port}',
            '-v', '{path}:/docs',
            '-e', 'ADD_MODULES=mkdocs-material pymdown-extensions',
            '-e', 'LIVE_RELOAD_SUPPORT=true',
            '-e', 'FAST_MODE=true',
            '-e', 'DOCS_DIRECTORY=/docs',
            '-e', 'AUTO_UPDATE=true',
            '-e', 'UPDATE_INTERVAL=1',
            '-e', 'DEV_ADDR=0.0.0.0:{port}',
            'polinux/mkdocs',
        ]);
        if (is_array($cmd)) {
            $cmd = array_map(fn (string $part) => str_replace(['{path}', '{port}'], [$docsBaseDir, $port], $part), $cmd);
        } else {
            $cmd = str_replace(['{path}', '{port}'], [$docsBaseDir, $port], $cmd);
        }

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
            $process->stop();
        }

        return self::SUCCESS;
    }
}
