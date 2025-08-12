<?php declare(strict_types=1);

it('generate config and docs', function () {
    $this->withoutExceptionHandling();
    $fs = new Illuminate\Filesystem\Filesystem;
    $expectedFolder = dirname(__DIR__, 2).'/mkdocs';
    $actualFolder = fixture('mkdocs/actual');
    $fs->deleteDirectory($actualFolder);
    $path = $actualFolder;

    $returnCode = $this->withoutMockingConsoleOutput()->artisan('mkdocs:generate', ['--path' => $path]);

    expect($fs->get($actualFolder.'/mkdocs.yml'))->toEqual($fs->get($expectedFolder.'/mkdocs.yml'));

    expect($returnCode)->toBe(0);
    //    $expectedFiles = $fs->allFiles($expectedFolder);
    //    foreach ($expectedFiles as $file) {
    //        $actualFile = str_replace($expectedFolder, $actualFolder, $file->getPathname());
    //        expect($fs->exists($actualFile))->toBeTrue();
    //        expect($fs->get($actualFile))->toEqual($fs->get($file->getPathname()));
    //    }
});
