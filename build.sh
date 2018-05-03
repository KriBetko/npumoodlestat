#!/usr/bin/env bash

module_name="npumoodlestat"

build_dir="build"

files_to_move=(
"db"
"lang"
"db"
"block_npumoodlestat.php"
"edit_form.php"
"settings.php"
"version.php"
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

rm ${build_dir}/${module_name}/access.php

zip -r ${build_dir}/${module_name}_$(date '+%H%M%d%m%Y') ${build_dir}/${module_name}

rm -rf ${build_dir}/${module_name}
