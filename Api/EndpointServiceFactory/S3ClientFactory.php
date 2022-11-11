<?php

namespace Api\EndpointServiceFactory;

use Api\CombinedEndpointParamSpecification\S3CredentialsSpecification;
use Framework\Endpoint\EndpointInput\ExpectedInput;
use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\EndpointServiceFactory;
use Framework\IntegratedService\S3\S3Config;
use Framework\IntegratedService\S3\S3Client;

final class S3ClientFactory implements EndpointServiceFactory
{
    private S3Config $s3Credentials;
    private S3CredentialsSpecification $s3CredentialsSpecification;

    public function __construct(
        S3CredentialsSpecification $s3CredentialsSpecification
    )
    {
        $this->s3CredentialsSpecification = $s3CredentialsSpecification;
    }

    public function buildExpectedInput(): ExpectedInput
    {
        return new ExpectedInput(
            $this->s3CredentialsSpecification,
        );
    }

    public function applyInput(FilledExpectedInput $input): void
    {
        $this->s3Credentials = $input->getValueOfCombinedParams($this->s3CredentialsSpecification);
    }

    public function __invoke(): S3Client
    {
        return new S3Client(
            $this->s3Credentials,
        );
    }
 }