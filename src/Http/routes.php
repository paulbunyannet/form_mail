<?php
Route::post('form-mail/send',[\Pbc\FormMail\Http\Controllers\FormMailController::class, 'requestHandler'])->name('form-mail.send');
