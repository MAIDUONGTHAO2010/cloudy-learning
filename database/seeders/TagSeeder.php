<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Programming
            'PHP',
            'Laravel',
            'JavaScript',
            'TypeScript',
            'Vue.js',
            'React',
            'Node.js',
            'Python',
            'Django',
            'FastAPI',
            'Java',
            'Spring Boot',
            'C#',
            '.NET',
            'Go',
            'Rust',
            'Swift',
            'Kotlin',
            'Flutter',
            'React Native',

            // Web & DevOps
            'HTML',
            'CSS',
            'Tailwind CSS',
            'REST API',
            'GraphQL',
            'Docker',
            'Kubernetes',
            'CI/CD',
            'Git',
            'Linux',
            'Nginx',
            'AWS',
            'Firebase',
            'Supabase',

            // Data & AI
            'Machine Learning',
            'Deep Learning',
            'Data Science',
            'TensorFlow',
            'PyTorch',
            'SQL',
            'PostgreSQL',
            'MySQL',
            'MongoDB',
            'Redis',

            // Design
            'UI/UX',
            'Figma',
            'Adobe XD',
            'Photoshop',
            'Illustrator',

            // Business & Soft skills
            'Excel',
            'Project Management',
            'Digital Marketing',
            'SEO',
            'Content Writing',

            // Languages
            'English',
            'Japanese',
            'Korean',
        ];

        foreach ($tags as $name) {
            Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
