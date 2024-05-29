<?php

namespace App\Module\ML\Application\Model\VO;

readonly class TestInput
{
    public function __construct(
        public int $minDocumentCount,
        public float $maxDocumentRatio,
        public int $treeEstimators,
        public float $treeRatio,
    ) {
    }
}
