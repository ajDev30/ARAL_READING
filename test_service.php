<?php
$python_path = __DIR__ . '/v536/.azure-venv/bin/python3';
$script_path = __DIR__ . '/v536/service.py';
$cwd = __DIR__ . '/v536';
$output = shell_exec("cd $cwd && $python_path $script_path 2>&1");
echo $output;
?>
