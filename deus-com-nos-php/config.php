<?php
declare(strict_types=1);
session_start();
$dsn='mysql:host='. (getenv('DB_HOST') ?: '127.0.0.1').';dbname='.(getenv('DB_NAME') ?: 'deus_com_nos').';charset=utf8mb4';
try {$db=new PDO($dsn,getenv('DB_USER') ?: 'root',getenv('DB_PASS') ?: '',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}catch(PDOException $e){http_response_code(503);exit('Base de données indisponible. Vérifiez config.php et importez schema.sql.');}
function e($v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function csrf():string{return $_SESSION['csrf']??=bin2hex(random_bytes(32));}
function check():void{if(!hash_equals(csrf(),$_POST['csrf']??'')){http_response_code(403);exit('Session expirée.');}}
function user():?array{return $_SESSION['user']??null;}
function redirect(string $to='index.php'):never{header('Location: '.$to);exit;}
function flash(string $msg):void{$_SESSION['flash']=$msg;}
function money($v):string{return number_format((float)$v,2,',',' ').' $';}
