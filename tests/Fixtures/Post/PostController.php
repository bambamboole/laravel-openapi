<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\Fixtures\Post;

use Bambamboole\LaravelOpenApi\Attributes\PostEndpoint;

class PostController
{
    #[PostEndpoint(
        path: '/api/post',
        request: PostRequest::class,
        resource: PostResource::class,
        description: 'post resource',
    )]
    public function store(): PostResource
    {
        return new PostResource;
    }
}
