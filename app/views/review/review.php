<?php
$html = file_get_contents('app/views/review/review.html');
$html = str_replace('---ID---', $_GET["ID"], $html);
echo $html;
