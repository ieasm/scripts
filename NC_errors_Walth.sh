#!/bin/sh

cmd="set timeout 10\nspawn telnet 172.17.38.4\nexpect \":\"\nsend  \"osp\"\nset timeout 10\nexpect \":\"\n"
cmd="${cmd}send \"jnltkcgtwghjtrnjd\"\nset timeout 10\nexpect \"#\"\nsend \"sh interfaces Serial0/3/1:0 \"\nexpect \"#\"\nsend \"exit\\\n\""
unset a
echo $cmd
a=`printf "$cmd" | /usr/bin/expect --`
echo $a
#while [ -z $a ]
#do
#sleep 10

#a=`printf "$cmd" | /usr/bin/expect -- | grep -e "Active prefixes:" | awk -F'[:]' '{print $2}' | awk '{print $1}'`
#if [ -n "$a" ]; then
#	echo $a
#fi
#done
