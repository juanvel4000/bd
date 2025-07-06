<?php
$config = require 'core.php';
require_once 'database.php';

function createPost($title, $body, $delkey, $table) {
    $hashk = password_hash($delkey, PASSWORD_BCRYPT);
    db_exec("INSERT INTO posts (name, body, delkey, bd_table) VALUES (?, ?, ?, ?)", [$title, $body, $hashk, $table]);
    return db_last_id();
}
function getTableInfo($table) {
    global $config;
    return $config['tableinfo'][$table];
}
function exists_in_table($id, $table) {
    return (bool) db_row("SELECT 1 FROM posts WHERE id = ? AND bd_table = ?", [$id, $table]);
}
function render_template($body = "<h1>test</h1>", $bar = "<a href='/'>/</a>") {
    ob_start();
    global $config;
    $navbar = "<nav><a href='/'>Home</a>";
    foreach ($config['tables'] as $table) {
        $navbar = $navbar . " | <a href='/$table/'>/$table/</a>";
    }
    $navbar = $navbar . "</nav>";
    $title = $config['title'];
    $desc = $config['description'];
    include $config['theme']; 
    return ob_get_clean();
}
function deletePost($id, $delkey) {
    $row = db_row("SELECT delkey FROM posts WHERE id = ?", [$id]);
    if (!$row) return false;

    if (password_verify($delkey, $row['delkey'])) {
        db_exec("DELETE FROM posts WHERE id = ?", [$id]);
        db_exec("DELETE FROM replies WHERE post_id = ?", [$id]);
        return true;
    }

    return false;
}
function deleteComment($id, $delkey, $expectedtable) {
    $row = db_row("SELECT delkey, post_id, table FROM replies WHERE id = ?", [$id]);
    if (!$row) return false;
    if (!exists_in_table($row['post_id'], $expectedtable)) {
        return false;
    }
    if (password_verify($delkey, $row['delkey'])) {
        db_exec("DELETE FROM replies WHERE id = ?", [$id]);
        return true;
    }

    return false;
}
function comment($post_id, $body, $delkey) {
    $hashk = password_hash($delkey, PASSWORD_BCRYPT);
    db_exec("INSERT INTO replies (post_id, body, delkey) VALUES (?, ?, ?)", [$post_id, $body, $hashk]);
    return db_last_id();
}

function post_exists($id) {
    return (bool) db_row("SELECT 1 FROM posts WHERE id = ?", [$id]);
}

