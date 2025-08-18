<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create()); // Authenticate a user for tests
    }

    /** @test */
    public function it_can_create_a_stock_opname_and_details()
    {
        $product = Product::factory()->create();
        $batch = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 100,
        ]);

        $response = $this->postJson('/stock-opnames', [
            'notes' => 'Initial stock opname',
            'details' => [
                [
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'system_stock' => 100,
                    'counted_stock' => 95,
                ],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('stock_opnames', [
            'notes' => 'Initial stock opname',
        ]);
        $this->assertDatabaseHas('stock_opname_details', [
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'system_stock' => 100,
            'counted_stock' => 95,
        ]);
    }

    /** @test */
    public function it_correctly_adjusts_stock_on_stock_opname_application_for_shortage()
    {
        $product = Product::factory()->create();
        $batch = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 100,
        ]);

        $stockOpname = StockOpname::factory()->create([
            'notes' => 'Stock opname with shortage',
        ]);

        StockOpnameDetail::factory()->create([
            'stock_opname_id' => $stockOpname->id,
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'system_stock' => 100,
            'counted_stock' => 90, // 10 units short
        ]);

        // Assuming an endpoint to apply the stock opname
        $response = $this->postJson("/stock-opnames/{$stockOpname->id}/apply");

        $response->assertStatus(200);
        $this->assertEquals(90, $batch->fresh()->stock); // Stock should be adjusted to counted stock
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'type' => 'stock_opname_out', // Or similar type for shortage
            'quantity' => 10,
            'new_stock' => 90,
        ]);
    }

    /** @test */
    public function it_correctly_adjusts_stock_on_stock_opname_application_for_overage()
    {
        $product = Product::factory()->create();
        $batch = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 100,
        ]);

        $stockOpname = StockOpname::factory()->create([
            'notes' => 'Stock opname with overage',
        ]);

        StockOpnameDetail::factory()->create([
            'stock_opname_id' => $stockOpname->id,
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'system_stock' => 100,
            'counted_stock' => 110, // 10 units over
        ]);

        // Assuming an endpoint to apply the stock opname
        $response = $this->postJson("/stock-opnames/{$stockOpname->id}/apply");

        $response->assertStatus(200);
        $this->assertEquals(110, $batch->fresh()->stock); // Stock should be adjusted to counted stock
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'type' => 'stock_opname_in', // Or similar type for overage
            'quantity' => 10,
            'new_stock' => 110,
        ]);
    }

    /** @test */
    public function it_does_not_adjust_stock_if_counted_stock_matches_system_stock()
    {
        $product = Product::factory()->create();
        $batch = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 100,
        ]);

        $stockOpname = StockOpname::factory()->create([
            'notes' => 'Stock opname with no change',
        ]);

        StockOpnameDetail::factory()->create([
            'stock_opname_id' => $stockOpname->id,
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
            'system_stock' => 100,
            'counted_stock' => 100, // No change
        ]);

        $response = $this->postJson("/stock-opnames/{$stockOpname->id}/apply");

        $response->assertStatus(200);
        $this->assertEquals(100, $batch->fresh()->stock); // Stock should remain unchanged
        $this->assertDatabaseMissing('stock_movements', [ // No stock movement should be recorded
            'product_id' => $product->id,
            'product_batch_id' => $batch->id,
        ]);
    }
}
