<?php
/*
By Alex Kor.
*/
namespace app\utilities;

use app\utilities\modifiers\PregMatchGroup;
use app\utilities\modifiers\TagDescriber;

class TextFeatures {
    public static function validateHTML($text){
        $_SINGLETONS = array('meta', 'img', 'br', 'hr', 'input');

        $TagList = array();

        preg_match_all('#<\b([a-z]+)(?: .*)?(?<![/|/ ])[ ]{0,1}[/]{0,1}>#ui', $text, $result, PREG_OFFSET_CAPTURE);
        $openedTagsFull = $result[0];
        $openedTagsShort = $result[1];
        //var_export($openedTagsFull);

        preg_match_all('#</([a-z]+)>#ui', $text, $result, PREG_OFFSET_CAPTURE);
        $closedTagsFull = $result[0];
        $closedTagsShort = $result[1];
        //var_export($closedTagsShort);


        $len_opened = count($openedTagsShort);

        for($i=0; $i<$len_opened; $i++){
            $openInfo = new PregMatchGroup($openedTagsShort[$i][0], $openedTagsShort[$i][1]);
            $closingTagIndex = self::getClosingTagInfoIndex($openInfo, $closedTagsShort);

            if($closingTagIndex === null && !in_array(mb_strtolower($openInfo->getMatch()), $_SINGLETONS)) {
                /*
                 * Closing part for non-single tag is not found
                 * Не найден закрывающий фрагмент для НЕ-одиночного тега
                 */
                return false;
            } else {
                /*
                 * TODO: check allowed tag attributes
                 checkAllowedTagAttributes($openInfo);
                 */
                $closedTagsShort[$closingTagIndex] = null;
                $current = new TagDescriber(
                    new PregMatchGroup($openedTagsFull[$i][0], $openedTagsFull[$i][1])
                    , $closingTagIndex!==null?new PregMatchGroup($closedTagsFull[$closingTagIndex][0], $closedTagsFull[$closingTagIndex][1]):null
                    , $openInfo->getMatch()
                );

                if($current->getCloseTagInfo()){
                    $error = self::getCrossedExisting($current, $TagList);
                    if($error){
                        /*
                         * Incapsulation error
                         * Нарушена вложенность для НЕ-одиночного тега
                         */
                        return false;
                    }
                }

                $TagList[] = $current;
            }
        }

        if(count(array_filter($closedTagsShort))){
            /*
             * More closing parts than opening are available
             * Закрывающих фрагментов больше, чем открывающих
             */
            return false;
        }
        //var_export($TagList);
        return true;
    }


    /**
     * @param $openedTagInfo PregMatchGroup
     * @param $closedTags
     * @return PregMatchGroup|null
     */
    private static function getClosingTagInfoIndex($openedTagInfo, &$closedTags){
        $len_closed = count($closedTags);
        $result = null;
        $candidate = null;

        for($i=0; $i<$len_closed; $i++){
            if(!isset($closedTags[$i]) || $closedTags[$i] === null) continue;
            $closedInfo = new PregMatchGroup($closedTags[$i][0], $closedTags[$i][1]);
            if($openedTagInfo->getMatch() === $closedInfo->getMatch()){
                if($candidate === null){
                    // Находим первого кандидата на закрытие
                    if($closedInfo->getStartByteIndex() > $openedTagInfo->getStartByteIndex()) {
                        $candidate = $closedInfo;
                        $result = $i;
                    }
                } else {
                    // Выбираем ближайший закрывающий тег
                    /**
                     * @var $result PregMatchGroup
                     */
                    if($candidate->getStartByteIndex() > $closedInfo->getStartByteIndex()){
                        $candidate = $closedInfo;
                        $result = $i;
                    }
                }
            }
        }

        return $result;
    }

    /** incapsulation check - not [ < ] >  or   < [ > ]
     * @param $currTag  TagDescriber
     * @param $previousTagsArr TagDescriber[]
     * @return null
     */
    private static function getCrossedExisting($currTag, $previousTagsArr){
        $len = count($previousTagsArr);
        for($i=0; $i<$len; $i++){
           if($previousTagsArr[$i]->getCloseTagInfo() === null) continue;
           if(
               $currTag->getOpenTagInfo()->getStartByteIndex() < $previousTagsArr[$i]->getOpenTagInfo()->getStartByteIndex()
               && $currTag->getCloseTagInfo()->getStartByteIndex() > $previousTagsArr[$i]->getOpenTagInfo()->getStartByteIndex()
               && $currTag->getCloseTagInfo()->getStartByteIndex() < $previousTagsArr[$i]->getCloseTagInfo()->getStartByteIndex()
               ||
               $currTag->getOpenTagInfo()->getStartByteIndex() > $previousTagsArr[$i]->getOpenTagInfo()->getStartByteIndex()
               && $currTag->getOpenTagInfo()->getStartByteIndex() < $previousTagsArr[$i]->getCloseTagInfo()->getStartByteIndex()
               && $currTag->getCloseTagInfo()->getStartByteIndex() > $previousTagsArr[$i]->getCloseTagInfo()->getStartByteIndex()
           ) return $previousTagsArr[$i];
        }

        return null;
    }

    private static function checkAllowedTagAttributes($tag){
        /*
         TODO: create attrs-to-tags rules
         */
        preg_filter("/(\w[-\w]*)=\"(.*?)\"/", '$1', $tag);
    }








    public static function highlightWords($text, $array_of_words, $open = '[', $close = ']'){
        $openBtLen = strlen($open);
        $closeBtLen = strlen($close);

        foreach($array_of_words as $word){
            $accumulated_offset = 0;
            $mask = '#[^a-zа-я0-9]+('.$word.')[^a-zа-я0-9]+#ui';
            preg_match_all($mask, $text, $result, PREG_OFFSET_CAPTURE);
            $cleanMatches = $result[1];

            usort($cleanMatches, function($a, $b)
            {
                if ($a[1] == $b[1]) {
                    return 0;
                }
                return ($a[1] < $b[1]) ? -1 : 1;
            });

            foreach($cleanMatches as $cM){
                $found = new PregMatchGroup($cM[0],$cM[1]);

                $checkLeftMarker = mb_strcut($text, $found->getStartByteIndex()+$accumulated_offset-$openBtLen, $openBtLen) === $open;
                $checkRightMarker = mb_strcut($text, $found->getEndByteIndex()+$accumulated_offset+1, $closeBtLen) === $close;

                if(!($checkLeftMarker || $checkRightMarker)){
                    $result = mb_strcut($text, 0, $found->getStartByteIndex()+$accumulated_offset);
                    $result .= $open.$found->getMatch().$close;
                    $result .= mb_strcut($text, $found->getEndByteIndex()+$accumulated_offset);
                    $text = $result;
                    //break;
                    $accumulated_offset += ($openBtLen+$closeBtLen);
                }
            }
        }

        return $text;
    }
}