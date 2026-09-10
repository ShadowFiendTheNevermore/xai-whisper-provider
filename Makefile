IMAGE ?= sf-xai-whisper-provider:dev

.PHONY: build install shell lint test update-lock

build:
	docker build --tag "$(IMAGE)" .

install: build
	docker run --rm --mount type=bind,src="$(CURDIR)",dst=/app "$(IMAGE)" composer install --no-interaction --prefer-dist

update-lock:
	docker run --rm --mount type=bind,src="$(CURDIR)",dst=/app -w /app composer:2 update --lock

shell:
	docker run --rm -it --mount type=bind,src="$(CURDIR)",dst=/app "$(IMAGE)" sh

lint:
	docker run --rm "$(IMAGE)" composer lint

test:
	docker run --rm "$(IMAGE)" composer test
