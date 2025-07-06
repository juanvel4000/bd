<?php
$title = "bd";
$desc = "A simple imageboard website with no image support";
$url = "https://bd.example.com";
$theme = "default";
$tables = ["bd", "test"]; # list of allowed tables
$tableinfo = [ /* modify this according to your tables */
        "bd" => "The main table",
        "test" => "A testing table"
    ];
# modifying this might mess up with bd
if (file_exists('version.php')) {
    require_once "version.php";
} else {
    $version = "v0.1";
}
return [
    "title" => $title,
    "theme" => __DIR__ . "/templates/" . $theme . ".php",
    "description" => $desc,
    "url" => $url,
    "tables" => $tables,
    "version" => $version,
    "tableinfo" => $tableinfo
];
