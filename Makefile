up:
	./vendor/bin/sail up -d
	./vendor/bin/sail exec laravel.test php artisan migrate

down:
	./vendor/bin/sail down

restart:
	./vendor/bin/sail down
	./vendor/bin/sail up -d

chat:
	./vendor/bin/sail artisan chat

bash:
	./vendor/bin/sail exec laravel.test /bin/bash
