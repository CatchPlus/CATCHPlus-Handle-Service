<?php

$input = fopen('php://input', 'r');
$output = fopen('include/chunked', 'w');
while (!feof($input)) {
  fwrite($output, fgetc($input));
  fflush($output);
}

header('Content-Type: text/plain; charset=US-ASCII');
?>Hello world!