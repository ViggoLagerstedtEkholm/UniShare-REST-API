<?php
$html = file_get_contents('app/views/post/add/post.html');
echo str_replace('---forumID---', $forumID , $html);