<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // member: default. author: can post Blogs + SOS. organizer: can also create Causes.
            $table->enum('author_tier', ['member', 'author', 'organizer'])->default('member')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('author_tier');
        });
    }
};
