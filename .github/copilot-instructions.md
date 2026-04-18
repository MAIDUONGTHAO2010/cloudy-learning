# Feature Development Guide

Each feature follows a 4-layer architecture. The flow is:

```
HTTP Request → FormRequest (validate) → Controller → Service → Repository (Eloquent) → DB
```

---

## 1. Form Request — validate input

**Location:** `app/Http/Requests/Admin/{Feature}/`

```php
<?php

namespace App\Http\Requests\Admin\Course;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // add policy check here if needed
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'user_id'     => 'required|exists:users,id',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'tags'        => 'nullable|array',
            'tags.*'      => 'string|max:50',
        ];
    }
}
```

---

## 2. Controller — receive request, return response

**Location:** `app/Http/Controllers/Admin/`

- Type-hint the `FormRequest` subclass (not `Request`) — Laravel resolves and validates automatically.
- Inject the **Service** via the constructor.
- Keep controllers thin: delegate all logic to the service.

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Course\StoreCourseRequest;
use App\Http\Requests\Admin\Course\UpdateCourseRequest;
use App\Services\CourseService;

class CourseController extends Controller
{
    public function __construct(protected CourseService $courseService) {}

    public function index()
    {
        return response()->json($this->courseService->list());
    }

    public function store(StoreCourseRequest $request)
    {
        $course = $this->courseService->create($request->validated());

        return response()->json($course, 201);
    }

    public function update(UpdateCourseRequest $request, int $id)
    {
        $course = $this->courseService->update($id, $request->validated());

        return response()->json($course);
    }

    public function destroy(int $id)
    {
        $this->courseService->delete($id);

        return response()->json(['message' => 'Deleted successfully']);
    }
}
```

---

## 3. Service — business logic

**Location:** `app/Services/`

- Inject the **Repository interface** (not the Eloquent class directly).
- All business logic lives here: slug generation, tag syncing, authorization checks, event dispatching, etc.
- Returns data (model / collection / array) back to the controller.

```php
<?php

namespace App\Services;

use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Support\Str;

class CourseService
{
    public function __construct(protected CourseRepositoryInterface $courseRepository) {}

    public function list()
    {
        return $this->courseRepository->getAllWithRelations();
    }

    public function create(array $data): mixed
    {
        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']));

        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $course = $this->courseRepository->create($data);
        $course->tags()->sync($this->resolveTagIds($tags));

        return $course->load(['instructor:id,name', 'category:id,name', 'tags:id,name,slug']);
    }

    public function update(int $id, array $data): mixed
    {
        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']), $id);

        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $course = $this->courseRepository->update($data, $id);
        $course->tags()->sync($this->resolveTagIds($tags));

        return $course->load(['instructor:id,name', 'category:id,name', 'tags:id,name,slug']);
    }

    public function delete(int $id): void
    {
        $this->courseRepository->delete($id);
    }

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        // ... slug uniqueness logic
    }

    private function resolveTagIds(array $tagNames): array
    {
        // ... find-or-create tags and return their IDs
    }
}
```

---

## 4. Repository — query the database

### 4a. Interface

**Location:** `app/Repositories/Contracts/`

Declare only the methods specific to this feature. CRUD methods (`create`, `update`, `delete`, `find`, `all`) are inherited from `RepositoryInterface` via `prettus/l5-repository`.

```php
<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

interface CourseRepositoryInterface extends RepositoryInterface
{
    public function getAllWithRelations();
}
```

### 4b. Eloquent implementation

**Location:** `app/Repositories/`

```php
<?php

namespace App\Repositories;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

class CourseRepositoryEloquent extends BaseRepository implements CourseRepositoryInterface
{
    public function model(): string
    {
        return Course::class;
    }

    public function getAllWithRelations()
    {
        return Course::with(['instructor:id,name', 'category:id,name', 'tags:id,name,slug'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('order')
            ->get();
    }
}
```

---

## 5. Bind interface → implementation

**Location:** `app/Providers/AppServiceProvider.php`

Add one line in the `register()` method for every new repository:

```php
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\CourseRepositoryEloquent;

public function register(): void
{
    // existing bindings ...
    $this->app->bind(CourseRepositoryInterface::class, CourseRepositoryEloquent::class);
}
```

---

## File checklist for a new feature

| # | File | Namespace |
|---|------|-----------|
| 1 | `app/Http/Requests/Admin/{Feature}/Store{Feature}Request.php` | `App\Http\Requests\Admin\{Feature}` |
| 1 | `app/Http/Requests/Admin/{Feature}/Update{Feature}Request.php` | `App\Http\Requests\Admin\{Feature}` |
| 2 | `app/Http/Controllers/Admin/{Feature}Controller.php` | `App\Http\Controllers\Admin` |
| 3 | `app/Services/{Feature}Service.php` | `App\Services` |
| 4 | `app/Repositories/Contracts/{Feature}RepositoryInterface.php` | `App\Repositories\Contracts` |
| 4 | `app/Repositories/{Feature}RepositoryEloquent.php` | `App\Repositories` |
| 5 | `app/Providers/AppServiceProvider.php` | bind interface → eloquent |
