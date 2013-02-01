# -*- coding: utf-8 -*-
import pickle

def parse(log, codes, trunks, dump_dir):

	resolve_code = {'Россия, Москва, моб. (Билайн)':'beeline',
					'Россия, Москва, моб. (МСС)':'mss',
					'Россия, Москва, моб. (МТС)':'mts',
					'Россия, Москва, моб. (Мегафон)':'megafon'}			

	actual_codes = {}					#словарь - результат парсинга
	j = 0

	for line in log:
		j+=1 							#счетчик кол-ва звонков в логе
		elements = line.split(';')		#разделение строки на элементы
		src_num = elements[5]			#номер звонка
		code = ''

		for i in src_num:
			code = code + i
			if code in codes:

				if ('Россия, Москва, моб.' in codes[code]):
					code = resolve_code[codes[code]]

				if not (code in actual_codes):
					actual_codes[code] = {'calls':0, 'suc_calls': 0, 'trunks':{}, 'asr': 0, 'without_tg': 0}

				actual_codes[code]['calls']+=1.0

				if (elements[14] != '0') and (elements[14]!=''):
					actual_codes[code]['suc_calls']+=1.0

				if elements[10] != '':
					tg_num = elements[10]

					if not (tg_num in actual_codes[code]['trunks']):

						if tg_num in trunks:
							tg_name = trunks[tg_num]
						else:
							tg_name = tg_num

						actual_codes[code]['trunks'][tg_num] = {'tg_calls':0, 'tg_suc_calls':0, 'tg_asr':0, 'tg_name':tg_name}

					actual_codes[code]['trunks'][tg_num]['tg_calls']+=1.0

					if (elements[14] != '0') and (elements[14]!=''):
						actual_codes[code]['trunks'][tg_num]['tg_suc_calls']+=1.0

					if actual_codes[code]['trunks'][tg_num]['tg_calls'] != 0:
						actual_codes[code]['trunks'][tg_num]['tg_asr'] = actual_codes[code]['trunks'][tg_num]['tg_suc_calls']/actual_codes[code]['trunks'][tg_num]['tg_calls']*100
				else:
					actual_codes[code]['without_tg']+=1

				if actual_codes[code]['calls']!=0:
					actual_codes[code]['asr']= actual_codes[code]['suc_calls']/actual_codes[code]['calls']*100
			else:
				pass

	print "complete!"
	print "number of calls: ", j
	print "number of codes: ", len(actual_codes.keys())
	print "writing in file: code_dump"
	code_dump = open(dump_dir+'/code_dump','w')
	pickle.dump(actual_codes, code_dump)
	code_dump.close()

	print '\n'

	return actual_codes

def regroupe(actual_codes, dump_dir):
	print "regrouping..."

	actual_trunks = {}

	for key in actual_codes.keys():

		for trunk in actual_codes[key]['trunks'].keys():

			if not (trunk in actual_trunks):
				actual_trunks[trunk] = {'calls':0, 'suc_calls':0, 'asr':0, 'codes':{}, 'tg_name':''}

			actual_trunks[trunk]['tg_name'] = actual_codes[key]['trunks'][trunk]['tg_name']

			actual_trunks[trunk]['calls'] = actual_trunks[trunk]['calls'] + actual_codes[key]['trunks'][trunk]['tg_calls']
			actual_trunks[trunk]['suc_calls'] = actual_trunks[trunk]['suc_calls'] + actual_codes[key]['trunks'][trunk]['tg_suc_calls']

			if actual_trunks[trunk]['calls'] !=0:
				actual_trunks[trunk]['asr'] = actual_trunks[trunk]['suc_calls']/actual_trunks[trunk]['calls']*100

			if not (key in actual_trunks[trunk]['codes']):
				actual_trunks[trunk]['codes'][key] = {'code_calls':0, 'code_suc_calls':0, 'code_asr':0}

			actual_trunks[trunk]['codes'][key]['code_calls'] = actual_codes[key]['trunks'][trunk]['tg_calls']
			actual_trunks[trunk]['codes'][key]['code_suc_calls'] = actual_codes[key]['trunks'][trunk]['tg_suc_calls']
			actual_trunks[trunk]['codes'][key]['code_asr'] = actual_codes[key]['trunks'][trunk]['tg_asr']

	print "complete!"
	print "writing in file: trunk_dump"
	trunk_dump = open(dump_dir+'/trunk_dump','w')
	pickle.dump(actual_trunks, trunk_dump)
	trunk_dump.close()

	return actual_trunks
