<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class ActionEndpoint extends PatchEndpoint {}
