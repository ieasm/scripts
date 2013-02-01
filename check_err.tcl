#!/usr/bin/expect

spawn telnet 172.17.38.4

expect "Username:" 
send "osp\r"  
expect "Password:" 
send "jnltkcgtwghjtrnjd\r"
expect "Gate-NewYork2#"


send "sh int Serial0/3/0:0 | inc input errors\r"
expect -indices -re "(.*)abort"
set response $expect_out(buffer)

set index1 [string first "\r" $response]
set index2 [string last "input" $response]

set index1 [expr $index1 + 7]
set index2 [expr $index2 - 2]
set inp_err [string range $response $index1 $index2]

set fileid [open "/var/zabbix/serial0300_input_nc_ny" w+]
puts $fileid $inp_err
close $fileid


expect "*"


send "sh int Serial0/2/0:0 | inc input errors\r"
expect -indices -re "(.*)abort"
set response $expect_out(buffer)

set index1 [string first "\r" $response]
set index2 [string last "input" $response]

set index1 [expr $index1 + 7]
set index2 [expr $index2 - 2]
set inp_err [string range $response $index1 $index2]

set fileid [open "/var/zabbix/serial0310_input_nc_ny" w+]
puts $fileid $inp_err
close $fileid


expect "*"

send "sh int Serial0/3/0:0 | inc output errors\r"
expect -indices -re "(.*)resets"
set response $expect_out(buffer)

set index1 [string first "\r" $response]
set index2 [string last "output" $response]

set index1 [expr $index1 + 7]
set index2 [expr $index2 - 2]
set inp_err [string range $response $index1 $index2]

set fileid [open "/var/zabbix/serial0300_output_nc_ny" w+]
puts $fileid $inp_err
close $fileid

expect "*"

send "sh int Serial0/3/1:0 | inc output errors\r"
expect -indices -re "(.*)resets"
set response $expect_out(buffer)

set index1 [string first "\r" $response]
set index2 [string last "output" $response]

set index1 [expr $index1 + 7]
set index2 [expr $index2 - 2]
set inp_err [string range $response $index1 $index2]

set fileid [open "/var/zabbix/serial0310_output_nc_ny" w+]
puts $fileid $inp_err
close $fileid

send "exit\r"
exit 0
