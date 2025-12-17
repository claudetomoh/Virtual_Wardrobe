SHELL := /bin/bash

.PHONY: start stop seed logs

start:
	docker-compose up -d --build

start-dev:
	docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d --build

stop:
	docker-compose down

seed:
	# Seed the DB container via php container
	docker exec -it vw-php php src/admin/seed_sample.php

seed-host:
	# seed using XAMPP host php (Windows)
	# Replace C:\xampp\php\php.exe with your php path if needed
	C:\\xampp\\php\\php.exe src/admin/seed_sample.php

logs:
	docker-compose logs --no-color -f
