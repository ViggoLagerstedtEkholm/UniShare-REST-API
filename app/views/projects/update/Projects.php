<?php
$html = file_get_contents('app/views/projects/update/projects.html');
echo str_replace('---ID---', $projectID, $html);
