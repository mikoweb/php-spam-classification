<?php

namespace App\Module\ML\Application\Model;

use App\Module\ML\Domain\Constant;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\RandomForest;
use Rubix\ML\Learner;
use Rubix\ML\Pipeline;
use Rubix\ML\Tokenizers\WordStemmer;
use Rubix\ML\Transformers\MultibyteTextNormalizer;
use Rubix\ML\Transformers\StopWordFilter;
use Rubix\ML\Transformers\TfIdfTransformer;
use Rubix\ML\Transformers\WordCountVectorizer;
use Rubix\ML\Transformers\ZScaleStandardizer;

/**
 * @see https://docs.rubixml.com/latest/classifiers/random-forest.html
 * @see https://docs.rubixml.com/latest/tokenizers/word-stemmer.html
 * @see https://docs.rubixml.com/latest/transformers/word-count-vectorizer.html
 * @see https://docs.rubixml.com/latest/transformers/stop-word-filter.html
 * @see https://docs.rubixml.com/latest/transformers/tf-idf-transformer.html
 * @see https://docs.rubixml.com/latest/transformers/z-scale-standardizer.html
 */
class LearnerFactory
{
    public static function createLearner(
        int $uniqueWordsNum,
        int $minDocumentCount = Constant::DEFAULT_MIN_DOCUMENT_COUNT,
        float $maxDocumentRatio = Constant::DEFAULT_MAX_DOCUMENT_RATIO,
        string $language = Constant::DEFAULT_LANGUAGE,
        int $treeMaxHeight = PHP_INT_MAX,
        int $treeEstimators = Constant::DEFAULT_TREE_ESTIMATORS,
        float $treeRatio = Constant::DEFAULT_TREE_RATIO,
        bool $treeBalanced = Constant::DEFAULT_TREE_BALANCED,
    ): Learner {
        return new Pipeline([
            new MultibyteTextNormalizer(),
            new StopWordFilter(Constant::STOP_WORDS),
            new WordCountVectorizer(
                maxVocabularySize: $uniqueWordsNum,
                minDocumentCount: $minDocumentCount,
                maxDocumentRatio: $maxDocumentRatio,
                tokenizer: new WordStemmer($language),
            ),
            new TfIdfTransformer(),
            new ZScaleStandardizer(),
        ], new RandomForest(
            new ClassificationTree($treeMaxHeight),
            estimators: $treeEstimators,
            ratio: $treeRatio,
            balanced: $treeBalanced,
        ));
    }
}
