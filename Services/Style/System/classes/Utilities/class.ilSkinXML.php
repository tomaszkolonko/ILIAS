<?php
include_once("Services/Style/System/classes/Exceptions/class.ilSystemStyleException.php");
include_once("Services/Style/System/classes/Utilities/class.ilSkinStyleXML.php");

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSkinXML implements \Iterator, \Countable{

    /**
     * @var string
     */
    protected $id = "";
    /**
     * @var string
     */
    protected $name = "";

    /**
     * @var ilSkinStyleXML[]
     */
    protected $styles = array();


    /**
     * ilSkinXML constructor.
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->setId($id);
        $this->setName($name);
    }

    /**
     * @param string $path
     * @return ilSkinXML
     */
    public static function parseFromXML($path = ""){
        global $DIC;

        $DIC->logger()->root()->critical($path);

        try{
            $xml = new SimpleXMLElement(file_get_contents($path));
        }catch(Exception $e){
            throw new ilSystemStyleException(ilSystemStyleException::FILE_OPENING_FAILED, $path);
        }

        $id = basename (dirname($path));
        $skin = new self($id,(string)$xml->attributes()["name"]);

        /**
         * @var ilSkinStyleXML $last_style
         */
        $last_style = null;


        foreach($xml->children() as $style_xml){
            $style = ilSkinStyleXML::parseFromXMLElement($style_xml);

            if($style_xml->getName() == "substyle") {
                if(!$last_style){
                    throw new ilSystemStyleException(ilSystemStyleException::NO_PARENT_STYLE, $style->getId());
                }
                $style->setSubstyleOf($last_style->getId());
            }
            $skin->addStyle($style);
            $last_style = $style;

        }
        return $skin;
    }

    public function asXML(){
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><template/>');
        $xml->addAttribute("xmlns","http://www.w3.org");
        $xml->addAttribute("version","1");
        $xml->addAttribute("name",$this->getName());

        $last_style = null;

        foreach($this->getStyles() as $style){
            if(!$style->isSubstyle()){
                $this->addChildToXML($xml, $style);

                foreach($this->getSubstylesOfStyle($style->getId()) as $substyle){
                    $this->addChildToXML($xml, $substyle);
                }
            }
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        return $dom->saveXML();
    }

    protected function addChildToXML(SimpleXMLElement $xml,ilSkinStyleXML $style){
        $xml_style = null;
        if($style->isSubstyle()){
            $xml_style = $xml->addChild("substyle");
        }
        else{
            $xml_style = $xml->addChild("style");
        }
        $xml_style->addAttribute("id", $style->getId());
        $xml_style->addAttribute("name", $style->getName());
        $xml_style->addAttribute("image_directory", $style->getImageDirectory());
        $xml_style->addAttribute("css_file", $style->getCssFile());
        $xml_style->addAttribute("sound_directory", $style->getSoundDirectory());
        $xml_style->addAttribute("font_directory", $style->getFontDirectory());
    }

    public function writeToXMLFile($path){
        file_put_contents($path, $this->asXML());
    }
    /**
     * @param ilSkinStyleXML $style
     */
    public function addStyle(ilSkinStyleXML $style){
        try{
            $this->styles[$style->getId()] = $style;
        }catch(Exception $e){
            //Todo
        }
    }

    /**
     * @param string $id
     */
    public function removeStyle($id){
        unset($this->styles[$id]);
    }

    /**
     * @param $id
     * @return ilSkinStyleXML
     */
    public function getStyle($id){
        return $this->styles[$id];
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasStyle($id){
        return array_key_exists($id,$this->getStyles());
    }

    /**
     * @param $id
     * @return ilSkinStyleXML
     */
    public function getDefaultStyle(){
        //Todo there might be a better option than this to select the default style
        return array_values($this->styles)[0];
    }

    /**
     * Iterator implementations
     *
     * @return bool
     */
    public function valid() {
        return current($this->styles) !== false;
    }

    /**
     * @return	mixed
     */
    public function key() {
        return key($this->styles);
    }

    /**
     * @return	mixed
     */
    public function current() {
        return current($this->styles);
    }

    public function next() {
        next($this->styles);
    }
    public function rewind() {
        reset($this->styles);
    }

    /**
     * Countable implementations
     */
    public function count(){
        return count($this->styles);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @throws ilSystemStyleException
     */
    public function setId($id)
    {
        if (strpos($id, ' ') !== false) {
            throw new ilSystemStyleException(ilSystemStyleException::INVALID_CHARACTERS_IN_ID, $id);
        }
        $this->id = str_replace(" ","_",$id);
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return ilSkinStyleXML[]
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * @param ilSkinStyleXML[] $styles
     */
    public function setStyles($styles)
    {
        $this->styles = $styles;
    }

    /**
     * @param $style_id
     * @return array
     */
    public function getSubstylesOfStyle($style_id){
        $substyles = array();

        if($this->getStyle($style_id)){
            foreach($this->getStyles() as $style){
                if($style->getId() != $style_id && $style->isSubstyle()){
                    if($style->getSubstyleOf() == $style_id){
                        $substyles[$style->getId()] = $style;
                    }
                }
            }
        }
        return $substyles;
    }

    public function hasStyleSubstyles($style_id){
        if($this->getStyle($style_id)){
            foreach($this->getStyles() as $style){
                if($style->getId() != $style_id && $style->isSubstyle()){
                    if($style->getSubstyleOf() == $style_id){
                        return true;
                    }
                }
            }
        }
        return false;

    }

    /**
     * @return bool
     */
    public function hasStyles(){
        return count($this->getStyles()) > 0;
    }
}