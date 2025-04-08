@echo off
echo Creating folder structure...

REM Create core folder and its files
mkdir core
echo. > core\data_handler.php
echo. > core\product_module.php
echo. > core\sale_module.php
echo. > core\report_module.php

REM Create ui folder and its subfolders and files
mkdir ui
echo. > ui\index.php
echo. > ui\inventory.php
echo. > ui\reports.php
mkdir ui\css
echo. > ui\css\.gitkeep
mkdir ui\js
echo. > ui\js\.gitkeep

REM Create data folder and its files
mkdir data
echo [] > data\products.json
echo [] > data\sales.json

REM Create config folder and its file
mkdir config
echo. > config\settings.php

REM Create the root index.php file
echo. > index.php

echo Folder and file structure created successfully!
pause