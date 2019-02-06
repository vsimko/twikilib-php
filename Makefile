help:
	@echo "Useful targets: build test"

build: dist

dist:
	mkdir dist
	cp runapp.php dist
	cp -a merge-to-dist/. dist
	cp -r tests/dummy_twiki_root dist/
	./build-phar.php

release: dist
	cd dist && zip -r ../twikilib-php-1.0.zip .

clean:
	rm -r dist twikilib-php-*.zip

test:
	echo not implemented yet