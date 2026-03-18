<?php

declare(strict_types=1);

it('fixes the code', function () {
    [$statusCode, $output] = run('default', [
        'path' => base_path('tests/Fixtures/fixers/laravel_phpdoc_alignment.php'),
        '--preset' => 'laravel',
    ]);

    expect($statusCode)->toBe(1)
        ->and($output)
        ->toContain('  ⨯')
        ->toContain(
            <<<'EOF'
   /**
    * @param  string  $foo
  - * @param string  $bar
  - * @param  string $x
  + * @param  string  $bar
  + * @param  string  $x
    * @return string
    */
EOF,
        );
});
