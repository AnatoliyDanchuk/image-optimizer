<?php

namespace Framework\Endpoint\EndpointParamSpecification;

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

    public function validateValue($paramValue): void
    {
        $paramConstraints = $this->getParamConstraints();
        try {
            $this->validator->validate($paramValue, $paramConstraints);
        } catch (ValidatorException $exception) {
            throw new InvalidEndpointParamException($this, $paramValue, $exception);
        }
    }

    /**
     * @return Constraint[]
     */
    abstract protected function getParamConstraints(): array;

    abstract public function parseValue(string $value);
}