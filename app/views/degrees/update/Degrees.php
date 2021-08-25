<?php
$html = file_get_contents('app/views/degrees/update/Degrees.html');
echo str_replace('---ID---', $degreeID, $html);
