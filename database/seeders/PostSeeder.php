<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Post::factory(5)->create([
            'user_id'=> '1',
        ]);
        Post::factory(5)->create([
            'user_id'=> '2',
        ]);
    }
}
