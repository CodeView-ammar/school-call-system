<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->renameColumn('name', 'route_name');
        });
    }

    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->renameColumn('route_name', 'name');
        });
    }

};
