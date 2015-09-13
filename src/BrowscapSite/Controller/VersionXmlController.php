<?php

namespace BrowscapSite\Controller;

use Symfony\Component\HttpFoundation\Response;

class VersionXmlController
{
    protected $baseHost;
    protected $fileList;
    protected $metadata;

    public function __construct(array $fileList, array $metadata)
    {
        $this->baseHost = 'http://' . $_SERVER['SERVER_NAME'];
        $this->fileList = $fileList;
        $this->metadata = $metadata;
    }

    public function indexAction()
    {
        $metadata = $this->metadata;

        $xml = new \DOMDocument();

        $rss = $xml->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $xml->appendChild($rss);

        $channel = $xml->createElement('channel');
        $rss->appendChild($channel);

        $channel->appendChild($this->createTextNode($xml, 'title', 'Browser Capabilities Project Update Service'));
        $channel->appendChild($this->createTextNode($xml, 'link', $this->baseHost));
        $channel->appendChild($this->createTextNode($xml, 'description', 'The Browser Capabilities Project maintains and freely distributes a regularly updated browscap.ini file. The project\'s data is also available in many other formats that make it useful in a variety of situations. Last updated: ' . $metadata['released']));
        $channel->appendChild($this->createTextNode($xml, 'language', 'en-US'));
        $channel->appendChild($this->createTextNode($xml, 'pubDate', $metadata['released']));
        $channel->appendChild($this->createTextNode($xml, 'lastBuildDate', $metadata['released']));
        $channel->appendChild($this->createTextNode($xml, 'ttl', '1440'));

        foreach ($this->fileList as $format => $files) {
            foreach ($files as $fileCode => $fileInfo) {
                $channel->appendChild($this->createFileItem($xml, $metadata['released'], $metadata['version'], $fileCode, $fileInfo));
            }
        }

        $response = new Response();
        $response->headers->set('Content-type', 'text/xml'); #application/rss+xml
        $response->setContent($xml->saveXML());
        return $response;
    }

    public function createTextNode(\DOMDocument $xml, $element, $content)
    {
        $element = $xml->createElement($element);
        $elementContent = $xml->createTextNode($content);
        $element->appendChild($elementContent);
        return $element;
    }

    public function createFileItem(\DOMDocument $xml, $pubDate, $version, $fileCode, array $fileInfo)
    {
        $item = $xml->createElement('item');

        $item->appendChild($this->createTextNode($xml, 'title', $fileInfo['name']));
        $item->appendChild($this->createTextNode($xml, 'link', $this->baseHost . '/stream?q=' . $fileCode));
        $item->appendChild($this->createTextNode($xml, 'description', $fileInfo['description']));
        $item->appendChild($this->createTextNode($xml, 'pubDate', $pubDate));

        $guid = $this->createTextNode($xml, 'guid', $fileCode . '.' . $version);
        $guid->setAttribute('isPermaLink', 'false');
        $item->appendChild($guid);

        return $item;
    }
}
