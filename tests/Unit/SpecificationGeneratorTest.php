<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Unit;

use Bambamboole\LaravelOpenApi\OpenApiGeneratorFactory;
use OpenApi\Util;
use PHPUnit\Framework\TestCase;

class SpecificationGeneratorTest extends TestCase
{
    /**
     * @dataProvider provideFixtureFolders
     */
    public function test_it_matches_the_fixture(string $folder)
    {
        $factory = new OpenApiGeneratorFactory;
        $config = require $folder.'/config.php';
        $generator = $factory->create($config);

        $actualYaml = $generator->generate(Util::finder($config['folders']))->toYaml();
        $expectedYamlPath = $folder.'/expected.yml';
        if (! file_exists($expectedYamlPath)) {
            file_put_contents($expectedYamlPath, $actualYaml);
            self::markTestIncomplete('Expected YAML file does not exist. Created it for you. Please run test again.');
        }

        self::assertSame(file_get_contents($expectedYamlPath), $actualYaml);
    }

    public static function provideFixtureFolders()
    {
        $fixturesDir = __DIR__.'/../Fixtures';
        $folders = glob($fixturesDir.'/*', GLOB_ONLYDIR);

        return array_map(fn ($folder) => [$folder], $folders);
    }
}
