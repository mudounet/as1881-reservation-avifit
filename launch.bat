@ECHO OFF
echo in php.ini
echo  - extension=pdo_sqlite
echo  - extension=intl
echo  - extension=mbstring

C:\php\php.exe -S localhost:8000
pause