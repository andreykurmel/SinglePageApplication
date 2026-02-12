<?php


namespace Vanguard\Services\Tablda;

use Illuminate\Support\Arr;

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
        try {
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
        } catch (\Exception $e) {
            return [];
        }
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
        $caption = $caption ? $this->getText($caption->textContent) : '';
        $caption = strlen($caption) > 20 ? substr($caption, 0, 20).'...' : $caption;

        $first_el = $this->elXpath($node, '//th | //td | //li')->item(0);
        $first_el = $first_el ? $this->getText($first_el->textContent) : '';
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
        return $all ? $rows : (Arr::first($rows) ?: []);
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
            $rows[] = [ $this->getText($nod->textContent) ];
        }
        return $all ? $rows : (Arr::first($rows) ?: []);
    }

    /**
     * @param array $xml_settings
     * @param bool $all
     * @return array
     */
    public function parseXmlPage(array $xml_settings, bool $all = false)
    {
        $path = !empty($xml_settings['web_xml_file'])
            ? storage_path('app/tmp_import/'.$xml_settings['web_xml_file'])
            : $xml_settings['web_url'] ?? '';
        $data = file_get_contents($path);

        $html = new \DOMDocument();
        $html->loadXML($data, LIBXML_NOERROR);

        $web_xpath = $xml_settings['web_xpath'] ?? '';
        $web_xpath = '//' . preg_replace('/^[\/]+/i', '', $web_xpath);
        $xpather = new \DOMXPath($html);
        $nodeList = $xpather->query($web_xpath);

        $attributes = !empty($xml_settings['web_scrap_xpath_query']) ? $this->attributesArray($nodeList) : [];

        return $all
            ? $this->allNodes($nodeList, $attributes, $xml_settings)
            : $this->nodesHeader($nodeList[0] ?? null, $attributes, $xml_settings);
    }

    /**
     * @param \DOMNodeList $nodeList
     * @param array $attributes
     * @param array $xml_settings
     * @return array
     */
    protected function allNodes(\DOMNodeList $nodeList, array $attributes = [], array $xml_settings = [])
    {
        $rows = [];
        foreach ($nodeList as $nod) {
            $row = [];
            foreach ($attributes as $attr) {
                $row[] = $nod->getAttribute($attr);
            }
            $left_lvl = $xml_settings['web_xml_nested'] ?? 0;
            $rows[] = array_merge($row, $this->nestedNode($nod, '', $left_lvl));
        }
        return $rows;
    }

    /**
     * @param \DOMNode|null $node
     * @param array $attributes
     * @param array $xml_settings
     * @return array
     */
    protected function nodesHeader(\DOMNode $node = null, array $attributes = [], array $xml_settings = [])
    {
        $row = [];
        foreach ($attributes as $attr) {
            $row[] = $node->nodeName.','.$attr;
        }
        $left_lvl = $xml_settings['web_xml_nested'] ?? 0;
        return array_merge($row, $this->nestedNode($node, $node->nodeName . ',', $left_lvl));
    }

    /**
     * @param \DOMNode $node
     * @param string $pre_header
     * @param int $left_levels
     * @param array $name_idx
     * @param string $lvl_prefix
     * @return array
     */
    protected function nestedNode(\DOMNode $node, string $pre_header = '', int $left_levels = 0, array $name_idx = [], string $lvl_prefix = '')
    {
        if (!$name_idx) {
            $name_idx = [ 'items' => [], 'prefix_index' => 0, 'hist' => [] ];
        }

        $row = [];
        foreach ($this->getChildNodes($node) as $el)
        {
            if ($left_levels && $this->getChildNodes($el)) {

                $next_prefix = $lvl_prefix . $el->nodeName . (++$name_idx['prefix_index']) . '/';
                $name_idx['hist'][] = $next_prefix;
                $next_header = $pre_header ? ($pre_header . $el->nodeName . ',') : '';
                $nest_row = $this->nestedNode($el, $next_header, $left_levels-1, $name_idx, $next_prefix);
                $row = array_merge($row, $nest_row);

            } else {

                $key = $lvl_prefix . $el->nodeName;
                if (isset($name_idx['items'][$key])) {
                    $ii = $name_idx['items'][$key];
                    $row[$ii] = $pre_header
                        ? $pre_header . $el->nodeName
                        : $row[$ii] . ', ' . $this->getText($el->textContent);
                } else {
                    $name_idx['items'][$key] = count($row);
                    $row[] = $pre_header
                        ? $pre_header . $el->nodeName
                        : $this->getText($el->textContent);
                }

            }
        }
        return $row;
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function getChildNodes(\DOMNode $node)
    {
        $nodes = [];
        foreach ($node->childNodes as $child) {
            if (strtolower($child->nodeName) != '#text') {
                $nodes[] = $child;
            }
        }
        return $nodes;
    }

    /**
     * @param \DOMNodeList $list
     * @return array
     */
    protected function attributesArray(\DOMNodeList $list)
    {
        $attrs = [];
        foreach ($list as $el) {
            if ($el->attributes && count($attrs) < count($el->attributes)) {
                $attrs = [];
                foreach ($el->attributes as $attribute) {
                    $attrs[] = $attribute->name;
                }
            }
        }
        return $attrs;
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
                $row[] = $this->getText($el->textContent);
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
            $row[] = [ $this->getText($li->textContent) ];
        }
        return $rows;
    }

    /**
     * @param $text
     * @return false|mixed|string
     */
    protected function getText($text)
    {
        return json_encode($text);
    }
}