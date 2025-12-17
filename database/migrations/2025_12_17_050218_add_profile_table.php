<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('display_name')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('profile_image')->nullable();
            $table->string('bio')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->dropColumn([
                'firstname',
                'lastname',
                'display_name',
                'username',
                'bio',
                'phone',
                'website',
                'location',
            ]);
        });
    }
};
