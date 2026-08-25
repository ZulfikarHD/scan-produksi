<?php

use App\Enums\ProductionOrderStatus;

test('production order status enum has all expected cases and backing values', function () {
    expect(ProductionOrderStatus::cases())->toHaveCount(4);
    expect(ProductionOrderStatus::READY->value)->toBe('ready');
    expect(ProductionOrderStatus::IN_PROGRESS->value)->toBe('in_progress');
    expect(ProductionOrderStatus::COMPLETED->value)->toBe('completed');
    expect(ProductionOrderStatus::CANCELLED->value)->toBe('cancelled');
});

test('production order status enum returns expected Indonesian labels', function () {
    expect(ProductionOrderStatus::READY->label())->toBe('Siap Kemas');
    expect(ProductionOrderStatus::IN_PROGRESS->label())->toBe('Sedang Diproses');
    expect(ProductionOrderStatus::COMPLETED->label())->toBe('Selesai');
    expect(ProductionOrderStatus::CANCELLED->label())->toBe('Dibatalkan');
});

test('production order status enum returns expected badge colors for UI', function () {
    expect(ProductionOrderStatus::READY->badgeColor())->toBe('blue');
    expect(ProductionOrderStatus::IN_PROGRESS->badgeColor())->toBe('amber');
    expect(ProductionOrderStatus::COMPLETED->badgeColor())->toBe('green');
    expect(ProductionOrderStatus::CANCELLED->badgeColor())->toBe('red');

    // color() alias
    expect(ProductionOrderStatus::READY->color())->toBe('blue');
    expect(ProductionOrderStatus::IN_PROGRESS->color())->toBe('amber');
});
