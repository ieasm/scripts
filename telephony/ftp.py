# -*- coding: utf-8 -*-
from ftplib import FTP

def download_log(log_dir, env_prefix):
	print "connecting to ftp..."

	ftp = FTP('10.1.2.17')
	msg = ftp.login('atc','naukanet')
	print msg

	ftp.cwd(log_dir)
	L = ftp.nlst()
	L.sort()
	log_name = L[-2]


	print "retrieving latest log..."
	msg = ftp.retrbinary('RETR '+log_name, open(env_prefix+'/'+log_name, 'wb').write)
	print log_name, msg

	print "closing connection..."
	msg = ftp.quit()
	print msg


	print '\n'

	return env_prefix+'/' + log_name
