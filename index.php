<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/Test_TextFeatures/Utilities/Modifiers/PregMatches.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Test_TextFeatures/Utilities/TextFeatures.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Test_TextFeatures/Checks/TextFeaturesTests.php');

use app\utilities\TextFeatures;
use app\utilities\validators\TextFeaturesTests;

?>
<h2>Test checks:</h2>
<?
echo(TextFeaturesTests::highlightWords()===true)?'highlightWords QC passed':'(!)highlightWords QC failed';
?>
<br /><br />
<?
echo(TextFeaturesTests::validateHTML()===true)?'validateHTML QC passed':'(!)validateHTML QC failed';
?>

<br /><br />
<h2>Examples:</h2>
<h3>highlightWords:</h3>
Source:
<br/>
<?
$abc = '<pre>
Познакомился мужик в ресторане с дамой. Пили, пили, пили, пили, пили, пили, пили. Провожает он ее и говорит:
– Давай зайдем ко мне, чаю попьем!
– Да я не могу сегодня.
– Да мы чаю попьем!
Короче, утром просыпается с бодунища.
– Так. Что я вчера делал? Пил. Бабу привел. Где она? Нет.
Смотрит на руки – в крови:
– Зарезал!?
Смотрит в зеркало – морда в крови:
– И съел!
* * *
Главная ошибка клиники в методике лечения алкоголизма состояла в том, что палаты были трёхместными.
* * *
Все мои случаи дружбы с девушками заканчивались летальным исходом…
Они все залетали…
* * *
80-е годы в ГДР один переводчик хвастался, что может перевести любую фразу. Ему предложили перевести с русского на немецкий фразу "Косил косой косой косой".
</pre>';
?>
<?=$abc?>
Result:
<br/>
<?=TextFeatures::highlightWords($abc, array('да', 'что', 'вести'), '<b style="font-size: 150%">', '</b>')?>
<br/><br/>
<h3>validateHTML:</h3>
Source:
<br/>
<?
$var1 = '<pre>
<b>example: </b>, example:
<div align="left">this is a test</div>, this is a test
<p>This is some text and here is a <strong>bold text</strong> then the post stop here....</p>
</pre>';

$var2 = '<pre>
<b>example: </b>, example:
<div align="left">this is a test
</pre>';

$var3 = '<pre>
<b>example: , example:
<div align="left"></b>this is a test</div></b>
</pre>';
?>
<?="$var1\r\n\r\n"?>
<?="$var2\r\n\r\n"?>
<?="$var3\r\n\r\n"?>
Result:
<br/><br/>
1 - <?=TextFeatures::validateHTML($var1)?'Ok':'Failed'?>
<br/>
2 - <?=TextFeatures::validateHTML($var2)?'Ok':'Failed'?>
<br/>
3 - <?=TextFeatures::validateHTML($var3)?'Ok':'Failed'?>

