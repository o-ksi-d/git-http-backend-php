<?php
/*
Adapted from https://github.com/TuzelKO/git-php-backend/blob/master/src/GitPHP/Backend.php  (c) 2023 by Jean-Marc Lienher


BSD 3-Clause License

Copyright (c) 2019, Eugene Frost
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this
   list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice,
   this list of conditions and the following disclaimer in the documentation
   and/or other materials provided with the distribution.

3. Neither the name of the copyright holder nor the names of its
   contributors may be used to endorse or promote products derived from
   this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

function php_git($projectsDir) {

	$cmd = "/usr/lib/git-core/git-http-backend";

	$userName = "user";
	$userEmail = "user@none.com";

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

/*	file_put_contents("t.txt", print_r($_SERVER, true));
	file_put_contents("p.txt", print_r($_FILES, true));
	var_dump($environment);
*/


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

        list($headers, $body, $status) =
            array_pad(explode("\r\n\r\n", $stdout, 2), 3, '');

        $status =
            preg_match('/Status: (\\d\\d\\d)/', $headers, $status)? $status[1]:null;

        $headers = explode("\r\n", $headers);

        foreach($headers as $header) header($header, true, $status);
        echo $body;

	proc_close($proc);
}

php_git("/usr/share/");

?>
