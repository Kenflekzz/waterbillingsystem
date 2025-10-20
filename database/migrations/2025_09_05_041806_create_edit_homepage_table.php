<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('homepages', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            
            $table->string('announcement_image')->nullable();
            $table->string('announcement_heading')->nullable();
            $table->text('announcement_text')->nullable();
            $table->json('announcement_items')->nullable();
            
            $table->json('advisories')->nullable(); // [{title, text, image}, ...]
            
            $table->string('connect_title')->nullable();
            $table->json('connect_images')->nullable(); // [image1, image2]
            
            $table->string('footer_address')->nullable();
            $table->string('footer_contact')->nullable();
            $table->string('footer_email')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepages');
    }
};
