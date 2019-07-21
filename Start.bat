@echo off
tasklist /nh /fi "imagename eq php.exe" | find /i "php.exe" || (start cmd /c "title Halliwick && php -S localhost:8080 -t C:\Users\HASI\HalliwickSystem")
start http://localhost:8080/

