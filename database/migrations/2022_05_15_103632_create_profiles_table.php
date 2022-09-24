<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->primary("id_user");
            $table->foreignId("id_user")
                ->unique()
                ->constrained("users","id")
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string("path_photo")->nullable();
            $table->bigInteger("phone")->unsigned()->nullable();
            $table->enum("gender",["female","male"])->nullable();
            $table->date("age")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
}
