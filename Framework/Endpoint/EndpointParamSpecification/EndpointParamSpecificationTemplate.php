<?php

namespace Framework\Endpoint\EndpointParamSpecification;

use Framework\Validator;
use Symfony\Component\Validator\Constraint;
abstract class EndpointParamSpecificationTemplate
{
    public function __construct(
        private readonly Validator $validator,
    ) {
    }

    public function validateValue(mixed $value): void
    {
        $paramConstraints = $this->getParamConstraints();
        $this->validator->validate($value, $paramConstraints);
    }

    /**
     * @return Constraint[]
     */
    abstract protected function getParamConstraints(): array;

    abstract public function parseValue(string $value);

    final public function __toString(): string
    {
        return get_class($this);
    }
}