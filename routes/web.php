<?php declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;

if (config('openapi.docs.enabled') === false) {
    return;
}
Route::middleware(['web', 'auth'])->group(function () {

    Route::get('/api-docs/assets/{asset}', function (string $asset) {
        $allowedFiles = [
            'favicon-16x16.png',
            'favicon-32x32.png',
            'oauth2-redirect.html',
            'swagger-ui-bundle.js',
            'swagger-ui-bundle.js.map',
            'swagger-ui-standalone-preset.js',
            'swagger-ui-standalone-preset.js.map',
            'swagger-ui.css',
            'swagger-ui.css.map',
            'swagger-ui.js',
            'swagger-ui.js.map',
        ];
        if (! in_array($asset, $allowedFiles, true)) {
            abort(404, 'File not found');
        }

        $path = realpath(base_path('vendor/swagger-api/swagger-ui/dist/'.$asset));

        return (new Response(
            File::get($path),
            200,
            [
                'Content-Type' => (isset(pathinfo($asset)['extension']) && pathinfo($asset)['extension'] === 'css')
                    ? 'text/css'
                    : 'application/javascript',
            ]
        ))->setSharedMaxAge(31536000)
            ->setMaxAge(31536000)
            ->setExpires(new \DateTime('+1 year'));
    })->name('openapi.docs.assets');

    Route::get('/api-docs/schemas/{schema}', function (string $schema) {
        $path = config('openapi.schemas.'.$schema.'.output');

        if (! $path) {
            abort(404, 'Schema not found');
        }

        return str_ends_with($path, '.json')
            ? json_decode(File::get($path), true)
            : Yaml::parse(File::get($path));

    })->name('openapi.schema');

    Route::get('/api-docs/{schema?}', function (?string $schema = null) {
        $schema = $schema ?: 'default';

        return view('openapi::docs', ['title' => 'OpenAPI Docs: '.$schema, 'api' => $schema, 'url' => route('openapi.schema', ['schema' => $schema])]);
    })->name('openapi.docs');

});
