<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Cloudy Learning | Learn Without Limits</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
        @php
            $courses = [
                [
                    'title' => 'Modern Laravel for Real Projects',
                    'level' => 'Intermediate',
                    'category' => 'Web Development',
                    'description' => 'Build production-ready apps with clean architecture, APIs, and scalable backend workflows.',
                    'rating' => 5,
                    'reviews' => 1284,
                    'instructor' => 'Mai Nguyen',
                    'lessons' => [
                        ['title' => 'Course introduction', 'is_free' => true],
                        ['title' => 'Routing and controller flow', 'is_free' => true],
                        ['title' => 'Repository pattern in practice', 'is_free' => false],
                        ['title' => 'Testing the purchase flow', 'is_free' => false],
                    ],
                    'comments' => [
                        ['name' => 'An Tran', 'role' => 'Student', 'message' => 'Very practical structure. I can follow every lesson and ship features faster.'],
                        ['name' => 'Lina Ho', 'role' => 'Instructor', 'message' => 'Great pacing and excellent examples for teaching backend concepts.'],
                    ],
                ],
                [
                    'title' => 'UI/UX Design for Learning Products',
                    'level' => 'Beginner',
                    'category' => 'Design',
                    'description' => 'Design intuitive course experiences, dashboards, and student journeys for digital learning apps.',
                    'rating' => 5,
                    'reviews' => 842,
                    'instructor' => 'Lan Pham',
                    'lessons' => [
                        ['title' => 'What makes learning interfaces effective', 'is_free' => true],
                        ['title' => 'Create a course landing page', 'is_free' => true],
                        ['title' => 'Accessible design systems', 'is_free' => false],
                        ['title' => 'Prototype instructor workflows', 'is_free' => false],
                    ],
                    'comments' => [
                        ['name' => 'Bao Le', 'role' => 'Student', 'message' => 'The examples feel modern and useful for freelance client work.'],
                        ['name' => 'Nhi Vu', 'role' => 'Student', 'message' => 'I loved how the lessons balance theory and hands-on practice.'],
                    ],
                ],
                [
                    'title' => 'Data Analytics Bootcamp',
                    'level' => 'Advanced',
                    'category' => 'Data Science',
                    'description' => 'Master dashboards, SQL thinking, and storytelling with data through business case studies.',
                    'rating' => 5,
                    'reviews' => 967,
                    'instructor' => 'Quoc Bui',
                    'lessons' => [
                        ['title' => 'Analytics mindset and foundations', 'is_free' => true],
                        ['title' => 'SQL for analysis', 'is_free' => false],
                        ['title' => 'Dashboard design', 'is_free' => false],
                        ['title' => 'Present insights with confidence', 'is_free' => true],
                    ],
                    'comments' => [
                        ['name' => 'Hana Do', 'role' => 'Instructor', 'message' => 'Strong curriculum for learners who want both depth and business context.'],
                        ['name' => 'Khoa Truong', 'role' => 'Student', 'message' => 'The free lessons helped me decide quickly, and the course quality is excellent.'],
                    ],
                ],
            ];
        @endphp

        <div class="relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 -z-10 h-[38rem] bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.24),_transparent_45%),radial-gradient(circle_at_20%_20%,_rgba(139,92,246,0.24),_transparent_35%),linear-gradient(180deg,_#020617_0%,_#0f172a_100%)]"></div>

            <header class="sticky top-0 z-20 border-b border-white/10 bg-slate-950/75 backdrop-blur-xl">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan-400/15 text-lg font-extrabold text-cyan-300 ring-1 ring-cyan-300/20">CL</div>
                        <div>
                            <p class="text-lg font-bold tracking-tight">Cloudy Learning</p>
                            <p class="text-sm text-slate-400">E-learning platform for students & instructors</p>
                        </div>
                    </div>

                    <nav class="hidden items-center gap-8 text-sm text-slate-300 md:flex">
                        <a href="#courses" class="transition hover:text-white">Courses</a>
                        <a href="#lessons" class="transition hover:text-white">Lessons</a>
                        <a href="#reviews" class="transition hover:text-white">Reviews</a>
                        <a href="#contact" class="transition hover:text-white">Contact</a>
                    </nav>

                    <div class="flex items-center gap-3">
                        <button type="button" class="rounded-full border border-white/15 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-cyan-300/40 hover:bg-white/5">Login</button>
                        <button type="button" class="rounded-full bg-cyan-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300">Register</button>
                    </div>
                </div>
            </header>

            <main>
                <section class="mx-auto grid max-w-7xl gap-14 px-6 py-16 lg:grid-cols-[1.15fr_0.85fr] lg:px-8 lg:py-24">
                    <div class="max-w-3xl">
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-4 py-2 text-sm text-cyan-200">
                            <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                            Learn, teach, and grow with flexible online courses
                        </div>
                        <h1 class="text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-7xl">Build your future with engaging online lessons.</h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                            Discover curated e-learning experiences for students and instructors. Explore premium courses, preview free lessons, check 5-star reviews, and join a learning community designed to help you stay consistent.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-4">
                            <button type="button" class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-100">Start Learning</button>
                            <button type="button" class="rounded-full border border-white/15 px-6 py-3 text-sm font-semibold text-white transition hover:border-white/30 hover:bg-white/5">Become an Instructor</button>
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-2xl shadow-slate-950/30 backdrop-blur">
                                <p class="text-3xl font-black text-white">120+</p>
                                <p class="mt-2 text-sm text-slate-400">Structured courses across tech, business, design, and analytics.</p>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-2xl shadow-slate-950/30 backdrop-blur">
                                <p class="text-3xl font-black text-white">5★</p>
                                <p class="mt-2 text-sm text-slate-400">Top-rated learning journeys with feedback from active learners.</p>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-2xl shadow-slate-950/30 backdrop-blur">
                                <p class="text-3xl font-black text-white">24/7</p>
                                <p class="mt-2 text-sm text-slate-400">Learn anytime with free previews and instructor-ready course pages.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-cyan-950/20 backdrop-blur">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-cyan-300">Featured Course</p>
                                <h2 class="mt-2 text-2xl font-bold text-white">Modern Laravel for Real Projects</h2>
                            </div>
                            <span class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-300">New</span>
                        </div>

                        <div class="mt-6 rounded-3xl bg-gradient-to-br from-cyan-400 via-sky-500 to-violet-500 p-[1px]">
                            <div class="rounded-3xl bg-slate-950/90 p-6">
                                <div class="flex items-center justify-between text-sm text-slate-300">
                                    <span>Instructor: Mai Nguyen</span>
                                    <span>Intermediate</span>
                                </div>
                                <p class="mt-4 text-slate-300">Build robust APIs, admin dashboards, and scalable repository patterns from a single course page.</p>
                                <div class="mt-6 flex items-center gap-3">
                                    <div class="flex text-amber-300">
                                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                    </div>
                                    <span class="text-sm text-slate-400">5.0 • 1,284 reviews</span>
                                </div>
                                <div class="mt-6 space-y-3">
                                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                        <span class="text-sm text-white">Lesson 1: Course introduction</span>
                                        <span class="rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-semibold text-emerald-300">Free</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                        <span class="text-sm text-white">Lesson 2: Repository pattern in practice</span>
                                        <span class="rounded-full bg-violet-400/15 px-3 py-1 text-xs font-semibold text-violet-300">Premium</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                        <span class="text-sm text-white">Lesson 3: Testing the purchase flow</span>
                                        <span class="rounded-full bg-violet-400/15 px-3 py-1 text-xs font-semibold text-violet-300">Premium</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="courses" class="mx-auto max-w-7xl px-6 py-8 lg:px-8 lg:py-12">
                    <div class="mb-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-cyan-300">Course Library</p>
                            <h2 class="mt-3 text-3xl font-black tracking-tight text-white sm:text-4xl">Designed for modern students and instructors</h2>
                        </div>
                        <p class="max-w-2xl text-slate-400">Each course includes a high-level summary, lesson preview list, 5-star rating, and learner comments so your future logic can hook into a realistic UI.</p>
                    </div>

                    <div class="mb-6 flex flex-wrap gap-3 text-sm text-slate-300">
                        <span id="lessons" class="rounded-full border border-white/10 bg-white/5 px-4 py-2">Lesson previews included</span>
                        <span id="reviews" class="rounded-full border border-white/10 bg-white/5 px-4 py-2">5-star reviews and comments</span>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-3">
                        @foreach ($courses as $course)
                            <article class="rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-2xl shadow-slate-950/30">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <span class="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-200">{{ $course['category'] }}</span>
                                        <h3 class="mt-4 text-2xl font-bold text-white">{{ $course['title'] }}</h3>
                                    </div>
                                    <span class="rounded-full border border-white/10 px-3 py-1 text-xs font-medium text-slate-300">{{ $course['level'] }}</span>
                                </div>

                                <p class="mt-4 text-sm leading-7 text-slate-300">{{ $course['description'] }}</p>

                                <div class="mt-5 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-slate-400">Instructor</p>
                                        <p class="font-semibold text-white">{{ $course['instructor'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex justify-end text-amber-300">
                                            @for ($i = 0; $i < $course['rating']; $i++)
                                                <span>★</span>
                                            @endfor
                                        </div>
                                        <p class="text-sm text-slate-400">{{ number_format($course['reviews']) }} reviews</p>
                                    </div>
                                </div>

                                <div class="mt-6 rounded-3xl border border-white/10 bg-slate-950/60 p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h4 class="font-semibold text-white">Lesson preview</h4>
                                        <span class="text-xs uppercase tracking-wide text-slate-400">{{ count($course['lessons']) }} lessons</span>
                                    </div>
                                    <div class="space-y-3">
                                        @foreach ($course['lessons'] as $lesson)
                                            <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                                <p class="text-sm text-slate-200">{{ $lesson['title'] }}</p>
                                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $lesson['is_free'] ? 'bg-emerald-400/15 text-emerald-300' : 'bg-violet-400/15 text-violet-300' }}">
                                                    {{ $lesson['is_free'] ? 'Free' : 'Premium' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-6 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-semibold text-white">Comments</h4>
                                        <span class="text-sm text-slate-400">Recent feedback</span>
                                    </div>
                                    @foreach ($course['comments'] as $comment)
                                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="font-medium text-white">{{ $comment['name'] }}</p>
                                                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ $comment['role'] }}</p>
                                                </div>
                                                <div class="flex text-amber-300">
                                                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                                </div>
                                            </div>
                                            <p class="mt-3 text-sm leading-6 text-slate-300">{{ $comment['message'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="mx-auto max-w-7xl px-6 py-8 lg:px-8 lg:py-14">
                    <div class="grid gap-6 rounded-[2rem] border border-white/10 bg-gradient-to-r from-slate-900 to-slate-900/70 p-8 lg:grid-cols-3">
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-cyan-300">For Students</p>
                            <h3 class="mt-4 text-2xl font-bold text-white">Learn with clarity and momentum</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-300">Browse structured courses, check which lessons are free, and see course quality through comments and ratings before you enroll.</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-violet-300">For Instructors</p>
                            <h3 class="mt-4 text-2xl font-bold text-white">Showcase expertise beautifully</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-300">Present your courses with lesson previews, reputation signals, and engaging course cards that can later connect to real data.</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-300">Platform Ready</p>
                            <h3 class="mt-4 text-2xl font-bold text-white">Clean UI, no logic yet</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-300">This page is intentionally static so you can wire authentication, courses, lessons, comments, and enrollment in your next tasks.</p>
                        </div>
                    </div>
                </section>
            </main>

            <footer id="contact" class="border-t border-white/10 bg-slate-950/90">
                <div class="mx-auto grid max-w-7xl gap-10 px-6 py-12 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
                    <div>
                        <p class="text-lg font-bold text-white">Cloudy Learning</p>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-400">A modern e-learning home page for students and instructors. Use this layout as the foundation for your real course data, lesson access rules, comments, and future authentication flow.</p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-cyan-300">Contact</p>
                            <ul class="mt-4 space-y-3 text-sm text-slate-300">
                                <li>Email: hello@cloudylearning.dev</li>
                                <li>Phone: +84 987 654 321</li>
                                <li>Address: 12 Nguyen Hue, Ho Chi Minh City</li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-cyan-300">Support Hours</p>
                            <ul class="mt-4 space-y-3 text-sm text-slate-300">
                                <li>Mon - Fri: 8:00 AM - 6:00 PM</li>
                                <li>Sat: 9:00 AM - 1:00 PM</li>
                                <li>Response time: under 24 hours</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
