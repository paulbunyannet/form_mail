<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormMailModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_mail', function (Blueprint $table) {
            $table->increments('id');

            // name of form
            $table->string('form')->nullable();

            // resource name for views and language
            $table->string('resource')->nullable();

            // email of creator
            $table->string('sender')->nullable();

            // email of the recipient
            $table->string('recipient')->nullable();

            // fields from form submission
            $table->text('fields')->nullable();

            // body of message to send to recipient
            $table->text('message_to_recipient')->nullable();

            // body of message to send to recipient
            $table->text('message_to_sender')->nullable();

            // subject of message
            $table->string('subject')->nullable();

            // flag to check if this message has been sent already tp recipient
            $table->boolean('message_sent_to_recipient')->nullable();

            // flag to check if a confirmation message was sent to sender
            $table->boolean('confirmation_sent_to_sender')->nullable();

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
        Schema::drop('form_mail');
    }
}
