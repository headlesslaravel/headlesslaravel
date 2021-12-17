<?php

use HeadlessLaravel\Formations\Tests\Fixtures\AuthorFormation;
use HeadlessLaravel\Formations\Tests\Fixtures\LikeFormation;
use HeadlessLaravel\Formations\Tests\Fixtures\PostFormation;
use HeadlessLaravel\Formations\Tests\Fixtures\TagFormation;
use Illuminate\Support\Facades\Route;

Route::get('login')->name('login');

Route::formation('posts', PostFormation::class);
Route::formation('authors', AuthorFormation::class);
Route::formation('authors.posts', PostFormation::class);
Route::formation('posts.tags', TagFormation::class)->pivot();
