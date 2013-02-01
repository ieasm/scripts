#!/bin/bash
##Monitoring mac address table in cisco 6503 switch for client Evening Moscow.
##This algorithm doesn't analyse mac address on the such port, it analyse whole table 
unset a
unset sum
a=(`snmpwalk -v2c -c ytpfdbcbvjcnm@601 172.16.148.23 .1.3.6.1.2.1.17.4.3.1.1 | awk '{print $4}'`)
b=(`snmpwalk -v2c -c ytpfdbcbvjcnm@475 172.16.148.23 .1.3.6.1.2.1.17.4.3.1.1 | awk '{print $4}'`)
c=(`snmpwalk -v2c -c ytpfdbcbvjcnm@478 172.16.148.23 .1.3.6.1.2.1.17.4.3.1.1 | awk '{print $4}'`)
d=(`snmpwalk -v2c -c ytpfdbcbvjcnm@477 172.16.148.23 .1.3.6.1.2.1.17.4.3.1.1 | awk '{print $4}'`)
e=(`snmpwalk -v2c -c ytpfdbcbvjcnm@470 172.16.148.23 .1.3.6.1.2.1.17.4.3.1.1 | awk '{print $4}'`)
f=(`snmpwalk -v2c -c ytpfdbcbvjcnm@2551 172.16.148.23 .1.3.6.1.2.1.17.4.3.1.1 | awk '{print $4}'`)
g=(`snmpwalk -v2c -c ytpfdbcbvjcnm@2553 172.16.148.23 .1.3.6.1.2.1.17.4.3.1.1 | awk '{print $4}'`)
h=(`snmpwalk -v2c -c ytpfdbcbvjcnm@2555 172.16.148.23 .1.3.6.1.2.1.17.4.3.1.1 | awk '{print $4}'`)
i=(`snmpwalk -v2c -c ytpfdbcbvjcnm@2771 172.16.148.23 .1.3.6.1.2.1.17.4.3.1.1 | awk '{print $4}'`)

let "sum= ${#a[@]} + ${#b[@]} + ${#c[@]} + ${#d[@]} + ${#e[@]} + ${#f[@]} + ${#g[@]} + ${#h[@]} + ${#i[@]}"
echo $sum

