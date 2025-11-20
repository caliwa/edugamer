<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Videogame\LevelSelector;
use App\Livewire\Videogame\Menu\IndexComponent;
use App\Livewire\Videogame\Auth\RegisterComponent;
use App\Livewire\Videogame\Menu\IndexMenuComponent;
use App\Livewire\Videogame\Levels\First\IndexComponent as FirstLevelIndexComponent;
use App\Livewire\Videogame\Levels\Second\IndexComponent as SecondLevelIndexComponent;
use App\Livewire\Videogame\Levels\Third\IndexComponent as ThirdLevelIndexComponent;
use App\Livewire\Videogame\Levels\Fourth\IndexComponent as FourthLevelIndexComponent;
use App\Livewire\Videogame\Levels\Fifth\IndexComponent as FifthLevelIndexComponent;
use App\Livewire\Videogame\Levels\Sixth\IndexComponent as SixthLevelIndexComponent;
use App\Livewire\Videogame\Levels\Seventh\IndexComponent as SeventhLevelIndexComponent;
use App\Livewire\Videogame\Levels\Eighth\IndexComponent as EighthLevelIndexComponent;
use App\Livewire\Videogame\Levels\Ninth\IndexComponent as NinthLevelIndexComponent;
use App\Livewire\Videogame\Levels\Tenth\IndexComponent as TenthLevelIndexComponent;
use App\Livewire\Videogame\Levels\Eleventh\IndexComponent as EleventhLevelIndexComponent;
use App\Livewire\Videogame\Levels\Twelfth\IndexComponent as TwelfthLevelIndexComponent;
use App\Livewire\Videogame\Levels\Thirteenth\IndexComponent as ThirteenthLevelIndexComponent;
use App\Livewire\Videogame\Levels\Fourteenth\IndexComponent as FourteenthLevelIndexComponent;
use App\Livewire\Videogame\Levels\Fifteenth\IndexComponent as FifteenthLevelIndexComponent;
use App\Livewire\Videogame\Levels\Sixteenth\IndexComponent as SixteenthLevelIndexComponent;


Route::get('/', IndexComponent::class);
Route::get('/register', RegisterComponent::class);
// Route::get('/menu', IndexMenuComponent::class)->name('videogame.menu');

Route::get('/levels', LevelSelector::class)->name('menu.home');
Route::get('/levels/1', FirstLevelIndexComponent::class)->name('levels.1');
Route::get('/levels/2', SecondLevelIndexComponent::class)->name('levels.2');
Route::get('/levels/3', ThirdLevelIndexComponent::class)->name('levels.3');
Route::get('/levels/4', FourthLevelIndexComponent::class)->name('levels.4');
Route::get('/levels/5', FifthLevelIndexComponent::class)->name('levels.5');
Route::get('/levels/6', SixthLevelIndexComponent::class)->name('levels.6');
Route::get('/levels/7', SeventhLevelIndexComponent::class)->name('levels.7');
Route::get('/levels/8', EighthLevelIndexComponent::class)->name('levels.8');
Route::get('/levels/9', NinthLevelIndexComponent::class)->name('levels.9');
Route::get('/levels/10', TenthLevelIndexComponent::class)->name('levels.10');
Route::get('/levels/11', EleventhLevelIndexComponent::class)->name('levels.11');
Route::get('/levels/12', TwelfthLevelIndexComponent::class)->name('levels.12');
Route::get('/levels/13', ThirteenthLevelIndexComponent::class)->name('levels.13');
Route::get('/levels/14', FourteenthLevelIndexComponent::class)->name('levels.14');
Route::get('/levels/15', FifteenthLevelIndexComponent::class)->name('levels.15');
Route::get('/levels/16', SixteenthLevelIndexComponent::class)->name('levels.16');

// Route::get('/videogame/levels/first', FirstLevelIndexComponent::class)->name('videogame.levels.first');
