include config.mk

PHAR := AdvancedKits.phar

all: $(PHAR)

phpstan:
	$(PHP_PM) $(COMPOSER_PHAR) install
	$(PHP_PM) vendor/bin/phpstan analyse

clean:
	rm -rf shaded $(PHAR)

shade: clean phpstan
	$(PHP_PM) $(COMPOSER_PHAR) install --no-dev
	$(PHP_PM) $(SHADER_SCRIPT)

$(PHAR): shade
	$(PHP_PM) $(DEVTOOLS_PHAR) --make shaded --out $(PHAR)

.PHONY: phpstan shade clean all

