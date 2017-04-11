
NAME=sn_webservice

export GITHUB_USER=scoutnet
export GITHUB_REPO=plugins.$(NAME)

GIT_VERSION=$(shell git tag | sort | tail -n 1)
COMPOSER_VERSION=$(shell php -r "echo json_decode(file_get_contents('composer.json'))->version;")

CURRENTVERSION=$(COMPOSER_VERSION)

NEXTPATCHVERSION=$(shell php -r 'list($$a,$$b,$$c) = (explode(".","$(CURRENTVERSION)", 3)); echo "$$a.$$b.".($$c+1);')
NEXTMINORVERSION=$(shell php -r 'list($$a,$$b,$$c) = (explode(".","$(CURRENTVERSION)", 3)); echo "$$a.".($$b+1).".0";')
NEXTMAJORVERSION=$(shell php -r 'list($$a,$$b,$$c) = (explode(".","$(CURRENTVERSION)", 3)); echo ($$a + 1).".0.0";')

COMMIT_MESSAGE=$(shell git tag -l $(CURRENTVERSION) -n99 | sed "s/^$(CURRENTVERSION)[ ]*//g" | sed "s/^[ ]*//g" | sed -e ':a' -e 'N' -e '$$!ba' -e 's/\n/

default: zip

zip: Build/$(NAME)_$(CURRENTVERSION).zip

Build/%.zip: checkVersion
	-@[ -d Build ] || mkdir Build
	git archive -o "Build/$(NAME)_$(CURRENTVERSION).zip" $(CURRENTVERSION)

stepPatchVersion:
	@echo NEXT Version: $(NEXTPATCHVERSION)
	@cat composer.json | sed 's/"version": "$(CURRENTVERSION)"/"version": "$(NEXTPATCHVERSION)"/g' > composer_new.json && mv composer_new.json composer.json
	@git add composer.json && git commit -m "new patch $(NEXTPATCHVERSION)"
	@make tag

stepMinorVersion:
	@echo NEXT Version: $(NEXTMINORVERSION)
	@cat composer.json | sed 's/"version": "$(CURRENTVERSION)"/"version": "$(NEXTMINORVERSION)"/g' > composer_new.json && mv composer_new.json composer.json
	@git add composer.json && git commit -m "new minor Version $(NEXTMINORVERSION)"
	@make tag

stepMajorVersion:
	@echo NEXT Version: $(NEXTMAJORVERSION)
	@cat composer.json | sed 's/"version": "$(CURRENTVERSION)"/"version": "$(NEXTMAJORVERSION)"/g' > composer_new.json && mv composer_new.json composer.json
	@git add composer.json && git commit -m "new Version $(NEXTMAJORVERSION)"
	@make tag

tag:
	@if [ ! -n "$$(git tag -l $(CURRENTVERSION))" ]; then git tag -a $(CURRENTVERSION); fi
	@echo You can now use git push --tags to push all changes to github

release: checkVersion Build/$(NAME)_$(CURRENTVERSION).zip
	@if [ -z "$(GITHUB_TOKEN)" ]; then echo "Please Set ENV GITHUB_TOKEN"; exit 2; fi
	@echo "* Upload Release $(CURRENTVERSION) to Github"
	@github-release release -t $(CURRENTVERSION) -d "Release of version $(CURRENTVERSION)
	@github-release upload -t $(CURRENTVERSION) -f Build/$(NAME)_$(CURRENTVERSION).zip -n "$(NAME)_$(CURRENTVERSION).zip"
	@echo "* Upload Done"

clean:
	rm -rf Build/*.zip

checkVersion:
	@echo GIT_VERSION: $(GIT_VERSION)
	@echo COMPOSER_VERSION: $(CURRENTVERSION)
	@[ "$(GIT_VERSION)" = "$(CURRENTVERSION)" ]
	@echo "* All Versions correct"

deploy: checkVersion Build/$(NAME)_$(CURRENTVERSION).zip
	# clean build folder
	-@[ -d Build/$(NAME) ] && rm -rf Build/$(NAME)
	mkdir Build/$(NAME)
	unzip Build/$(NAME)_$(CURRENTVERSION).zip -d Build/$(NAME)
	# install ter uploader
	cd Build && composer require namelesscoder/typo3-repository-client
	@cd Build && vendor/bin/upload $(NAME) $(TYPO3_TER_USER) $(TYPO3_TER_PASSWORD) "$(shell git tag -l $(CURRENTVERSION) -n99 | sed "s/^$(CURRENTVERSION)[ ]*//g" | sed "s/^[ ]*//g")"
	@echo "uploaded $(CURRENTVERSION)"
