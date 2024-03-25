.DEFAULT: _info
	@echo "No make target defined"

.PHONY: _info
_info:
	@echo "Running make target '$(MAKECMDGOALS)' of '$(abspath $(lastword $(MAKEFILE_LIST)))'"

.PHONY: build
build: _info vendors
	npm run build

.PHONY: vendors
vendors: _info
	npm ci
