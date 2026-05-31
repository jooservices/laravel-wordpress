# Testing

Use Composer scripts as the source of truth for package validation.

```bash
composer validate --strict
composer lint
composer test
composer test:unit
composer test:feature
composer test:integration
composer test:real
composer quality
```

`composer test:unit` covers isolated package behavior such as registries, DTOs, payload mapping,
and service construction.

`composer test:feature` covers Laravel package behavior with the test application.

`composer test:integration` covers integration tests configured in `phpunit.xml`. These tests may
skip when required real WordPress environment variables or services are not present.

`composer test:real` uses the local WordPress Docker helper scripts in `scripts/wp-test`.

For the full fresh Laravel plus real WordPress workflow, run:

```bash
./scripts/test-docker.sh
```

Do not replace real WordPress SDK/API assertions with mocks or fake WordPress responses. Preserve
the Docker artifacts:

- `artifacts/integration-report.json`
- `artifacts/integration-summary.txt`
- `artifacts/junit.xml`
