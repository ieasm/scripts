#!/bin/bash
##Скрипт считает активное число вызовов с MVTSII
sum=0
while [ $sum = 0 ]
do
el1=(`snmpwalk -v2c -c ytpfdbcbvjcnm 81.26.144.42 1.3.6.1.4.1.28029.11.3.1.2.1 |  cut -d ' ' -f4`)
el2=(`snmpwalk -v2c -c ytpfdbcbvjcnm 81.26.144.42 1.3.6.1.4.1.28029.11.3.1.2.2 |  cut -d ' ' -f4`)
summ=`expr ${el1} + ${el2}`
let "sum= summ / 2"
done
echo $sum


