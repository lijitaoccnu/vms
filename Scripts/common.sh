#!/bin/bash

project=""

version=""

server_host=""

server_user=""

server_pwd=""

document_root=""

backup_filter=""

if [ ! $caller ]; then
    caller="sh"
fi

basepath=$(cd `dirname $0`; pwd)

source /etc/profile

#
#输出脚本使用说明
#
echo_usage()
{
    my_echo "###########################################################" 35
    my_echo "#    Usage:                                               #" 35
    my_echo "#    -p project name                                      #" 35
    my_echo "#    -v version code                                      #" 35
    my_echo "#    -h server host                                       #" 35
    my_echo "#    -u server user                                       #" 35
    my_echo "#    -P server password                                   #" 35
    my_echo "###########################################################" 35

    exit 1
}

#
#检查脚本参数
#
check_params()
{
    if [ "$project" = "" ] || [ "$version" = "" ]; then
        echo_usage
    fi
}

#
#操作授权
#
authorize()
{
    read -s -p "please enter the password of root: " password

    if [ "$password" = "" ]; then
        echo_error "Authorize failed"
    fi

    sshpass -p "$password" ssh root@127.0.0.1 'echo "ok"'
    RETVAL=$?

    if [ $RETVAL = 0 ]; then
        my_echo "Authorize success" 32
    else
        echo_error "Authorize failed"
    fi
}

#
#输出分隔线
#
echo_separator_line()
{
    my_echo "-----------------------------------------------------------"
}

convert_to_rgb()
{
    case $1 in
        30)
            color="black";;
        31)
            color="red";;
        32)
            color="green";;
        33)
            color="yellow";;
        34)
            color="blue";;
        35)
            color="#DB7093";;
        36)
            color="#3EEDE7";;
        37)
            color="white";;
        *)
            color="black";;
    esac

    echo $color
}

my_echo()
{
    text=$1
    color=$2
    opt=$3
    pos=$4

    if [ "$color" = "" ]; then
        color=34
    fi

    if [ "$pos" = "" ]; then
        pos=0
    fi

    if [ $caller = "PHP" ]; then
        tag="p"
        if [ $pos > 0 ]; then
            tag='span'
        fi
        color=$(convert_to_rgb $color)
        echo "<${tag} style='color:${color}'>${text}</${tag}>"
    else
        echo -e $opt "\033[${pos}G\033[${color}m${text}\033[0m"
    fi

    return 0
}

#
#在固定位置输出OK
#
echo_ok()
{
    my_echo "[OK]" 32 "" 60
}

#
#在固定位置输出FAIL
#
echo_fail()
{
    my_echo "[FAIL]" 31 "" 54
}

#
#输出成功提示
#
echo_success()
{
    text=$1

    if [ "$text" = "" ]; then
        text="SUCCESS"
    fi

    my_echo "$text" 32

    exit 0
}

#
#输出错误提示
#
echo_error()
{
    my_echo "$1" 31

    exit 1
}

#
#加载项目配置
#
load_config()
{
    conf_file="${basepath}/conf/${project}.conf"

    if [ ! -f $conf_file ]; then
        echo_error "File $conf_file not found!"
    fi

    source $conf_file

    my_echo "project = $project"
    my_echo "version = $version"
    my_echo "document_root = $document_root"
    my_echo "backup_filter = $backup_filter"
}

while getopts ":p:v:h:u:P:" opt
do
    case $opt in
        p) project="$OPTARG";;
        v) version="$OPTARG";;
        h) server_host="$OPTARG";;
        u) server_user="$OPTARG";;
        P) server_pwd="$OPTARG";;
        *) echo_usage;;
    esac
done

check_params

load_config