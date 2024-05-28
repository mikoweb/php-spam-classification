<?php

namespace App\Module\ML\Domain;

final class Constant
{
    public const string SPAM_MODEL_FILENAME = 'spam.rbx';
    public const string DEFAULT_SPAM_DATASET_FILENAME = 'spam_NLP.csv';
    public const string DEFAULT_SPAM_CLEANSED_DATASET_FILENAME = 'spam_cleansed_NLP.csv';
    public const string DEFAULT_SPAM_TRAINING_DATASET_FILENAME = 'spam_training_NLP.csv';
    public const string DEFAULT_SPAM_TESTING_DATASET_FILENAME = 'spam_testing_NLP.csv';

    public const string DEFAULT_LANGUAGE = 'english';
    public const float DEFAULT_SPLIT_RATIO = 0.75;
    public const int DEFAULT_MIN_WORDS_COUNT = 2;
    public const int DEFAULT_MIN_DOCUMENT_COUNT = 4;
    public const float DEFAULT_MAX_DOCUMENT_RATIO = 0.5;
    public const int DEFAULT_TREE_ESTIMATORS = 200;
    public const float DEFAULT_TREE_RATIO = 0.15;
    public const bool DEFAULT_TREE_BALANCED = true;

    public const int DEFAULT_TO_LONG_WORD_SIZE = 46;

    public const array STOP_WORDS = [
        'i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 'your', 'yours', 'yourself', 'yourselves',
        'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 'herself', 'it', 'its', 'itself', 'they', 'them', 'their',
        'theirs', 'themselves', 'what', 'which', 'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'is', 'are',
        'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'a', 'an',
        'the', 'and', 'but', 'if', 'or', 'because', 'as', 'until', 'while', 'of', 'at', 'by', 'for', 'with', 'about',
        'against', 'between', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'to', 'from', 'up',
        'down', 'in', 'out', 'on', 'off', 'over', 'under', 'again', 'further', 'then', 'once', 'here', 'there', 'when',
        'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no',
        'nor', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 's', 't', 'can', 'will', 'just', 'don',
        'should', 'now',
    ];
}
