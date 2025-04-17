<?php declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;

if (config('openapi.docs.enabled') === false){
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
    })->name('openapi.docs');

    Route::get('/api-docs/{api?}', function (?string $api = null) {
        $api = $api ?: 'default';
        $path = config('openapi.apis.'.$api.'.output');

        $spec = str_ends_with($path, '.json')
            ? json_decode(File::get($path), true)
            : Yaml::parse(File::get($path));

        return view('openapi::docs', ['title' => 'OpenAPI Docs: '.$api, 'api' => $api, 'spec' => $spec]);
    })->name('openapi.docs');

});
