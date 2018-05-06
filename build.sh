#!/usr/bin/env bash

module_name="npumoodlestat"

build_dir="build"

files_to_move=(
"assets"
"lang"
"category.php"
"course.php"
"Helper.php"
"settings.php"
"version.php"
)

root=$PWD

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

cd ${build_dir}

zip -r ${module_name}_$(date '+%H%M%d%m%Y') ${module_name}

rm -rf ${root}/${build_dir}/${module_name}
