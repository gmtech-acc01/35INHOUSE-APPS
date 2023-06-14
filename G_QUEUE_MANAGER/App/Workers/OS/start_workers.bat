@echo off
title OSWorkersProcessManager

::call "start_single_worker.cmd"

::start SilentCMD.exe "start_single_worker.cmd"
::start os_cmd.exe "start_single_worker.cmd"
::start os_cmd.exe "start_single_worker.cmd"


::kill all the previous process

echo Killing previous processes ...
taskkill /im os_cmd.exe /f /t

echo Starting.
echo ''

set /p n_process=<pc.txt
sleep 0
echo processes=%n_process%
FOR /L %%i IN (0,1,%n_process%) DO (
	color 0c
	echo [%%i] starting worker process ..
	start os_cmd.exe start_single_worker.cmd
	sleep 0
)
color 0a