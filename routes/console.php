<?php
use Illuminate\Support\Facades\Artisan;
Artisan::command('flightlab:about', fn () => $this->info('SkiJumping Flight Lab'));
