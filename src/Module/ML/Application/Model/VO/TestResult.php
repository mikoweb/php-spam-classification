<?php

namespace App\Module\ML\Application\Model\VO;

readonly class TestResult
{
    public function __construct(
        public float $score,
        public TestInput $parameters,
    ) {
    }
}
