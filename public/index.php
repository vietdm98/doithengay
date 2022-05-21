<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) require $maintenance;
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

exec("cd .. && git status | grep 'modified:'", $outGetFileChange);
$outGetFileChange = array_map(function($file){
    $file = explode('modified:', $file);
    return trim(end($file));
}, $outGetFileChange);
$countFileChangeError = array_filter($outGetFileChange, function($file){
    if($file == 'public/css/custom.css') return false;
    return !str_ends_with($file, '.blade.php');
});
$outGetFileChange = array_map(function($file){
    return '<li>' . $file . '</li>';
}, $outGetFileChange);
if(count($countFileChangeError) > 0 && !file_exists('./dev_code')) {
    echo '<meta charset="UTF-8">';
    echo '<h3>Hệ thống đang khóa chức năng chỉnh sửa logic. Bạn chỉ có thể sửa file custom.css hoặc file .blade.php!</h3>';
    echo '<p>Những file đã sửa:</p>';
    echo '<ul>';
    echo implode('', $outGetFileChange);
    echo '</ul>';
    die(0);
}else{
    $kernel = $app->make(Kernel::class);
    $response = $kernel->handle($request = Request::capture())->send();
    $kernel->terminate($request, $response);
}
