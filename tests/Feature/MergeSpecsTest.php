<?php declare(strict_types=1);

it('can merge specs', function () {
    $expected = fixture('merged_expected.json');
    $actual = fixture('merged_actual.json');

    $target = file_exists($expected) ? $actual : $expected;

    config([
        'openapi.schemas.default.output' => fixture('expected.yml'),
        'openapi.merge.files' => [fixture('another_spec.json')],
        'openapi.merge.output' => $target,
    ]);

    $this->artisan('openapi:merge')->assertExitCode(0);

    expect(file_exists($target))->toBeTrue();
    expect(filesize($target))->toBeGreaterThan(0);
    if ($target === $actual) {
        expect(file_get_contents($actual))->toEqual(file_get_contents($expected));
        unlink($actual);
    }
});
