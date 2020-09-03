<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYysAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('yys_accounts', function (Blueprint $table) {
            $table->id();

            $table->string('nickname');
            $table->string('sn')->unique();
            $table->integer('price')->unsigned();
            $table->tinyInteger('platform')->unsigned();
            $table->string('serverName');
            $table->integer('avalableTime')->unsigned()->nullable();

            $table->string('roleId')->nullable()->index();
            $table->tinyInteger('status')->unsigned()->nullable();
            $table->integer('hp')->nullable();
            $table->integer('gouyu')->nullable();
            $table->integer('lv15')->unsigned()->nullable();
            $table->integer('yuhunScore')->unsigned()->nullable();
            $table->tinyInteger('star6')->unsigned()->nullable();
            $table->tinyInteger('cards')->unsigned()->nullable();
            $table->tinyInteger('sp')->unsigned()->nullable();
            $table->tinyInteger('ssr')->unsigned()->nullable();

            $table->json('hero')->nullable();
            $table->json('yuhun')->nullable();
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
        Schema::dropIfExists('yys_accounts');
    }
}
