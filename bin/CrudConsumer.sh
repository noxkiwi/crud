#!/bin/bash

# Start a consumer.
startConsumer() {
	consumer=$1
	appCount=$(ps aux | grep "$consumer" | wc -l)
	echo "[INFO] Hello. I may start a $consumer if there is none of them running."
	echo "[INFO] ps aux told me, $consumer has $appCount process(es) running."
	echo "[INFO] This is including the starer.sh and the grep command."
	if [ $appCount -gt 3 ]
	then
		echo "[INFO] There is already a $consumer running. Abort."
		return;
	else
		echo "[INFO] I will now start a $consumer!"
		php $consumer.php > /dev/null 2>&1 &
		echo "[INFO] $consumer has been started."
	fi
	echo "[INFO] Starter for $consumer has finished."
}


startConsumer "MailerConsumer"
exit 0

