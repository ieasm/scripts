# -*- coding: utf-8 -*-
import ftp, parse, save, api
import os
import pickle
import time
#----------------------------------------------------
#Директории и файлы скрипта:
#	./data - директория с файлами, необходимыми для парсинга
#		codes - файл с соответствием кодов и направлений
#		si_trunks - файл с соответствием транковых групп и направлений для si2000
#	./parsing_dump - директория с дампом результатов парсинга
#		./AXE
#			code_dump - дамп словаря с результатами парсинга по кодам
#			trunk_dump - дамп словаря с результатами парсинга по операторам
#		./MVTS
#			code_dump - дамп словаря с результатами парсинга по кодам
#			trunk_dump - дамп словаря с результатами парсинга по операторам
#		./si2000
#			code_dump - дамп словаря с результатами парсинга по кодам
#			trunk_dump - дамп словаря с результатами парсинга по операторам
#	./source - директория с исходными кодами модулей
#		parse.py - модуль с функциями парсинга логов
#		ftp.py - модуль с функциями работы с фтп
#		save.py - модуль с функциями выведения на диск результатов парсинга
#		api.py - модуль с функциями, взаимодействующими с API Zabbix
#	parse.pyc - скомпилированный модуль с функциями парсинга логов
#	ftp.pyc - скомпилированный модуль с функциями работы с фтп
#	save.pyc - скомпилированный модуль с функциями выведения на диск результатов парсинга
#	api.pyc - скомпилированный модуль с функциями, взаимодействующими с API Zabbix
#	./telephony - целевая директория для сохранения файлов для Zabbix-агента
#	./zabbix_agent - директория с конфигурационным файлом Zabbix-агента
#	flushed_file - содержит список имен созданных файлов
#
#Используемые функции внешних модулей:
#
#ftp.download_log(log_dir) - скачивает с FTP предпоследний лог из директории log_dir, возвращает имя лога
#parse.parse(log, f_dump, f_trunks, dump_dir) - парсит лог и сохраняет результаты на диск
#												log - имя лога, f_dump - имя файла с кодами, f_trunks - имя файла с операт.
#												dump_dir - дочерняя директория для сохранения результата парсинга
#parse.regroupe(actual_codes, dump_dir)- перегруппирует результаты парсинга по транковым группам, считает ASR
#save.flush_result(actual_codes, directory, block=10) - выталкивает на диск файлы для Zabbix-агента
#												actual_codes - словарь с результатами парсинга
#												directory - директория, в которую сохранять файлы
#												block - макс. количество файлов, выводимое в одном потоке
#save.prepare_to_flush() - возвращает словарь только с теми АСР, которые надо записать на диск
#switches - список (кортеж) коммутаторов
#
#ключи словаря:
#
#name - название коммутатора (используется для сохранения файлов в директорию с этим именем и для создания эл-ов данных)
#directory - директория, в которой лежат логи на FTP
#trunk_data_dir - директория, в которой лежит файл с операторами
#
#-----------------------------------------------------

def zabbix_agent_reload():
	print 'restarting zabbix_agentd'
	os.system('service zabbix-agent restart')
	time.sleep(2)


def clear_dir(switch):
	os.system('rm /etc/zabbix/scripts/telephony/telephony/'+switch+'/*')
	time.sleep(0.2)


