<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoryReactionsTable extends Migration
{
    public function up()
    {
        Schema::create('story_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained('stories')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reaction');
            $table->timestamps();

            $table->unique(['story_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('story_reactions');
    }
}
