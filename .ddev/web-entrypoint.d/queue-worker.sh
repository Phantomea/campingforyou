#!/bin/bash
# Spusti Laravel queue worker na pozadi
cd /var/www/html/backend
nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 >> /var/log/queue-worker.log 2>&1 &
echo "Queue worker started (PID $!)"
