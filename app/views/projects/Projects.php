<?php
$html = file_get_contents('app/views/projects/projects.html');

$panel = str_replace('---name---', $project["name"], $html);
$panel = str_replace('---link---', $project["link"], $panel);
$panel = str_replace('---description---', $project["description"], $panel);

echo $panel;
