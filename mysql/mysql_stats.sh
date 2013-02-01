
#!/bin/bash
### Автор скрипта - sirkonst@gmail.com
### Сайт поддержки - http://wiki.enchtex.info/howto/zabbix/advanced_mysql_monitoring
 
### DESCRIPTION
# $1 - измеряемая метрика
# [$2] - пользователь mysql для подключения (не обязательный параметр, можно задать в скрипте ниже)
# [$3] - пароль пользователя (не обязательный параметр, можно задать в скрипте ниже)
 
### OPTIONS VERIFICATION
if [ -z $1 ]; then
	exit 1
 fi
 
### PARAMETERS
METRIC="$1"
USER="${2:-user}"	# если имя пользователя не указано в  после символов ":-"
PASSWD="${3:-}"		# пароль из 3-го параметра или указать после символов ":-"
 
MYSQLADMIN=`which mysqladmin`
MYSQL=`which mysql`
CACHEFILE="/tmp/mysql-stats.cache"
CACHETTL="55"	# Время действия кеша в секундах (чуть меньше чем период опроса элементов)
 
### RUN
# Проверка работы mysql не кешируется
if [ $METRIC = "alive" ]; then
 $MYSQLADMIN -u$USER -p$PASSWD ping | grep alive | wc -l
 exit 0
fi
 
# Проверка версии mysql
if [ $METRIC = "version" ]; then
 $MYSQL -V
 exit 0
fi
 
## Проверка кеша
# время создание кеша (или 0 есть файл кеша отсутствует или имеет нулевой размер)
if [ -s "$CACHEFILE" ]; then
	TIMECACHE=`stat -c"%Z" "$CACHEFILE"`
else
	TIMECACHE=0
fi
 
# текущее время
TIMENOW=`date '+%s'`
# Если кеш неактуален, то обновить его (выход при ошибке)
if [ "$(($TIMENOW - $TIMECACHE))" -gt "$CACHETTL" ]; then
	$MYSQLADMIN -u$USER -p$PASSWD extended-status > $CACHEFILE || exit 1
#extended-status
fi
 
# Получение значения указанной метрики
cat $CACHEFILE | grep -iw "$METRIC" | cut -d'|' -f3
 
exit 0
