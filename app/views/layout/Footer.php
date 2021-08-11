<?php
$html = file_get_contents('app/views/layout/Footer.html');
if($isError == 0){
echo $html;
}
