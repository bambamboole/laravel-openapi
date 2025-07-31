<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use OpenApi\Generator;

trait HasEndpointHelpers
{
    protected function response(string $status, string $description, ?array $properties = null): Response
    {
        return new Response(response: $status, description: $description, content: $properties ? new JsonContent(properties: $properties) : null);
    }

    protected function response401(): Response
    {
        return new Response(response: '401', description: 'Unauthorized');
    }

    protected function response403(): Response
    {
        return new Response(response: '403', description: 'Unauthorized');
    }

    protected function response404(): Response
    {
        return new Response(response: '404', description: 'Not Found');
    }

    protected function compileX(bool $isInternal, ?\DateTimeInterface $deprecated): string|array
    {
        $x = [];
        if ($isInternal) {
            $x['internal'] = true;
        }
        if ($deprecated) {
            $x['deprecated_on'] = $deprecated->format('Y-m-d');
        }

        return empty($x) ? Generator::UNDEFINED : $x;
    }

    protected function modifyDescription(?string $description, \BackedEnum|string|null $featureFlag): string
    {
        if (! $description && ! $featureFlag) {
            return Generator::UNDEFINED;
        }
        if (! $featureFlag) {
            return $description ?? Generator::UNDEFINED;
        }
        if ($featureFlag instanceof \BackedEnum) {
            $featureFlag = $featureFlag->value;
        }
        $featureFlagNote = "This endpoint is only available if the feature flag `{$featureFlag}` is enabled.\n\n";

        return $featureFlagNote.($description ?? '');
    }
}
