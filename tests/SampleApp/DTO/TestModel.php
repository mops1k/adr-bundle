<?php

namespace AdrBundle\Test\SampleApp\DTO;

class TestModel
{
    public function __construct(
        public readonly bool $status = true
    ) {
    }
}
