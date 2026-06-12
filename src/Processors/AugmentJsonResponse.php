<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Attributes as OAXT;
use Radebatz\OpenApi\Extras\Processors\Concerns\ResolvesDescription;

class AugmentJsonResponse
{
    use ResolvesDescription;

    public function __invoke(Analysis $analysis): void
    {
        $responses = $analysis->getAnnotationsOfType([OAX\JsonResponse::class, OAXT\JsonResponse::class]);

        foreach ($responses as $response) {
            if (Generator::isDefault($response->source)) {
                continue;
            }

            if (Generator::isDefault($response->content)) {
                $this->createJsonContent($response, $analysis);
            }

            if (Generator::isDefault($response->description)) {
                $response->description = $this->resolveDescription($response->source, $analysis);
            }
        }
    }

    protected function createJsonContent(OAX\JsonResponse|OAXT\JsonResponse $response, Analysis $analysis): void
    {
        $context = new Context(['nested' => $response], $response->_context);
        $jsonContent = new OA\JsonContent(['ref' => $response->source, '_context' => $context]);

        $analysis->addAnnotation($jsonContent, $context);
    }
}
