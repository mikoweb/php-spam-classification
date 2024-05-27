<?php

namespace App\Module\ML\Infrastructure\Reader;

use App\Core\Application\Path\AppPathResolver;
use App\Module\ML\Domain\Constant;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Ramsey\Collection\Collection;

use function Symfony\Component\String\u;

readonly class SpamDatasetReader
{
    public function __construct(
        private AppPathResolver $appPathResolver,
    ) {
    }

    /**
     * @return Collection<array<string, string>>
     *
     * @throws UnavailableStream
     * @throws Exception
     */
    public function read(
        string $datasetFilename,
        int $tooLongWordSize = Constant::DEFAULT_TO_LONG_WORD_SIZE,
        ?int $headerOffset = 0,
    ): Collection {
        $reader = Reader::createFromPath($this->appPathResolver->getDatasetPath($datasetFilename));
        $reader->setHeaderOffset($headerOffset);
        $collection = new Collection('array');

        foreach ($reader->getRecords() as $record) {
            if ($this->isRecordValid($record)) {
                $collection->add([
                    'message' => $this->normalizeMessage($this->clearMessage($record['MESSAGE'], $tooLongWordSize)),
                    'is_spam' => match (intval($record['CATEGORY'])) {
                        1 => 'yes',
                        default => 'no',
                    },
                ]);
            }
        }

        return $collection;
    }

    /**
     * @param array<string, string> $record
     */
    private function isRecordValid(array $record): bool
    {
        return isset($record['CATEGORY'])
            && !empty($record['MESSAGE'])
            && in_array($record['CATEGORY'], ['0', '1'], true);
    }

    private function clearMessage(string $message, int $tooLongWordSize): string
    {
        $result = trim(strip_tags(html_entity_decode(
            u($message)
                ->replaceMatches('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', '$1$3')
                ->replaceMatches('/([a-zA-Z\-]+:).*?(\r\n|\r|\n)/is', '')
                ->replaceMatches('/charset="[a-zA-Z0-9\-]*"/is', '')
                ->replaceMatches('/(------=_).*?(\r\n|\r|\n)/is', '')
                ->replace('This is a multi-part message in MIME format.', '')
                ->toString()
        )));

        foreach ($this->getTooLongWords($result, $tooLongWordSize) as $word) {
            $result = u($result)->replace($word, '')->toString();
        }

        return trim($result);
    }

    private function normalizeMessage(string $message): string
    {
        return u($message)
            ->replaceMatches('/\r\n|\r|\n/is', ' ')
            ->replaceMatches('/\s+/uis', ' ')
            ->collapseWhitespace()
            ->toString();
    }

    /**
     * @return string[]
     */
    private function getTooLongWords(string $message, int $tooLongSize): array
    {
        $tooLong = [];
        $words = preg_split('/\s/', $message);

        foreach ($words as $word) {
            if (strlen($word) > ($tooLongSize - 1) && !in_array($word, $tooLong)) {
                $tooLong[] = $word;
            }
        }

        return $tooLong;
    }
}
