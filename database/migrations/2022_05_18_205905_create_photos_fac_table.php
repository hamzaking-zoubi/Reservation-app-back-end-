<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhotosFacTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photos_facility', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_facility")
                  ->constrained("facilities","id")
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->string("path_photo");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photos_facs');
    }
}
