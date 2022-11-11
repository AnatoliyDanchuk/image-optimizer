<?php

namespace Api\EndpointSpecification;

use Framework\Endpoint\EndpointInput\FilledExpectedInput;
use Framework\Endpoint\EndpointTemplate\ApplicationHttpEndpointTemplate;

abstract class ReplicateImageWithResizingSpecification extends ApplicationHttpEndpointTemplate
{
    final protected function getHttpPath(): string
    {
        return '/replicate_with_resizing';
    }

    final protected function executeVanguardAction(FilledExpectedInput $input): array
     {
         return [
             'confirmation' => 'Started replication of image.',
         ];
     }

    final protected function executePostponedAction(FilledExpectedInput $input): array
     {
         $this->replicate($input);

         return [
             'confirmation' => 'Image replicated successfully.',
         ];
     }

     abstract protected function replicate(FilledExpectedInput $input): void;
 }