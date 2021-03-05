<?php
/**
 * @var $exception
 */
$code = isset($exception->statusCode) ? $exception->statusCode : 'Error';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $code ?> - Woops 发生错误了~~~</title>
    <style>
        html,body{padding:0;font-size:12px;}
        body{font-size:1vw;}
        html,body,h1,h3,h4,h6{margin:0;}
        h1,h3,h4,h6{margin-block-start: 0;margin-block-end: 0}
        .container{text-align: center;}
        h1{color: #F56C6C;font-size: 2vw;}
        h3{color: #409EFF;font-size: 1vw;}
        a{color: #409EFF;text-decoration: none;}
    </style>
</head>
<body>
<div class="container">
    <pre>
        <h1><?= $code ?></h1>
        <h3>well! It seems some error occur</h3>
        <h4><?= $exception->getMessage(); ?></h4>
        <h4><?= $exception->getFile(); ?>(Line:<?= $exception->getLine(); ?>)</h4>
        <h6><a href="javascript:history.go(-1);">返回</a></h6>
    </pre>
</div>
</body>
</html>