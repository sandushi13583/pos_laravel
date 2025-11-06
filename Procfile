web: bash bin/start
release: php -r "@mkdir('storage/app/public/uploads', 0777, true);" && php artisan storage:link || true && ln -sfn "$PWD/storage/app/public/uploads" "$PWD/public/uploads" || true && chmod -R 0777 "$PWD/storage/app/public/uploads" "$PWD/public/uploads" || true
