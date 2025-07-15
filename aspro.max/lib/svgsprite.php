<?
namespace Aspro\Max;

use CMax as Solution;
use \Bitrix\Main\IO;

class SvgSprite{
    const SVG_NS = 'http://www.w3.org/2000/svg';
    
    public $maxHeight = 0;
    public $maxWidth = 0;
    public $viewBox = 0;
    public $imgName = '';
    public $filePath = '';
    public $fullFilePath = '';

    public static string $uploadPath = '/upload/'.Solution::partnerName.".".Solution::solutionName.'/sprite_svg/';

    public static function getFullPath($path) {
        if (strpos($path, '://') === false) {
            $path = $_SERVER['DOCUMENT_ROOT'].$path;
        }

        return $path;
    }

    public function __construct()
    {
        $this->out = new \DOMDocument();
        $this->out->formatOutput = true;
        $root = $this->out->createElementNS(self::SVG_NS, 'svg');
        $this->out->appendChild($root);
        $root->setAttribute('xmlns', self::SVG_NS);
    }

    public function checkFile($path)
    {
        return \Bitrix\Main\IO\File::isFileExists($path);
    }

    public function add($filename)
    {
        if (!$filename) return;

        $in = new \DOMDocument();
        $in->load($filename);
        $src = $in->documentElement;

        if (!$src) {
            $in->loadXML(IO\File::getFileContents($filename));
            $src = $in->documentElement;
        }
        if (!$src) return;

        $this->imgName = basename($filename);
        $this->filePath = self::$uploadPath.$this->imgName;
        $this->fullFilePath = static::getFullPath(self::$uploadPath.$this->imgName);

        $this->maxWidth = $src->getAttribute('width');
        $this->maxHeight = $src->getAttribute('height');

        $this->viewBox = $src->getAttribute('viewBox');
        if ($this->maxHeight && $this->maxWidth) {
            $this->viewBox = '0 0 '.(float)$this->maxWidth.' '.(float)$this->maxHeight;
        }
        if (!$this->maxHeight && !$this->maxWidth && $this->viewBox) {
            $sizes = explode(' ', $this->viewBox);
            $this->maxWidth = $sizes[2];
            $this->maxHeight = $sizes[3];
        }

        if ($this->checkFile($this->fullFilePath)) return;

        foreach ($in->getElementsByTagName('path') as $element) {
            foreach ($element->attributes as $name => $attrNode) {
                $value = $attrNode->nodeValue;
                if ($name === 'class') {
                    $styles = $this->getStyles($in->textContent, $value);
                    $element->setAttribute('style', $styles);
                    $element->setAttribute($name, '');
                } elseif ($name === 'fill' || $name === 'stroke') {
                    $element->setAttribute($name, '');
                }
            }

            $style = $element->getAttribute('style');
            $style = preg_replace('/fill:#?[0-9A-Fa-f]+;/', '', $style);
            $style = preg_replace('/stroke:#?[0-9A-Fa-f]+;/', '', $style);
            $element->setAttribute('style', $style);
        }

        $g = $this->out->createElementNS(self::SVG_NS, 'g');
        $this->out->documentElement->appendChild($g);

        $g->setAttribute('id', 'svg');

        foreach ($src->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE
                && $child->tagName !== 'metadata'
                && $child->tagName !== 'defs'
            ) {
                $g->appendChild($this->out->importNode($child, true));
            }
        }
    }

    public function getStyles($string, $start)
    {
        $style = '';
        preg_match_all("/\.$start.*?\{(.*?)\}/sm", $string, $matches);
        if (is_array($matches) && count($matches) > 1) {
            $style = array_reduce($matches[1], function($acc, $item){
                $arProps = array_filter(explode(';', $item), function($value){
                    return strpos($value, 'fill') === false && strpos($value, 'stroke') === false;
                });
                if ($arProps) {
                    $acc .= trim(implode(';', $arProps));
                }
                return $acc;
            }, '');
        }
        return $style;
    }

    public function output()
    {
        $root = $this->out->documentElement;
        if ($this->maxHeight) {
            $root->setAttribute('height', $this->maxHeight.'px');
        }
        if ($this->maxWidth) {
            $root->setAttribute('width',  $this->maxWidth.'px');
        }
        if ($this->viewBox) {
            $root->setAttribute('viewBox', $this->viewBox);
        }
        $this->out->normalizeDocument();

        return $this->out->saveXML();
    }

    public function save()
    {
        // $dir = $_SERVER['DOCUMENT_ROOT'].self::$uploadPath;
        // \Bitrix\Main\IO\Directory::createDirectory($dir);
        // if (!$this->checkFile($dir.$this->imgName)) {
        if (!$this->fullFilePath) return;
        
        if (!$this->checkFile($this->fullFilePath)) {
            IO\File::putFileContents($this->fullFilePath, $this->output());
        }
    }
}?>