<?php declare(strict_types=1);

it('generate config and docs', function () {
    $fs = new Illuminate\Filesystem\Filesystem;
    $expectedFolder = fixture('mkdocs/expected');
    $actualFolder = fixture('mkdocs/actual');
    $fs->deleteDirectory($actualFolder);
    $path = $fs->exists($expectedFolder.'/mkdocs.yml') ? $actualFolder : $expectedFolder;

    config(['mkdocs.paths' => [dirname(__DIR__, 2).'/src']]);

    $this->artisan('mkdocs:generate', ['path' => $path])->assertExitCode(0);

    if (! $fs->exists($actualFolder.'/mkdocs.yml')) {
        $this->markTestSkipped('Tests did only generated the expected files, not the actual ones.');
    }

    expect($fs->get($actualFolder.'/mkdocs.yml'))->toEqual($fs->get($expectedFolder.'/mkdocs.yml'));

    //    $expectedFiles = $fs->allFiles($expectedFolder);
    //    foreach ($expectedFiles as $file) {
    //        $actualFile = str_replace($expectedFolder, $actualFolder, $file->getPathname());
    //        expect($fs->exists($actualFile))->toBeTrue();
    //        expect($fs->get($actualFile))->toEqual($fs->get($file->getPathname()));
    //    }
});
