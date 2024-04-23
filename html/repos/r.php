<?php
/* Adapted from https://github.com/TuzelKO/git-php-backend/blob/master/src/GitPHP/Backend.php */

$projectsDir = "/usr/share/repos/";
$cmd = "/usr/lib/git-core/git-http-backend";
$url = null;
$userName = "user";
$userEmail = "user@example.com";
$request = file_get_contents("php://input");
$requestUrl = (empty($_SERVER['REQUEST_URI']))? null:strtok($_SERVER["REQUEST_URI"],'?');
$requestUrl = (empty($url))? $requestUrl:strtok($_SERVER["REQUEST_URI"],'?');
$remoteAddr = (empty($_SERVER['REMOTE_ADDR']))? null:$_SERVER['REMOTE_ADDR'];
$queryString = (empty($_SERVER['QUERY_STRING']))? null:$_SERVER['QUERY_STRING'];
$requestMethod = (empty($_SERVER['REQUEST_METHOD']))? null:$_SERVER['REQUEST_METHOD'];
$contentType = (empty($_SERVER['CONTENT_TYPE']))? null:$_SERVER['CONTENT_TYPE'];
$contentLength = (empty($_SERVER['CONTENT_LENGTH']))? null:$_SERVER['CONTENT_LENGTH'];

$environment = [
            'GIT_HTTP_EXPORT_ALL' => true,
            'GIT_PROJECT_ROOT' => $projectsDir,
            'PATH_INFO' => $requestUrl,
            'REMOTE_USER' => $userName,
            'REMOTE_ADDR' => $remoteAddr,
            'QUERY_STRING' => $queryString,
            'REQUEST_METHOD' => $requestMethod,
            'CONTENT_TYPE' => $contentType,
            'CONTENT_LENGTH' => $contentLength,
		    'GIT_COMMITTER_NAME' => $userName,
		    'GIT_COMMITTER_EMAIL' => $userEmail
        ];

$send = true;
$timeTaken = microtime(true);
$pipes = [];
$desc = [
            0 => ['pipe', 'r'], // STDIN
            1 => ['pipe', 'w'], // STDOUT
            2 => ['pipe', 'w']  // STDERR
        ];

$proc = proc_open($cmd, $desc, $pipes, null, $environment);

if(!empty($request)) fwrite($pipes[0], $request);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
foreach($pipes as $pipe) fclose($pipe);
$timeTaken = microtime(true) - $timeTaken;
list($headers, $body, $status) =
	array_pad(explode("\r\n\r\n", $stdout, 2), 3, '');

$status = preg_match('/Status: (\\d\\d\\d)/', $headers, $status)? $status[1]:0;

$headers = explode("\r\n", $headers);

if($send === true){
	foreach($headers as $header) header($header, true, $status);
        echo $body;
}
proc_close($proc);
?>
