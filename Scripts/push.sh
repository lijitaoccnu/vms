#!/bin/bash

source ./common.sh

#将更新包推送至指定服务器
#@param $1 files
#
sync_files()
{
    my_echo "Sync files to $server_host" 33 "-n"

    files=$1

    for file in ${files[@]}
    do
        if [ ! -f $file ]; then
            echo_fail
            return 1
        fi

        sshpass -p $server_pwd rsync -zac -e ssh $file $server_user@$server_host:/home/www/publish/
        RETVAL=$?
        if [ ! $RETVAL = 0 ]; then
            echo_fail
            return 2
        fi
    done

    echo_ok

    return 0
}

if [ "$server_host" = "" ] || [ "$server_user" = "" ] || [ "$server_pwd" = "" ]
then
    echo_usage
fi

echo_separator_line
my_echo "Push files start..." 36

files=(
    "${basepath}/package/${project}_${version}.zip"
    "${basepath}/conf/${project}.conf"
    "${basepath}/publish-remote.sh"
    "${basepath}/rollback-remote.sh"
    "${basepath}/common.sh"
)

sync_files files
RETVAL=$?

my_echo "Push files end..." 36
echo_separator_line

if [ ! $RETVAL = 0 ]; then
    echo_error "Push failed"
fi

echo_success