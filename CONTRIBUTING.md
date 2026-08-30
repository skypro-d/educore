# Contributing to EduCore (CONTRIBUTING.md)

Thank you for choosing to contribute to EduCore! Here's how you can get involved.

---

## 1. Reporting Bugs
- Search existing issues to verify the bug has not been reported.
- If it is a security vulnerability, do **not** file a public issue (refer to `SECURITY.md`).
- File a clear issue detailing step-by-step reproduction steps, expected behavior, and error logs.

---

## 2. Code Standards
- **Strict Typing**: All new classes must declare strict typing at the top:
  ```php
  <?php
  declare(strict_types=1);
  ```
- **CSRF & Security**: Ensure that any new POST requests are verified using `verify_csrf()`, and database edits target the appropriate model scoped via `TenantPDO`.
- **Formatting**: Format all changes using PSR-12 code styling rules.

---

## 3. Pull Request Process
1. Fork the repository and create a new feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```
2. Implement your changes and write unit verification scripts inside the `scratch/` directory.
3. Keep commit messages clear, explaining **why** the change was made.
4. Open a Pull Request mapping to the `main` development branch.
5. Once tests pass and maintainers approve, the PR will be merged.
