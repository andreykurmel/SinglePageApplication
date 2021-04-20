<?php


namespace Vanguard\Services\Tablda;

class HtmlXmlService
{
    /**
     * @param \DOMNode $node
     * @param string $xpath
     * @return \DOMNodeList
     */
    protected function elXpath(\DOMNode $node, string $xpath)
    {
        $dom = new \DOMDocument();
        $dom->loadHTML( '<html></html>', LIBXML_NOERROR );
        $imp = $dom->importNode($node, true);
        $dom->documentElement->appendChild($imp);
        return (new \DOMXPath($dom))->query($xpath, $imp);
    }

    /**
     * @param $url
     * @return array
     */
    public function preloadElements($url)
    {
        $html = new \DOMDocument();
        $html->loadHTMLFile($url, LIBXML_NOERROR);
        $results = [];
        $nodeList = $html->getElementsByTagName('table');
        foreach ($nodeList as $idx=>$nl) {
            $results[] = $this->getResult($nl, $idx);
        }
        $nodeList = $html->getElementsByTagName('ol');
        foreach ($nodeList as $idx=>$nl) {
            $results[] = $this->getResult($nl, $idx);
        }
        $nodeList = $html->getElementsByTagName('ul');
        foreach ($nodeList as $idx=>$nl) {
            $results[] = $this->getResult($nl, $idx);
        }
        return $results;
    }

    /**
     * @param \DOMNode $node
     * @param int $idx
     * @return array
     */
    protected function getResult(\DOMNode $node, int $idx)
    {
        $nodename = strtolower($node->nodeName);
        $full_name = '';
        switch ($nodename) {
            case 'table': $full_name = 'Table'; break;
            case 'ul': $full_name = 'Unordered List'; break;
            case 'ol': $full_name = 'Ordered List'; break;
        }

        $caption = $this->elXpath($node, '//caption')->item(0);
        $caption = $caption ? $caption->textContent : '';
        $caption = strlen($caption) > 20 ? substr($caption, 0, 20).'...' : $caption;

        $first_el = $this->elXpath($node, '//th | //td | //li')->item(0);
        $first_el = $first_el ? $first_el->textContent : '';
        $first_el = strlen($first_el) > 20 ? substr($first_el, 0, 20).'...' : $first_el;

        $total_num = $this->elXpath($node, '//th | //td | //li')->length;
        $rows = $this->elXpath($node, '//tr')->length ?: 1;

        $header = [];
        if ($caption) { $header[] = $caption; }
        if ($first_el) { $header[] = $first_el; }
        if ($total_num) {
            $header[] = ($rows>1 ? ($rows.'*') : '') . ($total_num/$rows);
        }

        return [
            'key' => $nodename.'_'.($idx+1),
            'val' => $full_name.' '.($idx+1).' ('.implode(', ', $header).')',
        ];
    }

    /**
     * @param $url
     * @param $query
     * @param $index
     * @param bool $all
     * @return array
     */
    public function parsePageHtml($url, $query, $index, bool $all = false)
    {
        $html = new \DOMDocument();
        $html->loadHTMLFile($url, LIBXML_NOERROR);
        if ($index && $index[0] == '#') {
            $node = $html->getElementById( substr($index,1) );
        } else {
            $nodeList = $html->getElementsByTagName($query);
            $index = $index ? $index-1 : 0;
            $node = $nodeList[$index] ?? null;
        }
        $rows = [];
        if ($node) {
            $childNodes = [];
            if ($query == 'table') {
                $rows = $this->parseTableEl($node);
            }
            if ($query == 'ol' || $query == 'ul') {
                $rows = $this->parseListEl($node);
            }
        }
        return $all ? $rows : (array_first($rows) ?: []);
    }

    /**
     * @param $url
     * @param $xpath
     * @param bool $all
     * @return array
     */
    public function parseXpathHtml($url, $xpath, bool $all = false)
    {
        $html = new \DOMDocument();
        $html->loadHTMLFile($url, LIBXML_NOERROR);
        $xpather = new \DOMXPath($html);
        $nodeList = $xpather->query($xpath);
        $rows = [];
        foreach ($nodeList as $nod) {
            $rows[] = [ $nod->textContent ];
        }
        return $all ? $rows : (array_first($rows) ?: []);
    }

    /**
     * @param $url
     * @param $xpath
     * @param bool $all
     * @return array
     */
    public function parseXmlPage($url, $xpath, bool $all = false)
    {
        $data = file_get_contents($url);
        $html = new \DOMDocument();
        $html->loadXML($data, LIBXML_NOERROR);
        $xpather = new \DOMXPath($html);
        $nodeList = $xpather->query($xpath);
        $rows = [];
        foreach ($nodeList as $nod) {
            $row = [];
            foreach ($nod->childNodes as $el) {
                if (strtolower($el->nodeName) != '#text') {
                    $row[] = $el->textContent;
                }
            }
            $rows[] = $row;
        }
        return $all ? $rows : (array_first($rows) ?: []);
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function parseTableEl(\DOMNode $node)
    {
        $rows = [];
        $trs = $this->elXpath($node, '//tr');
        foreach ($trs as $tr) {
            $row = [];
            $tds = $this->elXpath($tr, '//th | //td');
            foreach ($tds as $el) {
                $row[] = $el->textContent;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function parseListEl(\DOMNode $node)
    {
        $rows = [];
        $lis = $this->elXpath($node, '//li');
        foreach ($lis as $li) {
            $row[] = [ $li->textContent ];
        }
        return $rows;
    }
}