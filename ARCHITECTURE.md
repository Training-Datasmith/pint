# Architecture: laravel/pint

## Purpose

Laravel Pint is an opinionated PHP code style fixer built on top of PHP-CS-Fixer.
It provides a zero-configuration experience for Laravel projects while supporting
custom configuration via `pint.json`. Distributed as a standalone PHAR.

## Directory Structure

```
app/
  Actions/
    FixCode.php              # Orchestrates PHP-CS-Fixer runner (sequential or parallel)
    ElaborateSummary.php     # Builds the summary report from fix results
  Commands/
    DefaultCommand.php       # The single CLI command (pint [paths] [options])
  Contracts/
    PathsRepository.php      # Interface for resolving which files to fix
  Exceptions/
    HandleExceptions.php     # Global exception handler for cleaner error output
  Factories/
    ConfigurationFactory.php          # Builds PHP-CS-Fixer Configuration from pint.json
    ConfigurationResolverFactory.php  # Creates ConfigurationResolver from CLI input
  Fixers/
    TypeAnnotationsOnlyFixer.php  # Custom fixer: removes non-type PHPDoc annotations
  Kernel.php                 # Laravel Zero application kernel
  Output/
    AgentReporter.php        # JSON/machine-readable output reporter for agent use
    Concerns/InteractsWithSymbols.php  # Shared ANSI symbol rendering for output
    ProgressOutput.php       # Real-time progress display during fixing
    SummaryOutput.php        # Final summary display (files changed, fixers applied)
  Providers/
    ActionsServiceProvider.php       # Binds FixCode, ElaborateSummary as singletons
    AppServiceProvider.php           # Application-level service bindings
    CommandsServiceProvider.php      # Registers the DefaultCommand
    RepositoriesServiceProvider.php  # Registers ConfigurationJsonRepository
  Project.php                # Resolves the project root and pint.json path
  Repositories/
    ConfigurationJsonRepository.php  # Reads pint.json configuration
    GitPathsRepository.php           # Resolves dirty/uncommitted file paths (--dirty flag)
  ValueObjects/
    Issue.php                # Represents a single fixer issue (file + fixer + diff)

overrides/
  FixerFactory.php              # Extends PHP-CS-Fixer FixerFactory to inject custom fixers
  Runner/Parallel/ProcessFactory.php  # Custom parallel process factory

resources/
  presets/                    # Built-in preset configurations (laravel, psr12, per, symfony)
  boost/guidelines/           # Blade templates for guideline display

config/                       # Laravel Zero app configuration (app, commands, view)
bootstrap/app.php             # Laravel Zero bootstrap
```

## Key Design Decisions

### Laravel Zero as the Application Container

Pint uses Laravel Zero (a micro-framework built on Laravel) as its DI container and
command runner. This gives access to Laravel's service container, event system, and
configuration loading while keeping the binary lean.

### Thin Wrapper over PHP-CS-Fixer

All actual code fixing is delegated to PHP-CS-Fixer's `Runner`. Pint's value is in:
preset configurations (`resources/presets/`), opinionated defaults, and a simpler CLI
with better output formatting.

### Preset System

`resources/presets/laravel.php`, `psr12.php`, `per.php`, and `symfony.php` return
PHP-CS-Fixer rule arrays. The active preset is determined by `pint.json` or defaults
to `laravel`. Presets are merged with user overrides from `pint.json`.

### Parallel Execution

The `--parallel` flag uses PHP-CS-Fixer's native parallel runner with a custom
`ProcessFactory` override for Phar-compatible subprocess spawning.

## Extension Points

- Custom fixers can be added by contributing to `app/Fixers/` and registering them in
  `overrides/FixerFactory.php`.
- Custom presets: add a `pint.json` to your project root with `"preset"` and custom `"rules"`.

## Dependency Flow

```
DefaultCommand
  └─ FixCode::execute()
       ├─ ConfigurationResolverFactory::fromIO() → pint.json + CLI options + preset
       └─ Runner (PHP-CS-Fixer) → fixes files → returns changes
            └─ ElaborateSummary::execute() → SummaryOutput → renders results
```
