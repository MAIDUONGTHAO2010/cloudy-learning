# Cloudy Learning — User Site Documentation

This document describes every page and feature available to end-users (students and instructors) on the Cloudy Learning platform.

---

## Table of Contents

1. [Overview](#1-overview)
2. [Navigation](#2-navigation)
3. [Home Page (`/`)](#3-home-page-)
4. [Courses Page (`/courses`)](#4-courses-page-courses)
5. [Course Detail Page (`/courses/:slug`)](#5-course-detail-page-coursesslug)
6. [Learn Course Page (`/learn/:slug`)](#6-learn-course-page-learnslug)
7. [Dashboard — Student (`/dashboard`)](#7-dashboard--student-dashboard)
8. [Dashboard — Instructor (`/my-courses`)](#8-dashboard--instructor-my-courses)
9. [Profile Page (`/profile`)](#9-profile-page-profile)
10. [About Page (`/about`)](#10-about-page-about)
11. [Contact Page (`/contact`)](#11-contact-page-contact)
12. [Privacy Policy (`/policy`)](#12-privacy-policy-policy)
13. [Authentication](#13-authentication)
14. [Notifications](#14-notifications)
15. [Multi-language Support](#15-multi-language-support)

---

## 1. Overview

Cloudy Learning is a Vue 3 + TypeScript SPA (Single Page Application) served under `/` by Laravel. All data is fetched via a RESTful JSON API at `/api/*`. Tailwind CSS is used for all styling.

**Tech stack (frontend):**
- Vue 3 (Composition API, `<script setup>`)
- Vue Router 4
- Vue I18n 9
- Axios
- Tailwind CSS

**Roles:**
| Role ID | Name | Access |
|---------|------|--------|
| `1` | Student | Dashboard, enrolled courses, reviews |
| `2` | Instructor | My Courses management portal |
| `0` / Admin | Admin | Separate admin panel at `/admin` |

---

## 2. Navigation

The sticky top navigation bar (`Navbar.vue`) is present on every user-facing page.

### Desktop (`≥ 768 px`)
- **Logo** — links to Home (`/`)
- **Home** — links to `/`
- **Courses** — links to `/courses`
- **About** — links to `/about`
- **Contact** — links to `/contact`
- **My Learning** *(students only)* — links to `/dashboard`
- **My Courses** *(instructors only)* — links to `/my-courses`
- **Language Switcher** — English / Tiếng Việt / 日本語
- **Notifications bell** *(authenticated)* — dropdown list with mark-as-read
- **Profile button** *(authenticated)* — links to `/profile`
- **Sign out** *(authenticated)*
- **Sign in / Get started** *(guests)*

### Mobile (`< 768 px`)
- Logo + Language Switcher are always visible
- **Hamburger button** (☰) opens a full-width slide-down drawer containing all nav links and auth actions
- The drawer closes when a link is tapped or when the backdrop is clicked

---

## 3. Home Page (`/`)

**File:** `resources/app/pages/Home.vue`

### Sections

#### Hero
- Animated eyebrow label cycling through 4 slides (auto-advances every 5 s)
- Main headline and subtitle from the active slide
- **Search bar** — searches courses by keyword, navigates to `/courses?search=…`
- Feature chips: Flexible schedule · Hands-on lessons · Practical guidance

#### Stats Bar
Live stats pulled from the API:
- Total active courses
- Total active learners
- Total instructors

#### Start Your Journey
- Feature cards (project-based learning, flexible schedule, quality instructors)
- CTAs: Browse Courses / Sign up free (guests) or Continue Learning (authenticated)

#### Popular Courses
- Grid of top-rated courses (`GET /api/courses/popular`)
- Skeleton loading state
- Links to individual course detail pages

#### Why Choose Us
- Repeats feature cards in a centred 3-column grid

#### Top Instructors
- Grid of instructor cards with avatar, name, course count, average rating
- "View courses" button filters `/courses?instructor_id=…`

#### Newest Courses
- Grid of most recently published courses (`GET /api/courses/newest`)

#### CTA Banner
- Prompts guests to register; authenticated users to go to their dashboard

---

## 4. Courses Page (`/courses`)

**File:** `resources/app/pages/Courses.vue`

Paginated, filterable list of all published courses.

### Filter Sidebar
On **desktop** the sidebar is always visible on the left (`w-64`).  
On **mobile** the sidebar is hidden behind a **"Filters" toggle button** that expands/collapses it above the course grid.

| Filter | API param |
|--------|-----------|
| Keyword search (debounced 350 ms) | `search` |
| Category | `category_id` |
| Instructor | `instructor_id` |
| Tag | `tag` (slug) |

A **Clear all filters** button appears when any filter is active.

### Course Grid
- Responsive: 1 column (xs) → 2 columns (sm) → 3 columns (xl)
- Each card shows: thumbnail, category badge, title, instructor name, tags, lesson count, star rating
- Clicking a card navigates to `/courses/:slug`

### Pagination
Previous / Next buttons with "Page X of Y" indicator.

---

## 5. Course Detail Page (`/courses/:slug`)

**File:** `resources/app/pages/CourseDetail.vue`

Fetches course data from `GET /api/courses/:slug`.

### Sections
- **Breadcrumb:** Courses > Course title
- **Hero card:** thumbnail/placeholder, category + tag badges, title, instructor, lesson count, average rating, description
- **Enrollment actions:**
  - Guest → Sign in prompt
  - Not enrolled → "Enroll now" button (sends `POST /api/courses/:id/enroll`)
  - Pending → "Request pending" badge with cancel option
  - Approved → "Start learning" button → `/learn/:slug`
  - Cancelled → "Send request again" button
  - Instructor role → "Go to My Courses"
- **Lesson list:** all lessons with lock/unlock indicator; first lesson is always free to preview
- **Reviews section:** star rating distribution, list of reviews, write/edit/delete own review (approved students only)

---

## 6. Learn Course Page (`/learn/:slug`)

**File:** `resources/app/pages/LearnCourse.vue`

Full-screen learning environment (Navbar is hidden; dedicated back link to course detail).

### Layout
- **Left sidebar:** collapsible lesson list with progress indicators
- **Main area:** video player (if `video_url` exists) + lesson title/description
- **Quiz panel:** appears after video for lessons that have a quiz attached

### Progress Tracking
Lesson completion is recorded via `POST /api/progress/complete` when a student marks a lesson done.

### Quiz
- Multiple choice / true-false / fill-in-blank question types
- `POST /api/quiz/:quizId/attempt` to submit answers
- Score and correct answers displayed after submission

---

## 7. Dashboard — Student (`/dashboard`)

**File:** `resources/app/pages/Dashboard.vue`  
**Auth required:** yes (`requiresAuth: true`)

### Tabs / Sections
- **My Courses** — list of courses the student is enrolled in (approved, pending, cancelled)
- **Available Courses** — browseable course cards with quick-enroll

### Enrollment Status Badges
| Status | Badge |
|--------|-------|
| Approved | Green "Approved" |
| Pending | Yellow "Pending" |
| Cancelled | Red "Cancelled" + reason |

---

## 8. Dashboard — Instructor (`/my-courses`)

**File:** `resources/app/pages/InstructorCourses.vue`  
**Auth required:** yes, instructor role only

### Features
- List of courses created by the logged-in instructor
- **Create course** button → `/my-courses/create`
- **Edit** button → `/my-courses/:courseId/edit`
- **Enrollment Requests** panel: approve or cancel pending student requests (with optional cancellation reason)

### Course Editor (`/my-courses/:courseId/edit`)
**File:** `resources/app/pages/InstructorCourseEditor.vue`

- Edit course title, description, category, tags, thumbnail (S3 presigned upload)
- Manage lessons: create, edit, reorder (drag handles), presigned video upload
- Attach / detach quizzes per lesson

### Create Course (`/my-courses/create`)
**File:** `resources/app/pages/InstructorCreateCourse.vue`

Wizard-style form to create a new course draft.

---

## 9. Profile Page (`/profile`)

**File:** `resources/app/pages/Profile.vue`  
**Auth required:** yes

Editable fields:
- Full name
- Password (optional — leave blank to keep current)
- Date of birth
- Gender
- Bio
- Interests (up to 3 categories, multi-select)

Changes are saved via `PUT /api/profile`.

---

## 10. About Page (`/about`)

**File:** `resources/app/pages/About.vue`

Static marketing page:
- Mission & Vision statements
- Platform statistics (50K+ learners, 500+ courses, etc.)
- Core values grid (Accessibility, Quality, Community, Innovation, Diversity, Trust)
- CTA banner linking to `/register`

---

## 11. Contact Page (`/contact`)

**File:** `resources/app/pages/Contact.vue`

- Contact form: name, email, subject (dropdown topics), message
- Topics include general inquiries, course questions, technical support, billing, becoming an instructor, custom software development
- Form submits to `POST /api/contact`
- Custom software development service callout section

---

## 12. Privacy Policy (`/policy`)

**File:** `resources/app/pages/Policy.vue`

Static legal page covering:
1. Information We Collect
2. How We Use Your Information
3. Information Sharing
4. Cookies
5. Data Security
6. Data Retention
7. Your Rights
8. Children's Privacy
9. Changes to This Policy

---

## 13. Authentication

### Register (`/register`)
**File:** `resources/app/pages/Register.vue`

Fields: Full name · Email · Password · Confirm password · Role (Student / Instructor) · Date of birth · Gender · Interests

`POST /api/auth/register`

### Login (`/login`)
**File:** `resources/app/pages/Login.vue`

Fields: Email · Password

`POST /api/auth/login` → stores auth token via cookie / Sanctum session.

### Guest guards
Routes with `meta: { guest: true }` redirect authenticated users to their dashboard.

### Auth guards
Routes with `meta: { requiresAuth: true }` redirect guests to `/login`.  
Routes with `meta: { instructorOnly: true }` redirect non-instructors to `/dashboard`.

---

## 14. Notifications

Accessible via the bell icon in the navbar (authenticated users only).

| Action | Endpoint |
|--------|----------|
| Fetch list | `GET /api/notifications` |
| Fetch unread count | `GET /api/notifications/unread-count` |
| Mark one as read | `PUT /api/notifications/:id/read` |
| Mark all as read | `PUT /api/notifications/read-all` |

The dropdown shows the 20 most recent notifications. Unread notifications have a blue dot indicator; the bell badge shows the unread count (capped at "9+").

---

## 15. Multi-language Support

Implemented with **vue-i18n**. Three locales are available:

| Code | Language |
|------|----------|
| `en` | English (default) |
| `vi` | Tiếng Việt |
| `ja` | 日本語 |

The **Language Switcher** component persists the selected language in `localStorage` under the key `lang`. All UI strings are in `resources/locales/{en,vi,ja}.ts`.

To add a new locale:
1. Create `resources/locales/{code}.ts` and export a translations object matching the shape of `en.ts`.
2. Register it in `resources/i18n.ts`.
3. Add the new option to `LanguageSwitcher.vue`.
