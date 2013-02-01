#!/bin/sh

cmd="set timeout 10\nspawn ssh 172.16.148.3 -l smena\nexpect \"*?\"\nsend  \"yes\\\n\"\nset timeout 15\nexpect \"*:\"\n"
cmd="${cmd}send \"cvtyf502\\\n\"\nexpect \"*>\"\nsend \"show bgp neighbor 149.6.140.149 | no-more\\\n\"\nexpect \"*>\"\nsend \"exit\\\n\""
unset a

while [ -z $a ]
do
sleep 10
a=`printf "$cmd" | /usr/bin/expect -- | grep -e "Active prefixes:" | awk -F'[:]' '{print $2}' | awk '{print $1}'`
if [ -n "$a" ]; then
	echo $a
fi
done
