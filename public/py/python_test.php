<?php
    echo "something<br>";
    $command = escapeshellcmd('python test.py');
    $output = shell_exec($command);
    echo $output;
    $command = escapeshellcmd('python string.py');
    $output = shell_exec($command);
    echo $output;
?>