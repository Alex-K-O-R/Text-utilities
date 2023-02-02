<?php
/*
By Alex Kor.
*/
namespace app\utilities\modifiers;


class PregMatchGroup {
    private $match;
    private $index;

    function __construct($match, $index)
    {
        $this->index = $index;
        $this->match = $match;
    }

    public function getStartByteIndex()
    {
        return $this->index;
    }

    public function getEndByteIndex()
    {
        return $this->index+strlen($this->getMatch());
    }

    public function getMatch()
    {
        return $this->match;
    }
}

class TagDescriber {
    /**
     * @var $openTagInfo PregMatchGroup
     * @var $closeTagInfo PregMatchGroup
     */
    private $openTagInfo;
    private $closeTagInfo;
    private $tagId;

    /**
     * @param PregMatchGroup $openTagInfo
     * @param null|PregMatchGroup $closeTagInfo
     * @param null $tagId
     */
    function __construct($openTagInfo, $closeTagInfo = null, $tagId = null)
    {
        $this->openTagInfo = $openTagInfo;
        if($closeTagInfo){
            $this->closeTagInfo = $closeTagInfo;
        }
        if(!$tagId){
            /**
             * TODO: extract from tagInfo
             */
        } else $this->tagId = $tagId;
    }

    public function getOpenTagInfo()
    {
        return $this->openTagInfo;
    }

    public function getCloseTagInfo()
    {
        return $this->closeTagInfo;
    }

    public function getTotalBytesSpan(){
        if($this->closeTagInfo){
            return $this->closeTagInfo->getEndByteIndex() - $this->openTagInfo->getStartByteIndex();
        } else {
            return strlen($this->openTagInfo->getMatch());
        }
    }
}