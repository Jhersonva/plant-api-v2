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
        Schema::create('about_us', function (Blueprint $table) {
            $table->id();
            $table->text('mission');
            $table->text('vision');
            $table->json('about_values');
            $table->string('name_yt');
            $table->string('url_yt');
            $table->timestamps();
        });

        /**
         * INSERT INTO about_us (mission, vision, about_values, name_yt, url_yt, created_at, updated_at) 
            VALUES (
                'Nuestra misión es innovar constantemente.',
                'Nuestra visión es liderar el sector tecnológico.',
                '["Innovación", "Compromiso", "Integridad", "Calidad"]',
                'Canal Oficial',
                'https://www.youtube.com/channel/ejemplo',
                NOW(),
                NOW()
            );

         */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
