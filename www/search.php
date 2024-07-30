<pre><?php
require_once(__DIR__.'/vendor/autoload.php');

use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;

$config = [
    'endpoint' => [
        'search' => [
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
    $query->setFields(['id', 'content', 'subject', 'author', 'keywords', 'date', 'title']);

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
        print_r($document);
        echo "ID: " . $document->id . "\n\n";
        echo "Title: " . $document->title . "\n\n";
        echo "Author: " . $document->author . "\n\n";
        echo "Subject: " . $document->subject . "\n\n";
        echo "Keywords: " . $document->keywords . "\n\n";
        echo "Date: " . $document->date . "\n\n";
        // Get highlighted snippet if available, otherwise use regular content
        $highlightedDoc = $highlighting->getResult($document->id);
        if ($highlightedDoc && !empty($highlightedDoc->getField('content'))) {
            $snippet = implode("\n\n", $highlightedDoc->getField('content'));
        } else {
            $snippet = substr($document->content, 0, 200);
        }
        
        echo "Content snippet:\n\n";
        echo '<div style="border: 1px solid #000; padding: 10px; display: inline-block;">';
        echo $snippet;
        echo "</div>\n\n";
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}

