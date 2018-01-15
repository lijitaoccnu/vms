#!/bin/bash

source ./common.sh

#
#在指定服务器上执行更新操作
#
do_publish()
{
    cli="sh /home/www/publish/publish-remote.sh -p '$project' -v '$version'"
    sshpass -p $server_pwd ssh $server_user@$server_host "$cli"
    RETVAL=$?

    my_echo "Publish to $server_host" 33 "-n"

    if [ ! $RETVAL = 0 ]
    then
        echo_fail
    else
        echo_ok
    fi

    return $RETVAL
}

if [ "$server_host" = "" ] || [ "$server_user" = "" ] || [ "$server_pwd" = "" ]
then
    echo_usage
fi

echo_separator_line
my_echo "Publish files start..." 36

do_publish
RETVAL=$?

my_echo "Publish files end..." 36
echo_separator_line

if [ ! $RETVAL = 0 ]; then
    echo_error "Publish failed"
fi

echo_success