<?php
$title = "bd";
$desc = "A simple imageboard website with no image support";
$url = "https://bd.example.com";
$theme = "default";

# modifying this might mess up with bd

return [
    "title" => $title,
    "theme" => __DIR__ . "/templates/" . $theme . ".php",
    "description" => $desc,
    "url" => $url
];


