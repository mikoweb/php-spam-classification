<?php

namespace App\Module\ML\Application\Utils;

use function Symfony\Component\String\u;

class WordsUtils
{
    /**
     * @param array<string[]> $samples
     */
    public static function countUniqueWords(array $samples, int $minCount): int
    {
        $words = [];

        foreach ($samples as $sample) {
            $items = array_filter(
                preg_split('/\s/', $sample[0]),
                fn (string $word) => !empty($word)
            );

            foreach ($items as $item) {
                $word = u($item)->snake()->toString();

                if (!isset($words[$word])) {
                    $words[$word] = 1;
                } else {
                    ++$words[$word];
                }
            }
        }

        return count(array_filter($words, fn (int $count) => $count >= $minCount));
    }
}
