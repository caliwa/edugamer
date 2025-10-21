<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Videogame\LevelSelector;
use App\Livewire\Videogame\Menu\IndexComponent;
use App\Livewire\Videogame\Auth\RegisterComponent;
use App\Livewire\Videogame\Menu\IndexMenuComponent;
use App\Livewire\Videogame\Levels\First\IndexComponent as FirstLevelIndexComponent;
use App\Livewire\Videogame\Levels\Second\IndexComponent as SecondLevelIndexComponent;
use App\Livewire\Videogame\Levels\Third\IndexComponent as ThirdLevelIndexComponent;

Route::get('/', IndexComponent::class);
Route::get('/register', RegisterComponent::class);
// Route::get('/menu', IndexMenuComponent::class)->name('videogame.menu');

Route::get('/levels', LevelSelector::class)->name('menu.home');
Route::get('/levels/1', FirstLevelIndexComponent::class)->name('levels.1');
Route::get('/levels/2', SecondLevelIndexComponent::class)->name('levels.2');
Route::get('/levels/3', ThirdLevelIndexComponent::class)->name('levels.3');
// Route::get('/videogame/levels/first', FirstLevelIndexComponent::class)->name('videogame.levels.first');
