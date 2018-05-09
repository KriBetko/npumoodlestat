#!/usr/bin/env bash

module_name="npumoodlestat"

build_dir="build"

files_to_move=(
"assets"
"lang"
"assets"
"ajax.php"
"category.php"
"meta.php"
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

gulp sass

for entity in ${files_to_move[@]}
do
	cp -rv ${entity} ${build_dir}/${module_name}
done

rm -rf ${build_dir}/${module_name}/js
rm -rf ${build_dir}/${module_name}/style

cd ${build_dir}

zip -r ${module_name}_$(date '+%H%M%d%m%Y') ${module_name}

rm -rf ${root}/${build_dir}/${module_name}
