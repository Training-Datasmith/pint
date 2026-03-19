<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\PathsRepository;
use App\Factories\ConfigurationFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class GitPathsRepository implements PathsRepository
{
    /**
     * Creates a new Paths Repository instance.
     *
     * @param  string  $path
     */
    public function __construct(
        /**
         * The project path.
         */
        protected $path
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function dirty()
    {
        $process = tap(new Process(['git', 'status', '--short', '--', '**.php']))->run();

        if (! $process->isSuccessful()) {
            abort(1, 'The [--dirty] option is only available when using Git.');
        }

        $dirtyFiles = collect(preg_split('/\R+/', (string) $process->getOutput(), flags: PREG_SPLIT_NO_EMPTY))
            ->mapWithKeys(fn ($file): array => [substr((string) $file, 3) => trim(substr((string) $file, 0, 3))])
            ->reject(fn ($status): bool => $status === 'D')
            ->map(fn ($status, $file) => $status === 'R' ? Str::after($file, ' -> ') : $file)
            ->values();

        return $this->processFileNames($dirtyFiles);
    }

    /**
     * {@inheritDoc}
     */
    public function diff($branch)
    {
        if (! preg_match('/^[A-Za-z0-9\/_.\-]+$/', (string) $branch)) {
            abort(1, 'The [--diff] branch name contains invalid characters.');
        }

        $files = [
            'committed' => tap(new Process(['git', 'diff', '--name-only', '--diff-filter=AM', "{$branch}...HEAD", '--', '**.php']))->run(),
            'staged' => tap(new Process(['git', 'diff', '--name-only', '--diff-filter=AM', '--cached', '--', '**.php']))->run(),
            'unstaged' => tap(new Process(['git', 'diff', '--name-only', '--diff-filter=AM', '--', '**.php']))->run(),
            'untracked' => tap(new Process(['git', 'ls-files', '--others', '--exclude-standard', '--', '**.php']))->run(),
        ];

        /** @var Collection<int, string> $files */
        $files = collect($files)
            ->each(fn ($process) => abort_if(
                boolean: ! $process->isSuccessful(),
                code: 1,
                message: 'The [--diff] option is only available when using Git.',
            ))
            ->map(fn ($process) => preg_split('/\R+/', (string) $process->getOutput(), flags: PREG_SPLIT_NO_EMPTY))
            ->flatten()
            ->unique()
            ->values()
            ->map(fn ($s): string => (string) $s);

        return $this->processFileNames($files);
    }

    /**
     * Process the files.
     *
     * @param  Collection<int, string>  $fileNames
     * @return array<int, string>
     */
    protected function processFileNames(Collection $fileNames): array
    {
        $processedFileNames = $fileNames
            ->map(function ($file): string {
                if (PHP_OS_FAMILY === 'Windows') {
                    $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
                }

                return $this->path.DIRECTORY_SEPARATOR.$file;
            })
            ->all();

        $files = array_values(array_map(fn (\Symfony\Component\Finder\SplFileInfo $splFile) => $splFile->getPathname(), iterator_to_array(
            ConfigurationFactory::finder()
            ->in($this->path)
            ->files()
        )));

        return array_values(array_intersect($files, $processedFileNames));
    }
}
