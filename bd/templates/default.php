<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>
        body {
            background-color: #fffff8;
            color: #000;
            margin: 2em auto;
            max-width: 800px;
            line-height: 1.5;
        }

        a {
            color: #0000cc;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 2em 0;
        }

        .post {
            margin-bottom: 2em;
        }

        .post-meta {
            color: #666;
            font-size: 0.9em;
        }
        .cmt {
            margin-bottom: 2em;
        }

        .cmt-meta {
            color: #666;
            font-size: 0.9em;
        }


        pre, textarea {
            background: #f0f0f0;
            padding: 1em;
            overflow-x: auto;
        }

        textarea {
            width: 100%;
            height: 6em;
        }

        .form {
            margin-top: 1em;
        }

        input[type="text"] {
            width: 50%;
        }

        button {
            margin-top: 0.5em;
        }

        .reply {
            margin-left: 2em;
            margin-top: 1em;
            border-left: 2px solid #ccc;
            padding-left: 1em;
        }

        h1, h2, h3 {
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <h1><a href="/"><?= $title ?></a> <?= $desc ?></h1>
    <hr>

    <?= $body ?>

</body>
</html>


