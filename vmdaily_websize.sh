#!/bin/bash
wget http://www.vmdaily.ru/ -O /tmp/vmdaily_index.html
size=`du /tmp/vmdaily_index.html | awk '{print $1}'`
if [ "$size" -lt 10 ]
	then
wget http://www.vmdaily.ru/ -O /tmp/vmdaily_index.html
size=`du /tmp/vmdaily_index.html | awk '{print $1}'`
fi
echo $size

