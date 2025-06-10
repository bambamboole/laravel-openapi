<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Unit;

use Bambamboole\LaravelOpenApi\OpenApiGeneratorFactory;
use OpenApi\Util;
use PHPUnit\Framework\TestCase;

class ListTest extends TestCase
{
    public function test_it_matches_the_list_fixture()
    {
        $factory = new OpenApiGeneratorFactory;
        $config = require __DIR__.'/../Fixtures/List/list-config.php';
        $generator = $factory->create($config);

        $actualYaml = $generator->generate(Util::finder($config['folders']))->toYaml();
        //        file_put_contents(__DIR__.'/../Fixtures/List/list-actual.yml', $actualYaml);
        $expectedYaml = file_get_contents(__DIR__.'/../Fixtures/List/list-expected.yml');

        self::assertSame($expectedYaml, $actualYaml);
    }
}
