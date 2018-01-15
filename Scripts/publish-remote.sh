#!/bin/bash

source ./common.sh

package="$basepath/${project}_${version}.zip"

package_dir="$basepath/temp/${project}_${version}"

backup_dir="$basepath/backup/${project}_${version}"

unzip_package()
{
    if [ ! -f $package ];then
        echo_error "$package is not found!"
    fi

    if [ -d $package_dir ];then
        rm -rf $package_dir
    fi

    cd $basepath

    unzip $package 1>/dev/null

    if [ ! -d $package_dir ];then
        echo_error "$package is invalid!"
    fi
}

backup()
{
    if [ -f $backup_dir ] || [ -d $backup_dir ];then
        rm -rf $backup_dir
    fi

    mkdir -p $backup_dir

    rsync -a $backup_filter ${document_root}/* ${backup_dir}/
}

do_publish()
{
    unzip_package

    if [ ! -d $document_root ];then
        mkdir -p $document_root
    fi

    count=`ls $package_dir|wc -w`

    if [ "$count" != "0" ];then
        rsync -a ${package_dir}/* ${document_root}/
    fi

    rm -rf $package_dir
    rm -f $package

    backup
}

do_publish

exit 0