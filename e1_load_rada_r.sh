#!/bin/bash
unset count
unset rrrr
unset bb
unset cc
unset
count=(`/usr/bin/snmpwalk -v2c -c ytpfdbcbvjcnm 84.47.178.3 1.3.6.1.2.1.10.21.1.3.1.1.5`)

bb=1
for i in  $(seq 0 $((${#count[@]})))
	do
		if [ ${count[3]} == "Such" ]
		then
			bb=0

		else
		cc=${#count[@]}
		let "rrrr = $cc / 8"
		fi
	done

if [ $bb = 0 ]
	then
		echo $bb
	else
		echo $rrrr
fi
