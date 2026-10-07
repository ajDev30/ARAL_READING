<?php
$python_path = __DIR__ . '/v536/.azure-venv/bin/python3';
$script_path = __DIR__ . '/v536/service.py';
$log_path = __DIR__ . '/v536/service.log';
$cwd = __DIR__ . '/v536';
exec("pkill -f 'python3 $script_path'");
$cmd = "cd $cwd && nohup $python_path $script_path > $log_path 2>&1 &";
exec($cmd);
echo "Restarted!";
?>
