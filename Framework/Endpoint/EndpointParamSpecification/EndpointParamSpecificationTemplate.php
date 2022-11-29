<?php

namespace Framework\Endpoint\EndpointParamSpecification;

use Framework\Endpoint\EndpointInput\FoundParam;
use Framework\Exception\InvalidEndpointParamException;
use Framework\Exception\ValidatorException;
use Framework\Validator;
use Symfony\Component\Validator\Constraint;

abstract class EndpointParamSpecificationTemplate
{
    private Validator $validator;

    public function __construct(
        Validator $validator
    ) {
        $this->validator = $validator;
    }

    public function validateValue(FoundParam $foundParam): void
    {
        $paramConstraints = $this->getParamConstraints();
        try {
            $this->validator->validate($foundParam->value, $paramConstraints);
        } catch (ValidatorException $exception) {
            throw new InvalidEndpointParamException($foundParam, $exception);
        }
    }

    /**
     * @return Constraint[]
     */
    abstract protected function getParamConstraints(): array;

    abstract public function parseValue(string $value);

    /**
     * Used for search unique route params.
     * @see HttpEndpointLoader::fixRoutesWithSamePath()
     */
    final public function __toString(): string
    {
        return get_class($this);
    }
}