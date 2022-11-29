<?php

namespace Framework\Exception;

use Framework\Endpoint\EndpointInput\FoundParam;

final class SameParamFoundInFewPlacesError extends ExceptionWithContext
{
    public function __construct(FoundParam ...$foundParams)
    {
        parent::__construct([
            'errorReason' => 'Expects the param found only in 1 place.',
            'foundParams' => $this->formatToOutput(...$foundParams),
        ]);
    }

    private function formatToOutput(FoundParam ...$foundParams): array
    {
        $formatted = [];
        foreach ($foundParams as $param) {
            $formatted[] = $param->formatToOutput();
        }

        return $formatted;
    }
}