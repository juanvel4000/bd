<?php
$config = require 'core.php';
require_once 'helpers.php';
require_once 'database.php';


if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $fullPath = __DIR__ . $path;

    if (is_file($fullPath)) {
        return false;
    }
}
# Special thanks to Bram(us) Van Damme for the router code
require_once './include/Router.php';
use Bramus\Router\Router;
$router = new Router();

$router->post("/bd-internal/{table}/post", function ($table) {
    global $config;
    $delkey = $_POST['delkey'] ?? '';
    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';

    header("Content-Type: application/json");
    if (!in_array($table, $config['tables'])) {
        echo json_encode(["error" => true, "message" => "table $table is not a valid table"]);
        return false;
    }
    if (in_array('', [$delkey, $title, $body])) {
        echo json_encode(["error" => true, "message" => "either delkey, title or body is empty"]);
        return false;
    }
    if (!createPost($title, $body, $delkey, $table)) {
        echo json_encode(["error" => true, "message" => "error creating post"]);
        return false;
    } else {
        $id = db_last_id();
        echo json_encode(["error" => false, "message" => "successfully created post with id $id"]);
        return true;
    }
});
$router->post("/bd-internal/{table}/{id}/comment", function ($table, $id) {
    global $config;
    $delkey = $_POST['delkey'] ?? '';
    $body = $_POST['body'] ?? '';

    header("Content-Type: application/json");
    if (!in_array($table, $config['tables'])) {
        echo json_encode(["error" => true, "message" => "table $table is not a valid table"]);
        return false;
    }
    if (!post_exists($id)) {
        echo json_encode(["error" => true, "message" => "post does not exist"]);
        return false;
    }
    if (in_array('', [$delkey, $body])) {
        echo json_encode(["error" => true, "message" => "either delkey or body is empty"]);
        return false;
    }
    if (!comment($id, $body, $delkey)) {
        echo json_encode(["error" => true, "message" => "error creating comment"]);
        return false;
    } else {
        $id = db_last_id();
        echo json_encode(["error" => false, "message" => "successfully created comment with id $id"]);
        return true;
    }
});
$router->get("/{table}/{id}/json", function ($table, $id) {
    header("Content-Type: application/json");
    if (!post_exists($id)) {
        echo json_encode(["error" => true, "message" => "post does not exist"]);
        return false;
    }
    $ro = db_row("SELECT * FROM posts WHERE bd_table = ? AND id = ?", [$table, $id]);
    $post = [
        "id" => $ro['id'],
        "title" => $ro['name'],
        "body" => $ro['body']
    ];
    echo json_encode($post);
    return true;
});
$router->get("/{table}/{id}/no-comments", function ($table, $id) {
    $ro = db_row("SELECT * FROM posts WHERE bd_table = ? AND id = ?", [$table, $id]);
    if (!$ro) {
        $body = "<div><h1>Error</h1><br><p>Requested post does not exist</p>";
    } else {
        $post = [
            "id" => $ro['id'],
            "timestamp" => $ro['timestamp'],
            "title" => $ro['name'],
            "body" => $ro['body']
        ];
        $body = "
            <div class='post' id='".$post['id']."'>
                <div class='post-meta'>
                  <time datetime='".$post['timestamp']."'>".$post['timestamp']."</time>
                  <span class='post-id'>#".$post['id']."</span>
</div>
                <h1>".$post['title']."</h1>
                <p>".htmlspecialchars($post['body'])."</p>
            </div>";
    }
    echo render_template($body);
});
$router->get("/{table}/{id}", function ($table, $id) {
    $ro = db_row("SELECT * FROM posts WHERE bd_table = ? AND id = ?", [$table, $id]);
    if (!$ro) {
        $body = "<div><h1>Error</h1><br><p>Requested post does not exist</p>";
    } else {
        $post = [
            "id" => $ro['id'],
            "timestamp" => $ro['timestamp'],
            "title" => $ro['name'],
            "body" => $ro['body']
        ];
        $body = "
            <div class='cmt' id='".$post['id']."'>
                <div class='cmt-meta'>
                    <time datetime='".$post['timestamp']."'>".$post['timestamp']."</time>
                    <span class='post-id'>#".$post['id']."</span>
                    
                </div>
                <h1>".$post['title']."</h1>
                <p>".htmlspecialchars($post['body'])."</p>
            </div>";
        $cmts = db_all("SELECT * FROM replies WHERE post_id = ? ORDER BY timestamp DESC", [$post['id']]);
        foreach ($cmts as $cmt) {
            $body = $body . "
                <hr>
                <div class='post' id='comment-".$cmt['id']."'>
                    <div class='post-meta'>
                        <time datetime='".$cmt['timestamp']."'>".$cmt['timestamp']."</time>
                        <span class='cmt-id'>#c".$cmt['id']."</span>
                        Comment
                    </div>
                    <p>".htmlspecialchars($cmt['body'])."</p>
                </div>";
        }
    }
    echo render_template($body);
});
$router->run();

