<?php declare(strict_types=1);

use Bambamboole\LaravelOpenApi\OpenApiGeneratorFactory;
use OpenApi\Util;

dataset('fixtureFolders', function () {
    $fixturesDir = __DIR__.'/../Fixtures';
    $folders = glob($fixturesDir.'/*', GLOB_ONLYDIR);

    foreach ($folders as $folder) {
        yield [$folder];
    }
});

it('matches the fixture', function (string $folder) {
    $factory = new OpenApiGeneratorFactory;
    $config = require $folder.'/config.php';
    $generator = $factory->create($config);

    $actualYaml = $generator->generate(Util::finder($config['folders']))->toYaml();
    $expectedYamlPath = $folder.'/expected.yml';
    if (! file_exists($expectedYamlPath)) {
        file_put_contents($expectedYamlPath, $actualYaml);
        test()->markTestIncomplete('Expected YAML file does not exist. Created it for you. Please run test again.');
    }

    expect(file_get_contents($expectedYamlPath))->toBe($actualYaml);
})->with('fixtureFolders');
