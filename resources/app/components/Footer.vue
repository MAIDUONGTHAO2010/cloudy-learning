<template>
    <footer class="bg-[#0f1936] px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <h4 class="text-base font-bold text-white">Cloudy Learning</h4>
                    <p class="mt-3 text-sm leading-7 text-blue-100/60">A smart online learning platform for students and instructors.</p>
                </div>
                <div>
                    <h4 class="text-base font-bold text-white">Quick Links</h4>
                    <ul class="mt-3 space-y-2 text-sm text-blue-100/60">
                        <li><RouterLink to="/courses" class="transition hover:text-orange-400">Browse Courses</RouterLink></li>
                        <li><RouterLink to="/register" class="transition hover:text-orange-400">Register</RouterLink></li>
                        <li><RouterLink to="/login" class="transition hover:text-orange-400">Sign In</RouterLink></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-base font-bold text-white">Popular Categories</h4>
                    <ul class="mt-3 space-y-2 text-sm text-blue-100/60">
                        <li class="transition hover:text-orange-400 cursor-default">Development</li>
                        <li class="transition hover:text-orange-400 cursor-default">Design</li>
                        <li class="transition hover:text-orange-400 cursor-default">Data Science</li>
                        <li class="transition hover:text-orange-400 cursor-default">Marketing</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-base font-bold text-white">Contact</h4>
                    <p class="mt-3 text-sm leading-7 text-blue-100/60">Have questions? Reach us at<br/><a href="mailto:hello@cloudylearning.com" class="text-orange-400 hover:underline">hello@cloudylearning.com</a></p>
                </div>
            </div>
            <div class="mt-12 border-t border-white/10 pt-6 text-center text-sm text-blue-100/40">
                © {{ new Date().getFullYear() }} Cloudy Learning. All rights reserved.
            </div>
        </div>
    </footer>
</template>
