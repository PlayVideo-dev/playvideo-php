# Contributing to PlayVideo PHP SDK

Thank you for your interest in contributing to the PlayVideo PHP SDK!

## Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/PlayVideo-dev/playvideo-php.git
   cd playvideo-php
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Run tests:
   ```bash
   composer test
   ```

## Running Tests

```bash
# Run all tests
composer test

# Run PHPStan static analysis
composer phpstan

# Check code style (PSR-12)
composer cs
```

## Project Structure

```
src/
├── PlayVideo.php           # Main client class
├── Resources/              # API resource implementations
│   ├── Collections.php
│   ├── Videos.php
│   ├── Webhooks.php
│   ├── Embed.php
│   ├── ApiKeys.php
│   ├── Account.php
│   └── Usage.php
├── Exceptions/             # Exception classes
│   ├── PlayVideoException.php
│   ├── AuthenticationException.php
│   ├── NotFoundException.php
│   └── ...
└── Webhook.php             # Webhook signature verification

tests/
├── ClientTest.php
└── WebhookSignatureTest.php
```

## Making Changes

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass: `composer test`
6. Ensure static analysis passes: `composer phpstan`
7. Ensure code style passes: `composer cs`
8. Commit your changes: `git commit -m "Add my feature"`
9. Push to your fork: `git push origin feature/my-feature`
10. Open a Pull Request

## Code Style

- We follow PSR-12 coding standards
- Use strict types: `declare(strict_types=1);`
- Add type hints to all methods
- Add PHPDoc blocks to public APIs
- Use PHP 8.1+ features where appropriate

## Pull Request Guidelines

- Include a clear description of the changes
- Reference any related issues
- Add tests for new functionality
- Update documentation if needed
- Keep PRs focused on a single change

## Reporting Issues

When reporting issues, please include:

- SDK version
- PHP version
- Operating system
- Minimal code to reproduce the issue
- Expected vs actual behavior

## Questions?

If you have questions, feel free to open an issue or reach out at support@playvideo.dev.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
