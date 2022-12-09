<?php

namespace Framework\Endpoint\EndpointInput;

final class FoundInputParam extends InputParam
{
    public function __toString(): string
    {
        return $this->paramPath->getSignature();
    }
}