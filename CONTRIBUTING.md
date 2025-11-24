# Contributing to SAMS

Thank you for your interest in contributing to the Student Attendance Management System (SAMS)! This document provides guidelines and instructions for contributing.

## 📋 Table of Contents

1. [Code of Conduct](#code-of-conduct)
2. [Getting Started](#getting-started)
3. [Development Setup](#development-setup)
4. [Contribution Workflow](#contribution-workflow)
5. [Coding Standards](#coding-standards)
6. [Testing Guidelines](#testing-guidelines)
7. [Documentation](#documentation)
8. [Pull Request Process](#pull-request-process)
9. [Issue Reporting](#issue-reporting)
10. [Community](#community)

---

## 🤝 Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive environment for all contributors, regardless of:

- Experience level
- Gender identity and expression
- Sexual orientation
- Disability
- Personal appearance
- Body size
- Race or ethnicity
- Age
- Religion

### Our Standards

**Positive Behavior:**

- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what's best for the community
- Showing empathy towards others

**Unacceptable Behavior:**

- Harassment, trolling, or derogatory comments
- Public or private harassment
- Publishing others' private information
- Other conduct which could reasonably be considered inappropriate

### Enforcement

Violations may be reported to project maintainers at: conduct@sams-project.org

---

## 🚀 Getting Started

### Prerequisites

Before contributing, ensure you have:

- [x] PHP 8.0 or higher
- [x] MySQL 5.7 or higher
- [x] Git installed
- [x] Basic understanding of PHP, MySQL, JavaScript
- [x] Familiarity with SAMS architecture (read `/docs/IMPLEMENTATION_GUIDE.md`)

### First-Time Contributors

1. **Find an Issue**: Look for issues tagged `good-first-issue` or `help-wanted`
2. **Introduce Yourself**: Comment on the issue to let others know you're working on it
3. **Ask Questions**: Don't hesitate to ask for clarification
4. **Start Small**: Begin with documentation fixes or minor bug fixes

---

## 💻 Development Setup

### 1. Fork and Clone

```bash
# Fork the repository on GitHub, then:
git clone https://github.com/YOUR_USERNAME/sams.git
cd sams
```

### 2. Set Up Development Environment

```bash
# Install LAMPP/XAMPP
# Follow /docs/SETUP_GUIDE.md

# Start services
sudo /opt/lampp/lampp start
```

### 3. Create Database

```bash
# Import schema
mysql -u root -p < database/schema.sql
mysql -u root -p < database/lti_schema.sql

# Create test data
mysql -u root -p < database/test_data.sql
```

### 4. Configure Environment

```bash
# Copy config template
cp includes/config.sample.php includes/config.php

# Edit with your database credentials
nano includes/config.php
```

### 5. Install Dependencies

```bash
# If using Composer
composer install

# Install Node.js dependencies (if any)
npm install
```

---

## 🔄 Contribution Workflow

### 1. Create a Branch

Always create a new branch for your work:

```bash
# Feature branch
git checkout -b feature/your-feature-name

# Bug fix branch
git checkout -b fix/bug-description

# Documentation branch
git checkout -b docs/documentation-update
```

### Branch Naming Convention

- `feature/` - New features
- `fix/` - Bug fixes
- `docs/` - Documentation updates
- `refactor/` - Code refactoring
- `test/` - Adding tests
- `style/` - Code style changes
- `perf/` - Performance improvements

### 2. Make Changes

```bash
# Make your changes
# Test thoroughly

# Check for errors
php -l your-file.php

# Run tests (if available)
./vendor/bin/phpunit
```

### 3. Commit Changes

Use clear, descriptive commit messages:

```bash
git add .
git commit -m "Type: Brief description

Detailed explanation of what changed and why.

Fixes #123"
```

**Commit Message Format:**

```
Type: Brief description (max 50 chars)

Detailed explanation (if needed):
- What changed
- Why it changed
- Any breaking changes

Fixes #issue_number
```

**Types:**

- `Add:` New feature
- `Fix:` Bug fix
- `Update:` Update existing feature
- `Refactor:` Code refactoring
- `Docs:` Documentation
- `Test:` Tests
- `Style:` Formatting
- `Perf:` Performance

### 4. Push to Your Fork

```bash
git push origin feature/your-feature-name
```

### 5. Create Pull Request

1. Go to your fork on GitHub
2. Click "New Pull Request"
3. Select your branch
4. Fill out the PR template
5. Submit!

---

## 📝 Coding Standards

### PHP Standards

Follow **PSR-12** coding style:

```php
<?php

/**
 * Class description
 *
 * @package SAMS
 * @subpackage Module
 */
class MyClass
{
    /**
     * Method description
     *
     * @param string $param Description
     * @return bool Success status
     */
    public function myMethod($param)
    {
        // Use 4 spaces for indentation
        if ($condition) {
            return true;
        }

        return false;
    }
}
```

**Key Rules:**

- 4 spaces for indentation (no tabs)
- Opening braces on same line for methods
- One statement per line
- Use type hints when possible
- Always use `<?php` (no short tags)

### SQL Standards

```sql
-- Use uppercase for SQL keywords
SELECT
    column1,
    column2,
    column3
FROM table_name
WHERE condition = 'value'
    AND another_condition = 123
ORDER BY column1 ASC;

-- Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
```

### JavaScript Standards

```javascript
// Use ES6+ features
const myFunction = (param) => {
  // Use const/let, not var
  const result = doSomething(param);

  // Use template literals
  console.log(`Result: ${result}`);

  return result;
};

// Use semicolons
// Use camelCase for variables
// Use PascalCase for classes
```

### CSS Standards

```css
/* Use BEM methodology */
.block-name {
  property: value;
}

.block-name__element {
  property: value;
}

.block-name--modifier {
  property: value;
}

/* Use custom properties (CSS variables) */
:root {
  --primary-color: #00ffff;
  --secondary-color: #ff00ff;
}
```

---

## 🧪 Testing Guidelines

### Manual Testing

Before submitting PR, test:

1. **Functionality**

   - [ ] Feature works as expected
   - [ ] No errors in browser console
   - [ ] No PHP errors in logs

2. **Cross-Browser**

   - [ ] Chrome
   - [ ] Firefox
   - [ ] Safari
   - [ ] Edge

3. **Responsive Design**

   - [ ] Mobile (320px - 767px)
   - [ ] Tablet (768px - 1023px)
   - [ ] Desktop (1024px+)

4. **User Roles**
   - [ ] Admin
   - [ ] Teacher
   - [ ] Student
   - [ ] Parent

### Automated Testing (if available)

```bash
# Run PHPUnit tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/LTIIntegrationTest.php

# Check code coverage
./vendor/bin/phpunit --coverage-html coverage/
```

### Security Testing

- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities
- [ ] CSRF tokens properly implemented
- [ ] Input validation on all forms
- [ ] Output escaping where needed

---

## 📚 Documentation

### Code Documentation

Use PHPDoc for all functions and classes:

```php
/**
 * Calculate attendance percentage for a student
 *
 * @param int $student_id Student's database ID
 * @param string $start_date Start date (Y-m-d format)
 * @param string $end_date End date (Y-m-d format)
 * @return float Attendance percentage (0-100)
 * @throws Exception If student not found
 */
function calculateAttendancePercentage($student_id, $start_date, $end_date)
{
    // Implementation
}
```

### File Headers

Include headers in all PHP files:

```php
<?php
/**
 * File: filename.php
 * Purpose: Brief description of file's purpose
 *
 * @package SAMS
 * @subpackage Module
 * @version 2.1.0
 * @since 2025-11-24
 */
```

### Documentation Files

When updating features, also update:

- [ ] README.md (if user-facing change)
- [ ] Relevant `/docs/*.md` files
- [ ] Inline code comments
- [ ] API documentation (if API change)

---

## 🔀 Pull Request Process

### Before Submitting

1. **Self-Review**

   - [ ] Code follows style guidelines
   - [ ] No debugging code left behind
   - [ ] All tests pass
   - [ ] Documentation updated

2. **Rebase on Main**

   ```bash
   git fetch upstream
   git rebase upstream/main
   ```

3. **Squash Commits** (if needed)
   ```bash
   git rebase -i HEAD~3
   ```

### PR Template

When creating a PR, include:

```markdown
## Description

Brief description of changes

## Type of Change

- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing

- [ ] Tested on Chrome
- [ ] Tested on Firefox
- [ ] Tested on mobile
- [ ] All user roles tested

## Screenshots (if applicable)

[Add screenshots here]

## Checklist

- [ ] Code follows style guidelines
- [ ] Self-reviewed code
- [ ] Commented complex code
- [ ] Updated documentation
- [ ] No new warnings
- [ ] Added tests (if applicable)

## Related Issues

Fixes #123
Related to #456
```

### Review Process

1. **Automated Checks** (if configured)

   - Code style check
   - Unit tests
   - Security scan

2. **Peer Review**

   - At least one approval required
   - Address all review comments

3. **Maintainer Review**

   - Final approval from maintainer
   - May request additional changes

4. **Merge**
   - Squash and merge (default)
   - Merge commit (for large features)

---

## 🐛 Issue Reporting

### Bug Reports

Use the bug report template:

```markdown
**Describe the bug**
Clear description of the bug

**To Reproduce**
Steps to reproduce:

1. Go to '...'
2. Click on '...'
3. See error

**Expected behavior**
What should happen

**Screenshots**
If applicable

**Environment:**

- OS: [e.g., Ubuntu 20.04]
- Browser: [e.g., Chrome 96]
- PHP Version: [e.g., 8.0]
- SAMS Version: [e.g., 2.1.0]

**Additional context**
Any other information
```

### Feature Requests

```markdown
**Is your feature request related to a problem?**
Description of problem

**Describe the solution you'd like**
Clear description of desired feature

**Describe alternatives considered**
Other solutions you've considered

**Additional context**
Mockups, examples, etc.
```

### Security Issues

**Do NOT open public issues for security vulnerabilities!**

Email: security@sams-project.org

Include:

- Description of vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

---

## 👥 Community

### Communication Channels

- **GitHub Discussions**: General questions, ideas
- **GitHub Issues**: Bug reports, feature requests
- **Discord** (if available): Real-time chat
- **Email**: info@sams-project.org

### Getting Help

1. **Check Documentation**: Read `/docs` folder
2. **Search Issues**: Your question may be answered
3. **Ask Questions**: GitHub Discussions
4. **Be Patient**: Maintainers are volunteers

### Recognition

Contributors are recognized in:

- CONTRIBUTORS.md file
- Release notes
- Project website (if available)

---

## 📜 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

## 🙏 Thank You!

Your contributions make SAMS better for educational institutions worldwide!

**Questions?** Open a discussion on GitHub or email info@sams-project.org

---

**Last Updated**: November 24, 2025
**Version**: 2.1.0
