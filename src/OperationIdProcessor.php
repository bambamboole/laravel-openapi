<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi;

use OpenApi\Analysis;
use OpenApi\Generator;
use OpenApi\Annotations as OA;
class OperationIdProcessor
{
    public function __invoke(Analysis $analysis)
    {
        $allOperations = $analysis->getAnnotationsOfType(OA\Operation::class);

        /** @var OA\Operation $operation */
        foreach ($allOperations as $operation) {
            if (null === $operation->operationId) {
                $operation->operationId = Generator::UNDEFINED;
            }

            if (!Generator::isDefault($operation->operationId)) {
                continue;
            }

            $operationPath = str_replace('/', '.', ltrim($operation->path, '/'));

            $operation->operationId = strtoupper($operation->method) . '::' . $operationPath;
//            $operation->operationId = $operation->path.','.strtolower($operation->method);
        }
    }
}