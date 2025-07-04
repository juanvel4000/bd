<?php
function getdb() {
    static $pdo;
    if ($pdo === null) {
        /* you might modify this */
        $pdo = new PDO('sqlite:' . __DIR__ . '/bd.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}

function db_exec($sql, $params = []) {
    $stmt = getdb()->prepare($sql);
    return $stmt->execute($params);
}


function db_row($sql, $params = []) {
    $stmt = getdb()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

function db_all($sql, $params = []) {
    $stmt = getdb()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function db_last_id() {
    return getdb()->lastInsertId();
}


db_exec("CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT, 
    name VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    bd_table TEXT DEFAULT 'none',
    delkey TEXT NOT NULL,
    body TEXT NOT NULL
);");

db_exec("CREATE TABLE IF NOT EXISTS replies (
    post_id INTEGER,
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    body TEXT NOT NULL,
    delkey TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);");
