<?php

namespace Framework\ResponseBuilder;

use Framework\Exception\EndpointException;
use Symfony\Component\HttpFoundation\Response;

final class ExceptionResponseBuilder extends ResponseBuilderTemplate
{
    protected function getHttpCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    protected function getTtl(): int
    {
        // @todo: confirm to decrease ddos when endpoint can't return success answer.
        return 1;
    }

    public function getResponse(EndpointException $exception): Response
    {
        return $this->buildResponseWithContext([
            'input' => $exception->getEndpointAppliedInput(),
            'exception' => $exception->getDomainException()->getMessage(),
        ]);
    }
}