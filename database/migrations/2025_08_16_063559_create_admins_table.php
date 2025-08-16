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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // ชื่อแอดมิน
            $table->string('email')->unique(); // อีเมล (ใช้ล็อกอิน)
            $table->string('password');     // รหัสผ่าน (bcrypt)
            $table->boolean('is_super')->default(false); // ถ้าเป็น super admin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
