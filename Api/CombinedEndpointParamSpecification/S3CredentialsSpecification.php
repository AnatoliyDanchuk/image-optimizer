<?php

namespace Api\CombinedEndpointParamSpecification;

use Api\EndpointParamSpecification\S3Credentials\BucketSpecification;
use Api\EndpointParamSpecification\S3Credentials\KeySpecification;
use Api\EndpointParamSpecification\S3Credentials\RegionSpecification;
use Api\EndpointParamSpecification\S3Credentials\SecretSpecification;
use Aws\Credentials\Credentials;
use Framework\Endpoint\CombinedEndpointParamSpecifications\ConvertableCombinedEndpointParamSpecifications;
use Framework\Endpoint\CombinedEndpointParamSpecifications\CombinedEndpointParamSpecificationsTemplate;
use Framework\Endpoint\EndpointInput\CombinedEndpointParam;
use Framework\IntegratedService\S3\S3Config;

final class S3CredentialsSpecification extends CombinedEndpointParamSpecificationsTemplate implements ConvertableCombinedEndpointParamSpecifications
{
    public BucketSpecification $bucketSpecification;
    public RegionSpecification $regionSpecification;
    public KeySpecification $keySpecification;
    public SecretSpecification $secretSpecification;

    public function __construct(
        BucketSpecification $bucketSpecification,
        RegionSpecification $regionSpecification,
        KeySpecification $keySpecification,
        SecretSpecification $secretSpecification
    )
    {
        $this->bucketSpecification = $bucketSpecification;
        $this->secretSpecification = $secretSpecification;
        $this->keySpecification = $keySpecification;
        $this->regionSpecification = $regionSpecification;
        parent::__construct(...func_get_args());
    }

    public function toApplicationObject(CombinedEndpointParam $combinedEndpointParam): S3Config
    {
        return new S3Config(
            new Credentials(
                $combinedEndpointParam->getValue($this->keySpecification),
                $combinedEndpointParam->getValue($this->secretSpecification),
            ),
            $combinedEndpointParam->getValue($this->regionSpecification),
            $combinedEndpointParam->getValue($this->bucketSpecification),
        );
    }
}