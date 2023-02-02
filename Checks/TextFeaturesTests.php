<?php
/*
By Alex Kor.
*/
namespace app\utilities\validators;

use app\utilities\TextFeatures;

class TextFeaturesTests {
    public static function validateHTML(){
        $testCasesTrue = array();
        $testCasesTrue[] = '
            Пользователь в сообщениях может использовать только следующие HTML теги и только с такими атрибутами:
            <a href="1" title="2"> 3</a>
            <code>4 </code>
            <br />
            <i>5 </i>
            <strike> 6</strike>
            <br />
            <strong> 7 </strong>
        ';

        $testCasesTrue[] = '<i>This is some text and here is a <strong>bold text</strong>
         <img src="12345" />
         then the post stop here....</i>';
        $testCasesTrue[] = '<a href="1" title="2">sdfsdfsdf
        <hr/>
        <strong>dghdhdgh</strong>
         <hr />
         fdsgfgdfgfd</a>';
        $testCasesTrue[] = '<i><a>A</a><a href="1" title="2">B</a></i>';


        $testCasesFalse = array();
        $testCasesFalse[] = '
            Пользователь в сообщениях может использовать только следующие HTML теги и только с такими атрибутами:
            <i><a href="1" title="2"> 3</a>
            <code>4 </code>
            <br/>
            <i>5 </i>
            <strike> 6</strike>
            <br>
            <strong> 7 </strong>
        ';
        $testCasesFalse[] = '<i>This is some text and here is a <strong>bold text then the post stop here....</i></strong>';
        $testCasesFalse[] = '<a href="1" title="2" target="_blank">sdfsdfsdf</strong>dghdhdgh<strong> fdsgfgdfgfd</a>';
        $testCasesFalse[] = '<i><a><a href="1" title="2">A</a>B</a></i></i>';

        foreach($testCasesTrue as $gTC){
            $f_res = TextFeatures::validateHTML($gTC);
            if(!$f_res) {
                //print_r("$gTC\r\n");
                return false;
            }
        }

        foreach($testCasesFalse as $bTC){
            $f_res = TextFeatures::validateHTML($bTC);
            if($f_res) {
                //print_r("$bTC\r\n");
                return false;
            }
        }

        return true;
    }


    public static function highlightWords(){
        $testCases = array();

        $testCases[] = array(
            'src' => 'Необходимо выделить вхождения каждого из слов с помощью квадратных скобок (вася заменить на [Вася]'
        ,'res' => 'Необходимо выделить вхождения каждого из слов с помощью квадратных скобок ([вася] заменить на [Вася]'
        );

        $testCases[] = array(
            'src' => 'Необходимо выделить вхождения каждого из слов с помощью квадратных скобок (Васян заменить на ваСя]'
        ,'res' => 'Необходимо выделить вхождения каждого из слов с помощью квадратных скобок (Васян заменить на [ваСя]]'
        );

        $testCases[] = array(
            'src' => 'Например, есть строка «Мама мыла раму» и массив «ама», «раму». '
        ,'res' => 'Например, есть строка «Мама мыла [раму]» и массив «[ама]», «[раму]». '
        );

        $testCases[] = array(
            'src' => 'В результате должно получиться «Мама и Вася мыла Раму», а не «М[ама] мыла [раму]»'
        ,'res' => 'В результате должно получиться «Мама и [Вася] мыла [Раму]», а не «М[ама] мыла [раму]»'
        );


        $arr = array('вася', 'ама', 'раму');

        foreach($testCases as $tC){
            $f_res = TextFeatures::highlightWords($tC['src'], $arr);
            if ($f_res !== $tC['res']){
                //print_r("$f_res\r\n");
                return false;
            }
        }

        return true;
    }
}