<pre><?php
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

try {
    $query = $client->createSelect();
    $query->setQuery($_GET['q'] ?? '*:*');
    $query->setFields(['id', 'content']);

    // Set up highlighting
    $hl = $query->getHighlighting();
    $hl->setFields('content');
    $hl->setSimplePrefix('<font color="red"><b>'); // HTML tag to prefix the highlighted term
    $hl->setSimplePostfix('</b></font>'); // HTML tag to postfix the highlighted term

    $hl->setFragSize(150); // Set fragment size to 150 characters
    $hl->setSnippets(3); // Return up to 3 highlighted snippets per field

    $resultset = $client->select($query);

    echo "Number of documents found: " . $resultset->getNumFound() . "\n\n";

    // Get highlighting results
    $highlighting = $resultset->getHighlighting();

    foreach ($resultset as $document) {
        echo "ID: " . $document->id . "\n";
        
        // Get highlighted snippet if available, otherwise use regular content
        $highlightedDoc = $highlighting->getResult($document->id);
        if ($highlightedDoc && !empty($highlightedDoc->getField('content'))) {
            $snippet = implode(' ... ', $highlightedDoc->getField('content'));
        } else {
            $snippet = substr($document->content, 0, 200);
        }
        
        echo "Content snippet: " . $snippet . "...\n\n";
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}

