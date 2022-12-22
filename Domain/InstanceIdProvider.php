<?php

namespace Domain;

interface InstanceIdProvider
{
    public function getInstanceId(): string;
}