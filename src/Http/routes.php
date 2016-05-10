<?php
Route::post('form-mail/send',['uses' => 'Pbc\\FormMail\\Http\\Controllers\\FormMailController@requestHandler', 'as' => 'form-mail.send']);