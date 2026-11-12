<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comment_tags', function (Blueprint $table) {
            // Add missing foreign key columns if they don't exist
            if (!Schema::hasColumn('comment_tags', 'tweet_id')) {
                $table->foreignId('tweet_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('comment_tags', 'tag_id')) {
                $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            }
            
            // Add index for performance
            $table->index(['tweet_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comment_tags', function (Blueprint $table) {
            $table->dropIndex(['tweet_id', 'tag_id']);
        });
    }
};
