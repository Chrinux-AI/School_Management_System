# Nature-Themed UI Implementation Guide

## Student Attendance Management System (SAMS)

**Version 2.1.0** | Last Updated: November 24, 2025

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [Core CSS Files](#core-css-files)
4. [JavaScript Components](#javascript-components)
5. [Component Library](#component-library)
6. [Page Templates](#page-templates)
7. [Best Practices](#best-practices)
8. [Accessibility](#accessibility)
9. [Customization](#customization)
10. [Troubleshooting](#troubleshooting)

---

## 🌟 Overview

The Nature-Themed UI system provides a comprehensive, organic design framework for SAMS. Inspired by eco-friendly aesthetics with green gradients, leaf motifs, and natural animations, this system ensures consistency across all pages while offering advanced features like 3D card tilts, animated progress bars, and dynamic gradients.

### Key Features

- **🎨 Consistent Design Tokens**: CSS custom properties for colors, spacing, typography
- **🌿 Organic Animations**: Leaf particles, growth effects, smooth transitions
- **📱 Fully Responsive**: Mobile-first approach with breakpoints
- **♿ Accessibility**: WCAG 2.1 AA compliant with screen reader support
- **⚡ Performance**: Optimized animations with reduced motion support
- **🔧 Modular**: Reusable components with clear documentation

---

## 🚀 Getting Started

### 1. Include Required Files

Add these files in the `<head>` section of every page:

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your Page Title | SAMS</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />

    <!-- Nature Theme CSS -->
    <link rel="stylesheet" href="/assets/css/nature-theme.css" />
    <link rel="stylesheet" href="/assets/css/nature-components.css" />
  </head>
</html>
```

### 2. Add JavaScript Before Closing `</body>`

```html
    <!-- Nature UI Scripts -->
    <script src="/assets/js/nature-animations.js"></script>

    <!-- Your custom scripts -->
    <script>
        // Your page-specific code here
    </script>
</body>
</html>
```

---

## 🎨 Core CSS Files

### 1. `nature-theme.css` (Base Theme)

**Purpose**: Foundational styles, design tokens, typography, global animations

**Key Sections**:

- **CSS Custom Properties**: Color palette, spacing scale, typography
- **Global Reset**: Box-sizing, margins, padding
- **Typography**: Heading styles, drop caps
- **Gradients**: Primary, earth, gold, hero, card, animated
- **Animations**: Leaf fall, pulse, glow, fade-in, slide-up
- **Utility Classes**: Spacing, flexbox, grid, visibility

**Usage Example**:

```html
<!-- Hero section with gradient background -->
<section class="hero-nature">
  <h1 class="hero-title">Welcome to SAMS</h1>
  <p class="hero-subtitle">Manage attendance with ease</p>
  <button class="btn btn-gold">Get Started</button>
</section>
```

### 2. `nature-components.css` (Component Library)

**Purpose**: Reusable UI components (navigation, cards, tables, modals, alerts)

**Key Components**:

- Navigation (navbar, sidebar)
- Cards (stat cards, grids, 3D effects)
- Tables (with alternating beige rows)
- Tabs (with smooth transitions)
- Modals (with leaf-bordered containers)
- Alerts & Toasts
- Badges & Progress Bars
- Forms & Inputs
- Pagination

**Usage Example**:

```html
<!-- Stat card with icon -->
<div class="stat-card">
  <div class="stat-header">
    <div class="stat-icon">
      <i class="fas fa-users"></i>
    </div>
  </div>
  <div class="stat-value">1,248</div>
  <div class="stat-label">Total Students</div>
  <div class="stat-change positive">
    <i class="fas fa-arrow-up"></i> 12% this month
  </div>
</div>
```

---

## ⚙️ JavaScript Components

### `nature-animations.js`

**Features**:

- **Leaf Particles**: Falling leaves background animation
- **Card Animations**: 3D tilt effect on hover
- **Button Ripple**: Ripple effect on click
- **Scroll Animations**: Fade-in on scroll
- **Modal System**: Open/close with backdrop
- **Tab System**: Switch between tabs
- **Toast Notifications**: Temporary messages
- **Progress Bars**: Animated growth
- **Counter Animation**: Number count-up effect
- **Form Validation**: Nature-themed error feedback

**API Methods**:

```javascript
// Show toast notification
window.showToast(message, type, duration);
// Example: window.showToast('Success!', 'success', 3000);

// Animate progress bar
NatureUI.animateProgress(element, targetValue, duration);
// Example: NatureUI.animateProgress(progressBar, 85, 1000);

// Animate counter
NatureUI.animateCounter(element, targetValue, duration);
// Example: NatureUI.animateCounter(statValue, 1248, 2000);

// Validate form
NatureUI.validateForm(formElement);

// Confirm dialog
NatureUI.confirm(message, onConfirm, onCancel);

// Copy to clipboard
NatureUI.copyToClipboard(text, successMessage);

// Toggle sidebar (mobile)
NatureUI.toggleSidebar();
```

---

## 📦 Component Library

### Navigation Components

#### Top Navbar

```html
<nav class="nature-navbar">
  <div class="navbar-brand"><i class="fas fa-leaf"></i> SAMS</div>
  <ul class="navbar-menu">
    <li><a href="#" class="navbar-link active">Dashboard</a></li>
    <li><a href="#" class="navbar-link">Students</a></li>
    <li><a href="#" class="navbar-link">Reports</a></li>
  </ul>
</nav>
```

#### Sidebar Navigation

```html
<aside class="nature-sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <i class="fas fa-graduation-cap"></i>
    </div>
    <h3>SAMS</h3>
  </div>
  <ul class="sidebar-menu">
    <li class="sidebar-item">
      <a href="#" class="sidebar-link active">
        <i class="fas fa-home sidebar-icon"></i>
        <span>Dashboard</span>
        <span class="sidebar-badge">5</span>
      </a>
    </li>
    <!-- More items... -->
  </ul>
</aside>
```

### Card Components

#### Stat Card

```html
<div class="stat-card">
  <div class="stat-header">
    <div class="stat-icon">
      <i class="fas fa-users"></i>
    </div>
  </div>
  <div class="stat-value" data-counter="1248">0</div>
  <div class="stat-label">Total Students</div>
  <div class="stat-change positive">
    <i class="fas fa-arrow-up"></i> 12% this month
  </div>
</div>
```

#### Basic Card

```html
<div class="nature-card">
  <h3>Card Title</h3>
  <p>Card content goes here with nature-themed styling.</p>
</div>
```

#### Card with Leaf Border

```html
<div class="nature-card card-leaf-border">
  <h3>Premium Content</h3>
  <p>This card has a gradient leaf border.</p>
</div>
```

#### 3D Card

```html
<div class="nature-card card-3d">
  <h3>Interactive Card</h3>
  <p>Hover to see the 3D tilt effect.</p>
</div>
```

### Button Components

```html
<!-- Primary Green Button -->
<button class="btn btn-primary"><i class="fas fa-plus"></i> Add New</button>

<!-- Gold Button -->
<button class="btn btn-gold"><i class="fas fa-star"></i> Featured</button>

<!-- Earth Tone Button -->
<button class="btn btn-earth"><i class="fas fa-save"></i> Save</button>

<!-- Outline Button -->
<button class="btn btn-outline">
  <i class="fas fa-download"></i> Download
</button>
```

### Table Component

```html
<div class="nature-table-container">
  <h2>Student Records</h2>
  <table class="nature-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Class</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>STU001</td>
        <td>John Doe</td>
        <td>CS 101</td>
        <td><span class="nature-badge badge-success">Active</span></td>
        <td>
          <button class="btn-primary" style="padding: 0.25rem 0.75rem;">
            <i class="fas fa-eye"></i>
          </button>
        </td>
      </tr>
      <!-- Alternating beige rows applied automatically -->
    </tbody>
  </table>
</div>
```

### Tab System

```html
<div class="nature-tabs">
  <div class="tab-nav">
    <button class="tab-button active" data-tab="overview">Overview</button>
    <button class="tab-button" data-tab="details">Details</button>
    <button class="tab-button" data-tab="analytics">Analytics</button>
  </div>
  <div class="tab-content">
    <div class="tab-panel active" id="overview">
      <p>Overview content...</p>
    </div>
    <div class="tab-panel" id="details">
      <p>Details content...</p>
    </div>
    <div class="tab-panel" id="analytics">
      <p>Analytics content...</p>
    </div>
  </div>
</div>
```

### Modal Component

```html
<!-- Modal Trigger -->
<button class="btn btn-primary" data-modal-target="myModal">Open Modal</button>

<!-- Modal Structure -->
<div class="modal-backdrop" id="myModal">
  <div class="modal-container">
    <div class="modal-header">
      <h3 class="modal-title">Modal Title</h3>
      <button class="modal-close">×</button>
    </div>
    <div class="modal-body">
      <p>Modal content goes here.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-modal-close>Cancel</button>
      <button class="btn btn-primary">Confirm</button>
    </div>
  </div>
</div>
```

### Alert Components

```html
<!-- Success Alert -->
<div class="nature-alert alert-success">
  <i class="fas fa-check-circle alert-icon" style="color: var(--success);"></i>
  <div class="alert-content">
    <div class="alert-title">Success!</div>
    <div class="alert-message">Operation completed successfully.</div>
  </div>
</div>

<!-- Warning Alert -->
<div class="nature-alert alert-warning">
  <i
    class="fas fa-exclamation-triangle alert-icon"
    style="color: var(--warning);"
  ></i>
  <div class="alert-content">
    <div class="alert-title">Warning</div>
    <div class="alert-message">Please review your input.</div>
  </div>
</div>

<!-- Error Alert -->
<div class="nature-alert alert-error">
  <i class="fas fa-times-circle alert-icon" style="color: var(--error);"></i>
  <div class="alert-content">
    <div class="alert-title">Error</div>
    <div class="alert-message">Something went wrong.</div>
  </div>
</div>
```

### Form Elements

```html
<form id="myForm">
  <div class="form-group">
    <label class="form-label">Full Name</label>
    <input
      type="text"
      class="form-input form-input-vine"
      placeholder="Enter name"
      required
    />
  </div>

  <div class="form-group">
    <label class="form-label">Email</label>
    <input
      type="email"
      class="form-input"
      placeholder="your@email.com"
      required
    />
  </div>

  <div class="form-group">
    <label class="form-label">Select Option</label>
    <select class="form-select">
      <option>Option 1</option>
      <option>Option 2</option>
    </select>
  </div>

  <div class="form-group">
    <label class="form-label">Message</label>
    <textarea class="form-textarea" rows="4"></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### Progress Bars

```html
<!-- Standard Progress -->
<div class="progress-container">
  <div class="progress-bar" style="width: 75%;"></div>
</div>

<!-- Vine Growth Progress -->
<div class="progress-container">
  <div class="progress-bar progress-vine" style="width: 92%;"></div>
</div>
```

### Icon Grid (Quick Actions)

```html
<div class="icon-grid">
  <div class="icon-card" onclick="location.href='/student/checkin.php'">
    <div class="icon-card-icon">
      <i class="fas fa-user-check"></i>
    </div>
    <div class="icon-card-title">Check-In</div>
  </div>

  <div class="icon-card">
    <div class="icon-card-icon" style="background: var(--gold-400);">
      <i class="fas fa-calendar"></i>
    </div>
    <div class="icon-card-title">Schedule</div>
  </div>

  <!-- More icons... -->
</div>
```

---

## 📄 Page Templates

### Basic Page Template

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Page Title | SAMS</title>

    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link rel="stylesheet" href="/assets/css/nature-theme.css" />
    <link rel="stylesheet" href="/assets/css/nature-components.css" />
  </head>
  <body>
    <!-- Hero Section -->
    <section class="hero-nature">
      <h1 class="hero-title">Page Title</h1>
      <p class="hero-subtitle">Subtitle or description</p>
    </section>

    <!-- Main Content -->
    <div
      style="max-width: 1400px; margin: 0 auto; padding: 3rem 1.5rem; position: relative; z-index: 1;"
    >
      <!-- Your content here -->
      <div class="nature-card">
        <h2>Welcome</h2>
        <p>Content goes here...</p>
      </div>
    </div>

    <script src="/assets/js/nature-animations.js"></script>
  </body>
</html>
```

### Dashboard Template

```html
<body>
  <!-- Sidebar Navigation -->
  <aside class="nature-sidebar">
    <!-- Sidebar content -->
  </aside>

  <!-- Main Content Area -->
  <main style="margin-left: 280px; min-height: 100vh;">
    <!-- Top Navbar -->
    <nav class="nature-navbar">
      <!-- Navbar content -->
    </nav>

    <!-- Dashboard Content -->
    <div style="padding: 2rem;">
      <!-- Stat Cards -->
      <div class="card-grid">
        <!-- Stat cards -->
      </div>

      <!-- More content... -->
    </div>
  </main>
</body>
```

---

## 🎯 Best Practices

### 1. Color Usage

- **Primary Actions**: Use `btn-primary` (green) for main actions
- **Highlighted Items**: Use `btn-gold` for featured/premium content
- **Neutral Actions**: Use `btn-earth` for secondary actions
- **Destructive Actions**: Use custom red styling for delete/remove

### 2. Spacing Consistency

- Use the spacing scale (`--space-1` to `--space-16`)
- Maintain consistent gaps in grids and flexbox layouts
- Use margin utilities (`mt-4`, `mb-6`, etc.)

### 3. Typography Hierarchy

- **Headings**: Use `<h1>` to `<h6>` with serif font (Playfair Display)
- **Body Text**: Use default sans-serif (Roboto)
- **Labels**: Use uppercase with letter-spacing for form labels

### 4. Animation Performance

- Animations automatically respect `prefers-reduced-motion`
- Use CSS transitions for simple effects
- Leverage JavaScript for complex animations

### 5. Accessibility

- Always include `alt` text for images
- Use semantic HTML (`<nav>`, `<main>`, `<aside>`)
- Ensure sufficient color contrast (4.5:1 for text)
- Test with keyboard navigation
- Provide `aria-label` for icon-only buttons

---

## ♿ Accessibility Features

### Built-in Support

- **Screen Readers**: `.sr-only` class for hidden labels
- **Focus Styles**: Gold outline on `:focus-visible`
- **High Contrast Mode**: Darker colors when `prefers-contrast: high`
- **Reduced Motion**: Animations disabled when `prefers-reduced-motion: reduce`
- **Keyboard Navigation**: All interactive elements accessible via Tab

### Example

```html
<button class="btn btn-primary" aria-label="Add new student">
  <i class="fas fa-plus" aria-hidden="true"></i>
  <span class="sr-only">Add new student</span>
</button>
```

---

## 🎨 Customization

### Changing Color Palette

Edit `:root` variables in `nature-theme.css`:

```css
:root {
  /* Change primary green */
  --nature-green-500: #4caf50; /* Replace with your color */

  /* Change gold accent */
  --gold-400: #ffd700; /* Replace with your color */
}
```

### Adding Custom Components

Create new classes following the naming convention:

```css
.custom-component {
  background: var(--white);
  border-radius: var(--radius-lg);
  padding: var(--space-4);
  box-shadow: var(--shadow-md);
  transition: all var(--transition-base);
}

.custom-component:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
}
```

---

## 🔧 Troubleshooting

### Issue: Animations not working

**Solution**: Ensure `nature-animations.js` is loaded after DOM content

```html
<script src="/assets/js/nature-animations.js"></script>
```

### Issue: Modal not opening

**Solution**: Check that modal has correct ID matching `data-modal-target`

```html
<button data-modal-target="myModal">Open</button>
<div class="modal-backdrop" id="myModal">...</div>
```

### Issue: Tabs not switching

**Solution**: Ensure tab buttons have `data-tab` matching panel IDs

```html
<button class="tab-button" data-tab="overview">Overview</button>
<div class="tab-panel" id="overview">Content</div>
```

### Issue: Cards not tilting on hover

**Solution**: Add `card-3d` class to enable 3D effect

```html
<div class="nature-card card-3d">...</div>
```

---

## 📚 Additional Resources

- **Showcase Page**: `/src/ui/nature-ui-showcase.html` - See all components in action
- **Font Awesome Icons**: https://fontawesome.com/icons
- **Google Fonts**: https://fonts.google.com/
- **WCAG Guidelines**: https://www.w3.org/WAI/WCAG21/quickref/

---

## 📞 Support

For questions or issues with the UI system:

- Email: support@sams-project.com
- Documentation: `/docs/ui-guide.md`
- GitHub: https://github.com/sams-project/ui-system

---

**Last Updated**: November 24, 2025
**Version**: 2.1.0
**Maintained by**: SAMS Development Team
