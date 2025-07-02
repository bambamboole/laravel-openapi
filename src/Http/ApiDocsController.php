<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Http;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class ApiDocsController
{
    public function assets(string $asset): Response
    {
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

        $response = new Response(
            File::get($path),
            200,
            [
                'Content-Type' => (isset(pathinfo($asset)['extension']) && pathinfo($asset)['extension'] === 'css')
                    ? 'text/css'
                    : 'application/javascript',
            ]
        );

        $response->setSharedMaxAge(31536000)
            ->setMaxAge(31536000)
            ->setExpires(new \DateTime('+1 year'));

        return $response;
    }

    public function schema(string $schema)
    {
        $path = config('openapi.schemas.'.$schema.'.output');

        if (! $path) {
            abort(404, 'Schema not found');
        }

        return str_ends_with($path, '.json')
            ? json_decode(File::get($path), true)
            : Yaml::parse(File::get($path));
    }

    public function docs(?string $schema = null)
    {
        $schema = $schema ?: 'default';

        return view('openapi::docs', [
            'title' => 'OpenAPI Docs: '.$schema,
            'api' => $schema,
            'url' => route('openapi.schema', ['schema' => $schema]),
        ]);
    }
}
