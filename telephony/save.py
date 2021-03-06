# -*- coding: utf-8 -*-
import os, sys
import pickle

def flush_result(codes_to_flush, directory, block = 10):
	print '\n'
	print 'Creating files for zabbix agent...'

	code_list = codes_to_flush.keys()
	for i in range(len(code_list)/block+1):
		pid = os.fork()

		if pid == 0:
			for key in code_list[i*block:(i+1)*block]:
				f = open(directory+'/'+key, 'w')
				f.write(str(round(codes_to_flush[key]['asr'], 2)))
				f.close()
				flushed_file2 = open('/etc/zabbix/scripts/telephony/flushed_file2', 'a')
				flushed_file2.write(key+'\n')
				flushed_file2.flush()
				for trunk in codes_to_flush[key]['trunks'].keys():
					fname = key+'_'+trunk
					f1 = open(directory+'/'+fname, 'w')
					f1.write(str(round(codes_to_flush[key]['trunks'][trunk]['tg_asr'], 2)))
					f1.close()
					flushed_file = open('/etc/zabbix/scripts/telephony/flushed_file', 'a')
					flushed_file.write(fname+'\n')
					flushed_file.flush()
				flushed_file.close()

			sys.exit(0)
	print 'complete!'
	return None

def prepare_to_flush(actual_codes, trunk_data):
	codes_to_flush = {}
	for key in actual_codes.keys():
		for trunk in actual_codes[key]['trunks'].keys():
			if actual_codes[key]['trunks'][trunk]['tg_name'] in trunk_data.values():
				codes_to_flush[key] = actual_codes[key]
				print codes_to_flush[key]['trunks'][trunk]['tg_name']

	for key in codes_to_flush.keys():
		for trunk in codes_to_flush[key]['trunks'].keys():
			if not (codes_to_flush[key]['trunks'][trunk]['tg_name'] in trunk_data.values()):
				del(codes_to_flush[key]['trunks'][trunk])
	return codes_to_flush

def rewrite_old(path, flushed, flushed2):
	block = 10
	listdir = os.listdir(path)
	for i in range(len(listdir)/block+1):
		pid = os.fork()

		if pid == 0:
			for fname in listdir[i*block:(i+1)*block]:
				if not ((fname in flushed) or (fname in flushed2)):
					f = open(path+fname, 'w')
					f.write('')
					f.close
			sys.exit(0)

