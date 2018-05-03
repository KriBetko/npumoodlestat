#!/usr/bin/env bash

module_name="npumoodlestat"

build_dir="build"

files_to_move=(
"db"
"lang"
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
else
    rm -rfv ${build_dir}/*
fi

for entity in ${files_to_move[@]}
do
	cp -r ${entity} ${build_dir}/${module_name}
done

zip -r build/${module_name}_$(date '+%d%m%Y %H%M%S') ${build_dir}/${module_name}

rm -rf ${build_dir}/${module_name}
