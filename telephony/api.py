# -*- coding: utf-8 -*-
from zabbix_api import ZabbixAPI
import random

def start_session(server="http://sla.naukanet.ru/", username="sadist", password="y9cplcy0"):
	zapi = ZabbixAPI(server=server, path="", log_level=6)
	zapi.login(username, password)
	return zapi

def get_items(zapi, host_name):
	items_dict = {}
	items = zapi.item.get({'filter':{'host':host_name}, 'output':['name']})
	for item in items:
		items_dict[item['name']] = item['itemid']
	return items_dict

def create_item(zapi, zagconf, host_name, app_name, item_name):
	interfaces = zapi.host.get({"filter":{"host":host_name}, "selectInterfaces":"refer"})
	hostid = interfaces[0]['hostid']
	interfaceid = interfaces[0]['interfaces'].keys()[0]
	apps = zapi.application.get({'hostids':[hostid], 'output':'extend'})
	for app in apps:
		if app['name'] == unicode(app_name, 'utf-8'):
			appid = app['applicationid']
	key = host_name + item_name + r'[]'
	delay = str(random.randrange(3000,4200,60))
	history = 30
	zapi.item.create({
					 'hostid' : hostid,
					 'key_' : key, 
					 'name' : item_name, 
					 'type': "0", 
					 'value_type': 0, 
					 'interfaceid':interfaceid, 
					 'applications':[appid], 
					 'history':history, 
					 'delay':delay 
					 })
	zagconf.write('UserParameter=%s%s[*], cat /etc/zabbix/scripts/telephony/telephony/%s/%s \n' % (host_name, item_name, host_name, item_name))
	zagconf.flush()
	trig = zapi.trigger.create({
								'description':'Low ASR '+ item_name,
								'expression':'{'+host_name+':'+key+'.last(0)}<10',
								'priority': 4
								})
	trig_id = trig['triggerids'][0]
	return trig_id

def resolve_names(item, code_data, trunk_data, switch_name):
	code = item.split('_')[0]
	operator = item.split('_')[1]
	name = code + ' ' + code_data[code][0] + ' (' + switch_name + ')'
	parent_name = code_data[code][1] + ' (' + switch_name + ')'
	child_name = operator + ' ' + trunk_data[operator] + ' (' + switch_name + ')'
	return (name, parent_name, child_name)

def add_services(zapi, serv_name, serv_parent, serv_child, trig_id, switch_name):
	parent = zapi.service.get({'output':'shorten', 'filter':{'name':serv_parent}})
	print serv_parent
	print parent
	parent_id = parent[0]['serviceid']
	service = zapi.service.get({'output':'shorten', 'filter':{'name':serv_name}})
	if not service:
		service = zapi.service.create({'name':      serv_name,
                                    'algorithm':    '1',
                                    'showsla':      '1',
                                    'goodsla':      '99.7',
                                    'status':       '0',
                                    'sortorder':    '0'
                                    })
		service_id = service['serviceids'][0]
		zapi.service.addDependencies({'serviceid':          parent_id, 
                                    'dependsOnServiceid':   service_id,
                                    'soft':                 0
                                    })
	else:
		service_id = service[0]['serviceid']

	child = zapi.service.create({	'name':         serv_child,
                                    'algorithm':    '1',
                                    'showsla':      '1',
                                    'goodsla':      '99.7',
                                    'status':       '0',
                                    'sortorder':    '0',
                                    'triggerid':	trig_id
                                })
	child_id = child['serviceids'][0]
	zapi.service.addDependencies({'serviceid':            service_id, 
                                  'dependsOnServiceid':   child_id,
                                  'soft':                 0
                                 })


	parent = zapi.service.get({'output':'shorten', 'selectDependencies':'extend', 'filter':{'name':switch_name}})
	parent_id = parent[1]['serviceid']
	service = zapi.service.get({'output':'shorten', 'filter':{'name':serv_child}})
	
	service_exists = False
	for s in service:
		for d in parent[1]['dependencies']:
			if s['serviceid'] == d['serviceid']:
				service_exists = True
				service_id = s['serviceid']



	if not service_exists:
		service = zapi.service.create({'name':      serv_child,
                                   'algorithm':    '1',
                                    'showsla':      '1',
                                    'goodsla':      '99.7',
                                    'status':       '0',
                                    'sortorder':    '0'
                                    })
		service_id = service['serviceids'][0]
		zapi.service.addDependencies({'serviceid':          parent_id, 
                                    'dependsOnServiceid':   service_id,
                                    'soft':                 0
                                    })

	child = zapi.service.create({	'name':         serv_name,
                                    'algorithm':    '1',
                                    'showsla':      '1',
                                    'goodsla':      '99.7',
                                    'status':       '0',
                                    'sortorder':    '0',
                                    'triggerid':	trig_id
                                })
	child_id = child['serviceids'][0]
	zapi.service.addDependencies({'serviceid':            service_id, 
                                  'dependsOnServiceid':   child_id,
                                  'soft':                 0
                                 })
