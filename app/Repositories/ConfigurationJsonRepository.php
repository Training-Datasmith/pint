<?php

declare(strict_types=1);

namespace App\Repositories;

class ConfigurationJsonRepository
{
    /**
     * Lists the finder options.
     *
     * @var array<int, string>
     */
    protected $finderOptions = [
        'exclude',
        'notPath',
        'notName',
    ];

    /**
     * Create a new Configuration Json Repository instance.
     *
     * @param  string|null  $path
     * @param  string|null  $preset
     */
    public function __construct(protected $path, protected $preset)
    {

    }

    /**
     * Get the finder options.
     *
     * @return array<string, array<int, string>|string>
     */
    public function finder()
    {
        return collect($this->get())
            ->filter(fn ($value, $key): bool => in_array($key, $this->finderOptions))
            ->toArray();
    }

    /**
     * Get the rules options.
     *
     * @return array<int, string>
     */
    public function rules()
    {
        return $this->get()['rules'] ?? [];
    }

    /**
     * Get the cache file location.
     *
     * @return string|null
     */
    public function cacheFile()
    {
        return $this->get()['cache-file'] ?? null;
    }

    /**
     * Get the preset option.
     *
     * @return string
     */
    public function preset()
    {
        return $this->preset ?: ($this->get()['preset'] ?? 'laravel');
    }

    /**
     * Get the configuration from the "pint.json" file.
     *
     * @return array<string, array<int, string>|string>
     */
    protected function get()
    {
        if (! is_null($this->path) && $this->fileExists((string) $this->path)) {
            $baseConfig = json_decode(file_get_contents($this->path), true);

            if (isset($baseConfig['extend'])) {
                $baseConfig = $this->resolveExtend($baseConfig);
            }

            return tap($baseConfig, function ($configuration): void {
                if (! is_array($configuration)) {
                    abort(1, sprintf('The configuration file [%s] is not valid JSON.', $this->path));
                }
            });
        }

        return [];
    }

    /**
     * Determine if a local file exists.
     *
     * @return bool
     */
    protected function fileExists(string $path)
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            abort(1, 'Remote configuration files are not supported. The [path] option must be a local file path.');
        }

        return file_exists($path);
    }

    /**
     * Resolve the file to extend.
     *
     * @param  array<string, array<int, string>|string>  $configuration
     * @return array<string, array<int, string>|string>
     */
    private function resolveExtend(array $configuration): array
    {
        $extend = (string) $configuration['extend'];

        if (str_starts_with($extend, 'http://') || str_starts_with($extend, 'https://')) {
            abort(1, 'The [extend] configuration key does not support remote URLs.');
        }

        $configDir = dirname((string) $this->path);
        $path = realpath($configDir.DIRECTORY_SEPARATOR.$extend);

        if ($path === false) {
            abort(1, sprintf('The configuration file to extend [%s] does not exist.', $extend));
        }

        if (! str_starts_with($path, realpath($configDir).DIRECTORY_SEPARATOR) && $path !== realpath($configDir)) {
            abort(1, sprintf('The configuration file to extend [%s] must be within the same directory as the Pint configuration.', $extend));
        }

        $extended = json_decode(file_get_contents($path), true);

        if (isset($extended['extend'])) {
            throw new \LogicException('Pint configuration cannot extend from more than 1 file.');
        }

        return array_replace_recursive($extended, $configuration);
    }
}
