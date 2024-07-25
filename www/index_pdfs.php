<?php
require_once(__DIR__.'/vendor/autoload.php');

use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;

$config = [
    'endpoint' => [
        'localhost' => [
            'host' => 'solr',
            'port' => 8983,
            'path' => '/',
            'core' => 'mycore'
        ]
    ]
];

$client = new Client(new Curl(), new EventDispatcher(), $config);

$pdfDir = __DIR__ . '/pdfs';

// Use RecursiveDirectoryIterator to get all PDF files recursively
$directory = new RecursiveDirectoryIterator($pdfDir);
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/\.pdf$/i');

foreach ($regex as $file) {
    $filePath = $file->getRealPath();

    $extract = $client->createExtract();
    $extract->setFile($filePath);
    $extract->setCommit(true);
    $extract->setUprefix('ignored_');
    $extract->addFieldMapping('content', 'content');

    // Add an id field
    $id = str_replace($pdfDir . '/', '', $filePath); // Use relative path as id
    $id = str_replace('.pdf', '', $id); // Remove .pdf extension
    $id = str_replace('/', '_', $id); // Replace slashes with underscores
    $extract->addFieldMapping('id', 'id');
    $extract->addParam('literal.id', $id);

    $extract->addParam('fmap.content', 'content');
    $extract->addParam('fmap.title', 'title');
    // $extract->addParam('fmap.ignore', '*');

    try {
        $result = $client->extract($extract);
        echo "Indexed: " . $filePath . " with ID: " . $id . "\n";
    } catch (Exception $e) {
        echo "Error indexing " . $filePath . ": " . $e->getMessage() . "\n";
    }
}

echo "Indexing complete.\n";

