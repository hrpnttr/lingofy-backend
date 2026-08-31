<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Students;
use App\Models\Courses;
use App\Models\Classes;
use App\Models\Quizzes;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create a Default Admin User
        if (User::count() === 0) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'level' => 'advanced',
            ]);
        }

        // 2. Create a Default Student User
        if (Students::count() === 0) {
            Students::create([
                'full_name' => 'John Doe',
                'phone' => '1234567890',
                'email' => 'student@example.com',
                'password' => hash('sha256', 'password'),
            ]);
        }

        // 3. Create Courses, Classes, and Quizzes
        if (Courses::count() === 0) {
            $english = Courses::create([
                'title' => 'English Beginner Course',
                'description' => 'Learn the basics of English language, including vocabulary, greetings, and daily conversations.',
                'level' => 'beginner',
                'language' => 'English',
                'price' => 0.00,
                'durationHours' => 10,
                'tags' => ['english', 'beginner', 'basics'],
                'published' => true,
            ]);

            $german = Courses::create([
                'title' => 'German Beginner Course',
                'description' => 'Start speaking German today. Learn German alphabet, nouns, articles, and basic sentence construction.',
                'level' => 'beginner',
                'language' => 'German',
                'price' => 0.00,
                'durationHours' => 12,
                'tags' => ['german', 'a1', 'greetings'],
                'published' => true,
            ]);

            $turkish = Courses::create([
                'title' => 'Turkish Beginner Course',
                'description' => 'Learn Turkish language from scratch. Cover basic grammar, common verbs, and conversational structures.',
                'level' => 'beginner',
                'language' => 'Turkish',
                'price' => 0.00,
                'durationHours' => 15,
                'tags' => ['turkish', 'beginner', 'conversational'],
                'published' => true,
            ]);

            $spanish = Courses::create([
                'title' => 'Spanish Beginner Course',
                'description' => 'Introduce yourself to Spanish. Master pronouns, standard adjectives, and primary verbs in Spanish.',
                'level' => 'beginner',
                'language' => 'Spanish',
                'price' => 0.00,
                'durationHours' => 8,
                'tags' => ['spanish', 'esp', 'basics'],
                'published' => true,
            ]);

            // 4. Create Classes with Materials
            $engClass = Classes::create([
                'course_id' => $english->id,
                'class_name' => 'English Basics and Introduction',
                'description' => 'This lesson covers basic English greetings, personal pronouns, and how to introduce yourself to others.',
                'materials' => [
                    [
                        'title' => 'Video Lesson - Greetings',
                        'type' => 'video',
                        'fileName' => 'Bring-Me-The-Horizon-Shadow-Moses-Official-Video.mp4',
                    ],
                    [
                        'title' => 'PDF Module - Introductions',
                        'type' => 'pdf',
                        'fileName' => 'john_doe.pdf',
                    ],
                    [
                        'title' => 'Practice Quiz - Basic English',
                        'type' => 'quiz',
                    ]
                ],
            ]);

            $gerClass = Classes::create([
                'course_id' => $german->id,
                'class_name' => 'German Greetings and Alphabet',
                'description' => 'Learn the German alphabet, vowels, consonants, and common greetings like Hallo, Guten Morgen, and Tschüss.',
                'materials' => [
                    [
                        'title' => 'Video Lesson - German Alphabet',
                        'type' => 'video',
                        'fileName' => 'Let-you-break-my-heart-again-Laufey-Cover-by-Sally_1080pFHR.mp4',
                    ],
                    [
                        'title' => 'PDF Module - Alphabet & Greetings',
                        'type' => 'pdf',
                        'fileName' => 'aditya farid riyan wijaya.pdf',
                    ],
                    [
                        'title' => 'Practice Quiz - German Greetings',
                        'type' => 'quiz',
                    ]
                ],
            ]);

            $turClass = Classes::create([
                'course_id' => $turkish->id,
                'class_name' => 'Introduction to Turkish Verbs',
                'description' => 'An entry class covering simple verb structures and vocabulary in Turkish.',
                'materials' => [
                    [
                        'title' => 'PDF Module - Turkish Verbs',
                        'type' => 'pdf',
                        'fileName' => 'john_doe.pdf',
                    ],
                    [
                        'title' => 'Practice Quiz - Turkish Basics',
                        'type' => 'quiz',
                    ]
                ],
            ]);

            $spaClass = Classes::create([
                'course_id' => $spanish->id,
                'class_name' => 'Spanish Numbers and Nouns',
                'description' => 'Learn numbers 1 to 100, masculine and feminine nouns, and gender articles in Spanish.',
                'materials' => [
                    [
                        'title' => 'PDF Module - Numbers & Nouns',
                        'type' => 'pdf',
                        'fileName' => 'john_doe.pdf',
                    ],
                    [
                        'title' => 'Practice Quiz - Spanish Numbers',
                        'type' => 'quiz',
                    ]
                ],
            ]);

            // 5. Create Quizzes for Classes
            Quizzes::create([
                'class_id' => $engClass->id,
                'title' => 'English Basics Quiz',
                'description' => 'A short test covering greetings and introductions in English.',
                'type' => 'quiz',
                'contens' => [
                    [
                        'question' => 'Which word is a formal greeting in English?',
                        'choices' => [
                            ['text' => 'Hello', 'is_correct' => true],
                            ['text' => 'Bye', 'is_correct' => false],
                            ['text' => 'What’s up?', 'is_correct' => false],
                            ['text' => 'Hey', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Complete the sentence: "My name ___ John."',
                        'choices' => [
                            ['text' => 'are', 'is_correct' => false],
                            ['text' => 'is', 'is_correct' => true],
                            ['text' => 'am', 'is_correct' => false],
                            ['text' => 'be', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'How do you say goodbye in English?',
                        'choices' => [
                            ['text' => 'Nice to meet you', 'is_correct' => false],
                            ['text' => 'Goodbye', 'is_correct' => true],
                            ['text' => 'Please', 'is_correct' => false],
                            ['text' => 'Thank you', 'is_correct' => false],
                        ],
                    ],
                ],
            ]);

            Quizzes::create([
                'class_id' => $gerClass->id,
                'title' => 'German Greetings Quiz',
                'description' => 'A quiz testing German greetings, farewells, and basic letters.',
                'type' => 'quiz',
                'contens' => [
                    [
                        'question' => 'How do you say "Good morning" in German?',
                        'choices' => [
                            ['text' => 'Guten Morgen', 'is_correct' => true],
                            ['text' => 'Guten Tag', 'is_correct' => false],
                            ['text' => 'Gute Nacht', 'is_correct' => false],
                            ['text' => 'Hallo', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which of the following means "Goodbye"?',
                        'choices' => [
                            ['text' => 'Bitte', 'is_correct' => false],
                            ['text' => 'Danke', 'is_correct' => false],
                            ['text' => 'Auf Wiedersehen', 'is_correct' => true],
                            ['text' => 'Hallo', 'is_correct' => false],
                        ],
                    ],
                ],
            ]);

            Quizzes::create([
                'class_id' => $turClass->id,
                'title' => 'Turkish Vocab Quiz',
                'description' => 'Test your basic Turkish vocabulary words.',
                'type' => 'quiz',
                'contens' => [
                    [
                        'question' => 'What is the Turkish word for "Hello"?',
                        'choices' => [
                            ['text' => 'Merhaba', 'is_correct' => true],
                            ['text' => 'Teşekkürler', 'is_correct' => false],
                            ['text' => 'Evet', 'is_correct' => false],
                            ['text' => 'Hoşçakal', 'is_correct' => false],
                        ],
                    ],
                ],
            ]);

            Quizzes::create([
                'class_id' => $spaClass->id,
                'title' => 'Spanish Numbers Quiz',
                'description' => 'Check your knowledge of basic Spanish numbers.',
                'type' => 'quiz',
                'contens' => [
                    [
                        'question' => 'What is the Spanish word for "Three"?',
                        'choices' => [
                            ['text' => 'Uno', 'is_correct' => false],
                            ['text' => 'Dos', 'is_correct' => false],
                            ['text' => 'Tres', 'is_correct' => true],
                            ['text' => 'Cuatro', 'is_correct' => false],
                        ],
                    ],
                ],
            ]);

            // 6. Create Placement Tests (class_id = null)
            Quizzes::create([
                'class_id' => null,
                'title' => 'English Placement Test',
                'description' => 'Take this quick assessment test to see if you have beginner, intermediate, or advanced English skills.',
                'type' => 'placement',
                'contens' => [
                    [
                        'question' => 'If I ___ you, I would study harder.',
                        'choices' => [
                            ['text' => 'was', 'is_correct' => false],
                            ['text' => 'were', 'is_correct' => true],
                            ['text' => 'am', 'is_correct' => false],
                            ['text' => 'would be', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Choose the synonym of "Vast":',
                        'choices' => [
                            ['text' => 'Small', 'is_correct' => false],
                            ['text' => 'Huge', 'is_correct' => true],
                            ['text' => 'Narrow', 'is_correct' => false],
                            ['text' => 'Quick', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'She has been working here ___ five years.',
                        'choices' => [
                            ['text' => 'since', 'is_correct' => false],
                            ['text' => 'for', 'is_correct' => true],
                            ['text' => 'during', 'is_correct' => false],
                            ['text' => 'ago', 'is_correct' => false],
                        ],
                    ],
                ],
            ]);

            Quizzes::create([
                'class_id' => null,
                'title' => 'German Placement Test',
                'description' => 'Evaluate your current level of German vocabulary and sentence patterns.',
                'type' => 'placement',
                'contens' => [
                    [
                        'question' => 'Wie alt bist du? means...',
                        'choices' => [
                            ['text' => 'How are you?', 'is_correct' => false],
                            ['text' => 'How old are you?', 'is_correct' => true],
                            ['text' => 'Where are you from?', 'is_correct' => false],
                            ['text' => 'What is your name?', 'is_correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Which article belongs to "Buch" (Book)?',
                        'choices' => [
                            ['text' => 'der', 'is_correct' => false],
                            ['text' => 'die', 'is_correct' => false],
                            ['text' => 'das', 'is_correct' => true],
                        ],
                    ],
                ],
            ]);
        }
    }
}
