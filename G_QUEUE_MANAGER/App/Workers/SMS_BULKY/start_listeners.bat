@echo off
title SMSListenersProcessManager

::call "start_single_listener.cmd"

::start SilentCMD.exe "start_single_listener.cmd"
::start mailer_cmd.exe "start_single_listener.cmd"
::start mailer_cmd.exe "start_single_listener.cmd"


::kill all the previous process

echo Killing previous processes ...
taskkill /im sms_cmd.exe /f /t

echo Starting.
echo ''

set /p n_process=<pc.txt
sleep 0
echo processes=%n_process%
FOR /L %%i IN (0,1,%n_process%) DO (
	color 0c
	echo [%%i] starting listener process ..
	start sms_cmd.exe start_single_listener.cmd
	sleep 0
)
color 0a