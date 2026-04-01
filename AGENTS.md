# SOTO APPS Development Guidelines
**Laravel (Backend) + React TypeScript (Frontend)**

This document defines the development standards and architectural rules for the SOTO APPS automated letter system.

**Primary Goals:**
- Consistency
- Maintainability
- Scalability
- Clean Architecture
- DRY Principle (Don't Repeat Yourself)

---

## 1. Global Naming Convention

### 1.1 File Naming Rule (Mandatory)

All project files **MUST** use:

✅ **CapitalCase (PascalCase)**

This rule applies to:
- React components
- Services
- Hooks
- Context files
- Laravel controllers
- Laravel services
- Laravel actions
- DTOs
- Jobs
- Events
- Policies

**✅ Correct Examples**
```
CreateLetterService.ts
LetterController.php
GeneratePdfAction.php
LetterForm.tsx
LetterRepository.php
AuthMiddleware.php
```

**❌ Incorrect Examples**
```
createLetterService.ts
letter_controller.php
generate-pdf-action.php
letter-form.tsx
```

### 1.2 Naming Rules

| Element | Convention |
|---|---|
| Class | CapitalCase |
| Interface (TS) | CapitalCase (no prefix "I") |
| Enum | CapitalCase |
| Variable | camelCase |
| Function | camelCase |
| Database Table | snake_case |
| Database Column | snake_case |
| Migration File | snake_case |
| Route Name | dot.notation |

---

## 2. Frontend Architecture (React + TypeScript)

The frontend **MUST** follow a clean, feature-friendly structure that is easy to navigate and scale.

### 2.1 Folder Structure

Frontend React TypeScript akan ditempatkan di `resources/js/`.

```
resources/js/
│
├── assets/          # Static files (images, fonts, icons)
│
├── components/      # Shared reusable UI components (buttons, inputs, modals, etc.)
│
├── container/       # Page-level components — each file represents a full page/route
│
├── templates/       # HTML templates used for PDF generation
│
├── hooks/           # Shared custom React hooks
│
├── context/         # React Context definitions and providers
│
├── services/        # All API call logic, isolated from components
│
├── lib/             # Third-party library configurations (axios instance, etc.)
│
├── utils/           # Shared utility/helper functions
│
└── main.tsx
```

**Rules:**
- `container/` holds page-level components and acts as the entry point for each route
- `components/` must be reusable and stateless whenever possible
- `services/` is the **only** place where API calls are made — never inside components directly
- `templates/` stores HTML structures specifically for PDF rendering — keep it separate from UI components
- Do not duplicate logic across folders — if it appears twice, extract it

### 2.2 Component Standards

- Use **Functional Components** only
- Always define prop types
- Extract business logic into hooks or services
- Avoid inline complex logic inside JSX
- Use composition over inheritance

**Example:**
```tsx
type LetterFormProps = {
  onSubmit: (data: LetterPayload) => void
}

export const LetterForm = ({ onSubmit }: LetterFormProps) => {
  // logic here
}
```

### 2.3 State Management

- Use **Context API** for global state
- Use local state for UI-only logic
- Persist important data using custom hooks (e.g., `useLocalStorage`)
- Avoid prop drilling
- Prefer derived state over duplicated state

### 2.4 API Layer

All API calls **MUST** be isolated inside:

```
src/services/
```

**Never call APIs directly inside components.**

**Example:**
```
services/LetterService.ts
services/UserService.ts
```

### 2.5 Coding Conventions

- Keep components small and colocate minimal logic; defer to services.
- Use functional components and hooks only.
- Prefer Tailwind utility classes; avoid custom CSS unless necessary.
- Use `fetch` with simple helpers; abort overengineering.
- Exports: default for components, named for utilities.
- Use `@/` alias for imports from `src` (see `vite.config.js`).
- Do not use Vite dev server proxy; call services directly via `fetch` to `VITE_*` base URLs.

---

## 3. Frontend Color Palette

The system must use a consistent 4-color palette derived from the design.

| Name | Hex | Usage |
|---|---|---|
| Primary Blue | `#2E3A8C` | Main header background, primary buttons |
| Dark Navy | `#1F2A66` | Hover state, active navigation |
| Accent Yellow | `#F4C430` | Highlight elements, badges, underline separator |
| Teal / Green Accent | `#1ABC9C` | Secondary actions, icon highlight, status indicator |

**Rules:**
- Define colors in a centralized theme file
- **Never hardcode** colors directly inside components
- Use semantic naming: `primary`, `primaryHover`, `accent`, `success`, `warning`

---

## 4. Backend Architecture (Laravel Best Practices)

The backend must follow clean architecture principles with a clear separation of concerns.

### 4.1 Controller Rules

Controllers **MUST:**
- Be thin
- Only handle the HTTP layer
- Delegate logic to Services or Actions
- **Never** contain business logic

**Example:**
```php
public function store(StoreLetterRequest $request)
{
    $letter = $this->createLetterService->execute($request->validated());

    return new LetterResource($letter);
}
```

### 4.2 Service Layer

All business logic must live in `app/Services/`.

**Rules:**
- One responsibility per service
- Injectable via constructor
- Reusable and testable

## 5. DRY Principle (Mandatory)

Across the entire project:

- Never duplicate business logic
- Extract reusable functions
- Extract shared UI components
- Use helpers wisely
- Avoid repeated query logic

> **If logic appears twice → refactor.**

---

## 6. Security Guidelines

- Always use authorization policies
- Never trust frontend validation alone
- Sanitize file uploads
- Use Laravel Sanctum or JWT properly
- Protect sensitive routes
- Never expose internal errors in production

---

## 7. Code Quality Rules

- Write readable code first
- Avoid over-engineering
- Keep functions small and focused
- Maximum single responsibility per class
- Follow **PSR-12** (Laravel)
- Follow **ESLint + Prettier** (Frontend)

---

## 8. Commit Standard

Use conventional commits:

```
feat: add letter generation service
fix: correct pdf formatting issue
refactor: extract letter validation logic
docs: update agent guideline
```

---

## 9. Final Principle

This system is designed to be:
**Modular | Clean | Scalable | Maintainable**

Every new feature must respect:
- DRY
- Separation of Concerns
- Stable Naming Convention
- Modular Architecture
