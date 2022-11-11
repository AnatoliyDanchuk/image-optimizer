<?php

namespace Framework\Endpoint\EndpointParamSpecification;

interface InHttpUrlQueryAllowed
{
    public function getUrlQueryParamName(): string;
}