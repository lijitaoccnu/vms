#!/bin/bash

source ./common.sh

backup_dir="${basepath}/backup/${project}_${version}"

do_rollback()
{
    if [ ! -d $backup_dir ];then
        echo_error "$backup_dir is not exist!"
    fi

    if [ ! -d ${document_root} ];then
        mkdir $document_root
    fi

    rsync -a ${backup_dir}/* ${document_root}/
}

do_rollback

exit 0