###############################################################################################################
#
# Настройки
#
#-------------------------------------------------------------------------------------------------------------
#
#директория, в которой лежит скрипт
#
env_prefix = '/etc/zabbix/scripts/telephony'
#
#имя файла с соответствием кодов и названий направлений
#
code_data_file_name = '/data/codes'
#
#директория с результатами парсинга
#
parsing_dump_dir = '/parsing_dump/'
#
#файл для обмена между процессами
#
flushed_file_name = '/flushed_file'
#
#Директория с файлами для заббикс агента
#
zab_ag_dir = '/telephony/'
#
#Путь к файлу zabbix_agentd.conf
#
zagconf_path = '/etc/zabbix/etc/zabbix_agentd.conf'
#
#Параметры коммутаторов
#
switches = (
			{
			'name':					'si2000',							#Название коммутатора
			'directory':			'./si2000',							#Директория на фтп
			'trunk_data_dir':		env_prefix + '/data/si_trunks'		#Директория с файлом соответствия операторов
			},
			{
			'name':					'AXE', 								#
			'directory':			'./axe810', 						#
			'trunk_data_dir':		env_prefix + '/data/axe_trunks'		#
			},
			#{
			#'name':'MVTS', 'directory':'./mvtsii', 'trunk_data_dir':env_prefix + '/data/mvts_trunks'
			#},
			)
#
##############################################################################################################



code_data_file = open(env_prefix + code_data_file_name,'r')
code_data = pickle.load(code_data_file)

for switch in switches:

	trunk_data_file = open(switch['trunk_data_dir'], 'r')
	trunk_data = pickle.load(trunk_data_file)


	#Соединение с FTP и загрузка предпоследнего лога
	log_name = ftp.download_log(switch['directory'], env_prefix)

	#Обработка лога и сохранение результатов в файл
	print "handling log file..."
	log = open(log_name, 'r') #лог коммутатора

	actual_codes = parse.parse(log, code_data, trunk_data, env_prefix + parsing_dump_dir + switch['name'])


	#Перегруппировка результатов парсинга и пересчет ASR по транковым группам и сохранение в файл
	actual_trunks = parse.regroupe(actual_codes, env_prefix + parsing_dump_dir + switch['name'])


	flushed_file = open(env_prefix + flushed_file_name, 'w')
	flushed_file.write('')
	flushed_file.close()
	flushed_file2 = open(env_prefix + flushed_file_name+'2', 'w')
	flushed_file2.write('')
	flushed_file2.close()
	
	#Создание файлов для заббикс агента
	codes_to_flush = save.prepare_to_flush(actual_codes, trunk_data)
	save.flush_result(codes_to_flush, env_prefix + zab_ag_dir + switch['name'])
	time.sleep(0.3) #для синхронизации с процессами создания файлов
	

	#Загрузка списка сохраненных файлов
	flushed = []
	flushed_file = open(env_prefix + flushed_file_name, 'r')
	for line in flushed_file:
		flushed.append(line[:-1])
	flushed_file.close()
	print 'flushed: ', flushed

	flushed2 = []
	flushed_file2 = open(env_prefix + flushed_file_name + '2', 'r')
	for line in flushed_file2:
		if not line[:-1] in flushed2:
			flushed2.append(line[:-1])
	flushed_file2.close()
	print 'flushed2: ', flushed2

	save.rewrite_old(env_prefix+zab_ag_dir+switch['name']+'/', flushed, flushed2)
	time.sleep(0.3)

	#Удаление лога
	os.remove(log_name)

	#Авторизация через API и начало сессии
	zapi = api.start_session()

	#Получение списка существующих элементов данных
	existing_items = api.get_items(zapi, switch['name'])

	print len(flushed)
	#Создание элементов данных, которых нет в списке
	for item in flushed:
		if not (item in existing_items):
			zagconf = open(zagconf_path, 'a')
			trig_id = api.create_item(zapi, zagconf, switch['name'], code_data[item.split('_')[0]][1]+'_'+switch['name'], item)
			(serv_name, serv_parent, serv_child) = api.resolve_names(item, code_data, trunk_data, switch['name'])
			api.add_services(zapi, serv_name, serv_parent, serv_child, trig_id, switch['name'])
			zagconf.close()

	for item in flushed2:
		if not (item in existing_items):
			zagconf = open(zagconf_path, 'a')
			trig_id = api.create_item(zapi, zagconf, switch['name'], code_data[item.split('_')[0]][1]+'_'+switch['name'], item)
			zagconf.close()

	trunk_data_file.close()

code_data_file.close()

zabbix_agent_reload()
