@echo off
echo Creating database backup...
set timestamp=%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set timestamp=%timestamp: =0%

C:\xampp\mysql\bin\mysqldump -u root chania_db > "C:\xampp\htdocs\chania\backups\chania_db_backup_%timestamp%.sql"

if %errorlevel% equ 0 (
    echo Backup successful: chania_db_backup_%timestamp%.sql
) else (
    echo Backup failed!
)
pause
