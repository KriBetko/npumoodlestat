#!/usr/bin/env bash

module_name="npumoodlestat"

build_dir="build"

files_to_move=(

"lang"
"index.php"
"lib.php"
"db"
"locallib.php"
"mod_form.php"
"README.txt"
"version.php"
"view.php"
)

if [ ! -d ${build_dir} ];
then
    mkdir ${build_dir}
else
    rm -rfv ${build_dir}/*
fi

zip -r build/${module_name}_$(date '+%d%m%Y %H%M%S') ${files_to_move[@]}
