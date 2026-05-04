<?php

declare(strict_types=1);

use Deplox\Shield\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Route::middleware('auth:dynamic')
            ->get('/auth-test', fn (Request $request) => response()->json([
                'id' => $request->user()->getKey(),
            ]));
    })
    ->in('Feature');
