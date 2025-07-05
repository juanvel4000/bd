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

$router->get("/", function () {
    global $config;
    $body = "
            <p>Welcome to <b>bd</b></p>
            <h2>Tables</h2>";
    foreach ($config['tables'] as $table) {
        $body = $body . "<a href='/".$table."' style='text-decoration: none; color: black'><h4>/". $table ."/: ". getTableInfo($table) ."</h4></a>";
    }
    echo render_template($body);
    return;
});
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
        if ($_POST['go-to-post'] == "1") {
            header("Location: /". $table . "/" . $id);
            return true;
        }
        echo json_encode(["error" => false, "message" => "successfully created post with id $id"]);

        return true;
    }
});
$router->post("/bd-internal/{table}/{id}/delete-post", function ($table, $id) {
    global $config;
    $delkey = $_POST['delkey'] ?? '';
    header("Content-Type: application/json");
    if (!in_array($table, $config['tables'])) {
        echo json_encode(["error" => true, "message" => "table $table is not a valid table"]);
        return false;
    }
    if (!deletePost($id, $delkey)) {
        echo json_encode(["error" => true, "message" => "failed to delete $id, invalid delkey"]);
        return false;
    } else {
        if ($_POST['go-to-table'] == "1") {
            header("Location: /$table");
            return true;
        }
        echo json_encode(["error" => false, "message" => "successfully deleted $id"]);
        return true;
    }
});
$router->post("/bd-internal/{table}/{id}/delete-cmt", function ($table, $id) {
    global $config;
    $delkey = $_POST['delkey'] ?? '';
    header("Content-Type: application/json");
    if (!in_array($table, $config['tables'])) {
        echo json_encode(["error" => true, "message" => "table $table is not a valid table"]);
        return false;
    }
    if (!deleteComment($id, $delkey)) {
        echo json_encode(["error" => true, "message" => "failed to delete $id, invalid delkey"]);
        return false;
    } else {
        if ($_POST['go-to-table'] == "1") {
            header("Location: /$table");
            return true;
        }
        echo json_encode(["error" => false, "message" => "successfully deleted $id"]);
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
        if ($_POST['return-to-post'] == "1") {
            header("Location: /". $table ."/". $id, true, 303);
            return true;
        }
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
                <h1>". htmlspecialchars($post['title']) ."</h1>
                <p>".htmlspecialchars($post['body'])."</p>
            </div>";
    }
    echo render_template($body, "<a href='/'>/</a> >> <a href='/$table/'>/$table/</a> >> <a href='/$table/$id/no-comments'>#$id@nc</a>");
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
            <div class='post' id='".$post['id']."'>
                <div class='post-meta'>
                    <time datetime='".$post['timestamp']."'>".$post['timestamp']."</time>
                    <span class='post-id'>#".$post['id']."</span>
                    <form action='/bd-internal/".$table."/".$post['id']."/delete-post' method='POST'>
                        <input type='password'  name='delkey' placeholder='Deletion Key' required>
                        <input type='hidden' name='go-to-table' value='1'>
                        <input type='submit' value='Delete'>
                    </form>
                </div>
                <h1>". htmlspecialchars($post['title']) ."</h1>
                <p>". htmlspecialchars($post['body']) ."</p>
            </div>

            <div id='comment-form'>
                <form method='POST' action='/bd-internal/".$table."/".$post['id']."/comment'>
                    <input type='text' name='body' placeholder='Your comment' required>
                    <input type='password' name='delkey' placeholder='Deletion key' required>
                    <input type='hidden' name='return-to-post' value='1'>
                    <input type='submit' value='Comment'>
                </form>
            </div>
            <hr>";
        $cmts = db_all("SELECT * FROM replies WHERE post_id = ? ORDER BY timestamp DESC", [$post['id']]);
        foreach ($cmts as $cmt) {
            $body = $body . "
                <div class='reply' id='comment-".$cmt['id']."'>
                    <div class='cmt-meta'>
                        <time datetime='".$cmt['timestamp']."'>".$cmt['timestamp']."</time>
                        <span class='cmt-id'>#c".$cmt['id']."</span>
                        <form action='/bd-internal/".$table."/".$cmt['id']."/delete-cmt' method='POST'>
                            <input type='password'  name='delkey' placeholder='Deletion Key' required>
                            <input type='hidden' name='go-to-table' value='1'>
                            <input type='submit' value='Delete'>
                        </form>
                        Comment
                    </div>
                    <p>".htmlspecialchars($cmt['body'])."</p>
                </div>";
        }
    }
    echo render_template($body, "<a href='/'>/</a> >> <a href='/$table/'>/$table/</a> >> <a href='/$table/$id'>#$id</a>");
});
$router->get("/{table}", function ($table) {
    global $config;
    if (!in_array($table, $config['tables'])) {
        $body = "<h1>Error</h1><br><p>Table does not exist</p>"; 
    } else {
        $body = "<h3>/".$table."/: ". getTableInfo($table) ."</h3>";
        $posts = db_all("SELECT id, name FROM posts WHERE bd_table = ? ORDER BY timestamp DESC", [$table]);
        $body = $body . "
            <hr>
            <form action='/bd-internal/".$table."/post' method='POST'>
                <input type='text' name='title' placeholder='Your post name' required>
                <textarea name='body' rows='4' cols='50' placeholder='Your post body' required></textarea>
                <input type='password' name='delkey' placeholder='Deletion Key' required>
                <input type='hidden' name='go-to-post' value='1'>
                <input type='submit' value='Post'>
            </form>
            <h4>Posts</h4>";
        foreach ($posts as $post) {
            $body = $body . "
            <div id='". $post['id'] ."'>
                <a style='text-decoration: none; color: gray;' href='/".$table."/".$post['id']."'><b>". htmlspecialchars($post['name']) ."</b> <small>#".$post['id']."</small></a>
            </div>
            <hr>
            ";
        }
    }
    echo render_template($body, "<a href='/'>/</a> >> <a href='/$table/'>/$table/</a>");
    return;
});
$router->run();

