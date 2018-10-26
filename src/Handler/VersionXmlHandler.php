<?php
declare(strict_types=1);

namespace BrowscapSite\Handler;

use BrowscapSite\Metadata\Metadata;
use DOMDocument;
use DOMElement;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Http\Response;

final class VersionXmlHandler implements RequestHandlerInterface
{
    /** @var Metadata */
    private $metadata;

    /** @var array */
    private $fileList;

    public function __construct(Metadata $metadata, array $fileList)
    {
        $this->metadata = $metadata;
        $this->fileList = $fileList;
    }

    /**
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestUri = $request->getUri();
        $baseUrl = $requestUri->getScheme() . '://' . $requestUri->getHost();

        $xml = new DOMDocument();

        $rss = $xml->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $xml->appendChild($rss);

        $channel = $xml->createElement('channel');
        $rss->appendChild($channel);

        $releaseDate = $this->metadata->released()->format('r');

        $channel->appendChild($this->createTextNode($xml, 'title', 'Browser Capabilities Project Update Service'));
        $channel->appendChild($this->createTextNode($xml, 'link', $baseUrl));
        $channel->appendChild($this->createTextNode($xml, 'description', 'The Browser Capabilities Project maintains and freely distributes a regularly updated browscap.ini file. The project\'s data is also available in many other formats that make it useful in a variety of situations. Last updated: ' . $metadata['released']));
        $channel->appendChild($this->createTextNode($xml, 'language', 'en-US'));
        $channel->appendChild($this->createTextNode($xml, 'pubDate', $releaseDate));
        $channel->appendChild($this->createTextNode($xml, 'lastBuildDate', $releaseDate));
        $channel->appendChild($this->createTextNode($xml, 'ttl', '1440'));

        foreach ($this->fileList as $format => $files) {
            foreach ($files as $fileCode => $fileInfo) {
                $channel->appendChild($this->createFileItem($xml, $releaseDate, $this->metadata->version(), $fileCode, $fileInfo, $baseUrl));
            }
        }

        $response = (new Response())
            ->withHeader('Content-type', 'text/xml');

        $response->getBody()->write($xml->saveXML());

        return $response;
    }

    private function createTextNode(DOMDocument $xml, $element, $content): DOMElement
    {
        $element = $xml->createElement($element);
        $elementContent = $xml->createTextNode($content);
        $element->appendChild($elementContent);
        return $element;
    }

    private function createFileItem(DOMDocument $xml, $pubDate, $version, $fileCode, array $fileInfo, $baseUrl): DOMElement
    {
        $item = $xml->createElement('item');

        $item->appendChild($this->createTextNode($xml, 'title', $fileInfo['name']));
        $item->appendChild($this->createTextNode($xml, 'link', $baseUrl . '/stream?q=' . $fileCode));
        $item->appendChild($this->createTextNode($xml, 'description', $fileInfo['description']));
        $item->appendChild($this->createTextNode($xml, 'pubDate', $pubDate));

        $guid = $this->createTextNode($xml, 'guid', $fileCode . '.' . $version);
        $guid->setAttribute('isPermaLink', 'false');
        $item->appendChild($guid);

        return $item;
    }
}
