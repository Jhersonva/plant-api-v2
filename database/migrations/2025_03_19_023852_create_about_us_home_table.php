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
        Schema::create('about_us_home', function (Blueprint $table) {
            $table->id();
            $table->text('text_section_one');
            $table->text('text_section_two');
            $table->timestamps();
        });

        /**
    
         * INSERT INTO about_us_home (text_section_one, text_section_two, created_at, updated_at) 
            VALUES (
                'Primer texto de la seccion',
                'Segundo texto de la seccion',
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
        Schema::dropIfExists('about_us_home');
    }
};
