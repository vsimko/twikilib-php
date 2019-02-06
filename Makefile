help:
	@echo "Useful targets: build test"

build:
	mkdir dist
	cp runapp.php dist
	cp -a merge-to-dist/. dist
	cp -r tests/dummy_twiki_root dist/
	./build-phar.php

clean:
	rm -r dist

test:
	echo not implemented yet