<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Commands;

use Illuminate\Console\Command;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Merge\ComponentsMerger;
use Mthole\OpenApiMerge\Merge\PathMerger;
use Mthole\OpenApiMerge\Merge\ReferenceNormalizer;
use Mthole\OpenApiMerge\Merge\SecurityPathMerger;
use Mthole\OpenApiMerge\OpenApiMerge;
use Mthole\OpenApiMerge\Reader\FileReader;
use Mthole\OpenApiMerge\Writer\DefinitionWriter;

class MergeOpenApiSchemasCommand extends Command
{
    protected $signature = 'openapi:merge';

    public function handle(): int
    {
        $files = array_merge(
            array_map(fn ($schema) => config("openapi.schemas.$schema.output"), config('openapi.merge.schemas')),
            config('openapi.merge.files', []),
        );
        $merger = new OpenApiMerge(
            new FileReader,
            [
                new PathMerger,
                new ComponentsMerger,
                new SecurityPathMerger,
            ],
            new ReferenceNormalizer
        );
        $writer = new DefinitionWriter;

        $mergedResult = $merger->mergeFiles(
            new File(array_shift($files)),
            array_map(
                static fn (string $file): File => new File($file),
                $files,
            ),
        );

        $outputFileName = config('openapi.merge.output', base_path('openapi_merged.yml'));
        touch($outputFileName);
        $outputFile = new File($outputFileName);
        $specificationFile = new SpecificationFile(
            $outputFile,
            $mergedResult->getOpenApi(),
        );
        file_put_contents(
            $outputFile->getAbsoluteFile(),
            $writer->write($specificationFile),
        );

        return 0;
    }
}
