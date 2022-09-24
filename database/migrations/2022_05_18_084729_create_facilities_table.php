<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_user")->constrained("users","id")->cascadeOnDelete()->cascadeOnUpdate();
            $table->string("name");
            $table->string("location");
            $table->text("description");
            $table->enum("type",["hostel","chalet","farmer"]);
            $table->float("cost")->unsigned();
            $table->integer("rate")->nullable();
            $table->integer("num_guest")->unsigned();
            $table->integer("num_room")->unsigned();
            $table->boolean("wifi")->default(false);
            $table->boolean("coffee_machine")->default(false);
            $table->boolean("air_condition")->default(false);
            $table->boolean("tv")->default(false);
            $table->boolean("fridge")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facilities');
    }
}
