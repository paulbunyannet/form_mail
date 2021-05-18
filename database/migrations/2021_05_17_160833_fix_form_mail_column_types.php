<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixFormMailColumnTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->table('form_mail', function (Blueprint $table) {
            $table->longText('fields')->change();
            $table->longText('message_to_recipient')->change();
            $table->longText('message_to_sender')->change();
            $table->longText('branding')->change();
            $table->longText('head')->change();
            $table->longText('greeting')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->table('form_mail', function (Blueprint $table) {
            $table->text('fields')->change();
            $table->text('message_to_recipient')->change();
            $table->text('message_to_sender')->change();
            $table->text('branding')->change();
            $table->text('head')->change();
            $table->text('greeting')->change();
        });
    }
}
