<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Tag;
use App\Models\Vote;
use App\Models\Activity;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'reputation' => 1000,
        ]);

        // Create regular user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'reputation' => 100,
        ]);

        // Create tags
        $tags = [
            ['name' => 'Laravel', 'description' => 'The PHP framework for web artisans', 'slug' => 'laravel'],
            ['name' => 'Vue.js', 'description' => 'Progressive JavaScript framework', 'slug' => 'vue-js'],
            ['name' => 'JavaScript', 'description' => 'Programming language for web development', 'slug' => 'javascript'],
            ['name' => 'PHP', 'description' => 'Server-side scripting language', 'slug' => 'php'],
            ['name' => 'MySQL', 'description' => 'Open-source relational database management system', 'slug' => 'mysql'],
            ['name' => 'HTML', 'description' => 'Standard markup language for web pages', 'slug' => 'html'],
            ['name' => 'CSS', 'description' => 'Style sheet language for web pages', 'slug' => 'css'],
            ['name' => 'Git', 'description' => 'Distributed version control system', 'slug' => 'git'],
        ];

        foreach ($tags as $tagData) {
            Tag::create($tagData);
        }

        // Get all tags
        $allTags = Tag::all();

        // Create questions
        $questions = [
            [
                'title' => 'How to install Laravel?',
                'body' => '<p>I am new to Laravel and I am struggling to install it. I have tried following the documentation, but I am getting errors. Can someone help me?</p>',
                'user_id' => $admin->id,
                'views' => rand(10, 100),
                'score' => rand(1, 10),
                'is_answered' => false,
                'slug' => 'how-to-install-laravel',
            ],
            [
                'title' => 'What is the difference between Vue 2 and Vue 3?',
                'body' => '<p>I have been using Vue 2 for a while now and I am thinking of upgrading to Vue 3. What are the main differences between the two versions?</p>',
                'user_id' => $user->id,
                'views' => rand(10, 100),
                'score' => rand(1, 10),
                'is_answered' => false,
                'slug' => 'what-is-the-difference-between-vue-2-and-vue-3',
            ],
            [
                'title' => 'How to use Eloquent relationships in Laravel?',
                'body' => '<p>I am trying to set up relationships between my models in Laravel, but I am not sure how to use them correctly. Can someone explain how to use one-to-many and many-to-many relationships?</p>',
                'user_id' => $admin->id,
                'views' => rand(10, 100),
                'score' => rand(1, 10),
                'is_answered' => false,
                'slug' => 'how-to-use-eloquent-relationships-in-laravel',
            ],
            [
                'title' => 'How to make API calls in Vue.js?',
                'body' => '<p>I am building a Vue.js application that needs to make API calls to my backend. What is the best way to do this? Should I use Axios or Fetch?</p>',
                'user_id' => $user->id,
                'views' => rand(10, 100),
                'score' => rand(1, 10),
                'is_answered' => false,
                'slug' => 'how-to-make-api-calls-in-vue-js',
            ],
            [
                'title' => 'What are the best practices for securing a Laravel application?',
                'body' => '<p>I want to make sure my Laravel application is secure. What are some best practices for securing a Laravel application?</p>',
                'user_id' => $admin->id,
                'views' => rand(10, 100),
                'score' => rand(1, 10),
                'is_answered' => false,
                'slug' => 'what-are-the-best-practices-for-securing-a-laravel-application',
            ],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create($questionData);
            $question->tags()->attach($allTags->random(rand(1, 3))->pluck('id')->toArray());
        }

        // Create answers
        $allQuestions = Question::all();
        foreach ($allQuestions as $question) {
            // Create 1 to 3 answers per question
            for ($i = 0; $i < rand(1, 3); $i++) {
                $answer = Answer::create([
                    'body' => '<p>This is an answer to your question. Here is some helpful information.</p>',
                    'user_id' => $i % 2 === 0 ? $admin->id : $user->id,
                    'question_id' => $question->id,
                    'score' => rand(0, 5),
                    'is_accepted' => false,
                ]);

                // Mark one of the answers as accepted
                if ($i === 0) {
                    $answer->is_accepted = true;
                    $answer->save();
                    $question->is_answered = true;
                    $question->save();
                }

                // Update answer count
                $question->answers_count = $question->answers()->count();
                $question->save();
            }
        }

        // Create votes
        $allAnswers = Answer::all();
        $users = [$admin, $user];
        
        foreach ($allQuestions as $question) {
            // Create votes for questions (one vote per user)
            foreach ($users as $voteUser) {
                Vote::create([
                    'user_id' => $voteUser->id,
                    'votable_type' => 'App\\Models\\Question',
                    'votable_id' => $question->id,
                    'vote_type' => rand(0, 1) === 0 ? 1 : -1,
                ]);
            }
        }

        foreach ($allAnswers as $answer) {
            // Create votes for answers (one vote per user)
            foreach ($users as $voteUser) {
                Vote::create([
                    'user_id' => $voteUser->id,
                    'votable_type' => 'App\\Models\\Answer',
                    'votable_id' => $answer->id,
                    'vote_type' => rand(0, 1) === 0 ? 1 : -1,
                ]);
            }
        }

        // Create activities
        for ($i = 0; $i < 10; $i++) {
            $question = $allQuestions->random();
            Activity::create([
                'user_id' => $i % 2 === 0 ? $admin->id : $user->id,
                'action' => ['asked', 'answered', 'upvoted', 'downvoted', 'accepted an answer to'][rand(0, 4)],
                'title' => $question->title,
                'link' => '/questions/' . $question->slug,
            ]);
        }
    }
}
