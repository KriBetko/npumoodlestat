#!/usr/bin/env bash

module_name="npumoodlestat"

build_dir="build"

files_to_move=(
"backup"
"classes"
"db"
"lang"
"pix"
"tests"
"grade.php"
"index.php"
"lib.php"
"locallib.php"
"mod_form.php"
"README.txt"
"version.php"
"view.php"
)

if [ ! -d ${build_dir} ];
then
    mkdir ${build_dir}
fi

zip -r build/${module_name}_$(date '+%d%m%Y%H%M%S') ${files_to_move[@]}
