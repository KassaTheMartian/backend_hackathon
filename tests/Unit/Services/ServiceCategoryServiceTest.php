<?php

namespace Tests\Unit\Services;

use App\Models\ServiceCategory;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use App\Services\ServiceCategoryService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class ServiceCategoryServiceTest extends TestCase
{
    private ServiceCategoryRepositoryInterface $serviceCategoryRepository;
    private ServiceCategoryService $serviceCategoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceCategoryRepository = Mockery::mock(ServiceCategoryRepositoryInterface::class);
        $this->serviceCategoryService = new ServiceCategoryService($this->serviceCategoryRepository);
    }

    public function test_get_active_categories_returns_collection(): void
    {
        $categories = new Collection([
            (object)['id' => 1, 'name' => 'Skincare', 'is_active' => true],
            (object)['id' => 2, 'name' => 'Massage', 'is_active' => true],
        ]);

        $this->serviceCategoryRepository
            ->shouldReceive('getActive')
            ->once()
            ->andReturn($categories);

        $result = $this->serviceCategoryService->getActiveCategories();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_get_active_categories_returns_empty_when_none_active(): void
    {
        $categories = new Collection();

        $this->serviceCategoryRepository
            ->shouldReceive('getActive')
            ->once()
            ->andReturn($categories);

        $result = $this->serviceCategoryService->getActiveCategories();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

    public function test_get_category_by_slug_returns_category(): void
    {
        $category = Mockery::mock(ServiceCategory::class)->makePartial();
        $category->id = 1;
        $category->slug = 'skincare';
        $category->name = 'Skincare';

        $this->serviceCategoryRepository
            ->shouldReceive('getBySlug')
            ->once()
            ->with('skincare')
            ->andReturn($category);

        $result = $this->serviceCategoryService->getCategoryBySlug('skincare');

        $this->assertInstanceOf(ServiceCategory::class, $result);
        $this->assertEquals('skincare', $result->slug);
        $this->assertEquals('Skincare', $result->name);
    }

    public function test_get_category_by_slug_returns_null_when_not_found(): void
    {
        $this->serviceCategoryRepository
            ->shouldReceive('getBySlug')
            ->once()
            ->with('non-existent')
            ->andReturn(null);

        $result = $this->serviceCategoryService->getCategoryBySlug('non-existent');

        $this->assertNull($result);
    }

    public function test_create_category_creates_new_category(): void
    {
        $data = [
            'name' => 'New Category',
            'slug' => 'new-category',
            'description' => 'Test description',
            'is_active' => true,
        ];

        $category = Mockery::mock(ServiceCategory::class)->makePartial();
        $category->id = 1;
        $category->name = 'New Category';

        $this->serviceCategoryRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($category);

        $result = $this->serviceCategoryService->createCategory($data);

        $this->assertInstanceOf(ServiceCategory::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('New Category', $result->name);
    }

    public function test_create_category_with_minimal_data(): void
    {
        $data = [
            'name' => 'Minimal Category',
            'slug' => 'minimal-category',
        ];

        $category = Mockery::mock(ServiceCategory::class)->makePartial();
        $category->id = 2;
        $category->name = 'Minimal Category';

        $this->serviceCategoryRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($category);

        $result = $this->serviceCategoryService->createCategory($data);

        $this->assertInstanceOf(ServiceCategory::class, $result);
        $this->assertEquals('Minimal Category', $result->name);
    }

    public function test_update_category_updates_existing_category(): void
    {
        $data = [
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ];

        $category = Mockery::mock(ServiceCategory::class)->makePartial();
        $category->id = 1;
        $category->name = 'Updated Category';

        $this->serviceCategoryRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($category);

        $result = $this->serviceCategoryService->updateCategory(1, $data);

        $this->assertInstanceOf(ServiceCategory::class, $result);
        $this->assertEquals('Updated Category', $result->name);
    }

    public function test_update_category_returns_null_when_not_found(): void
    {
        $data = ['name' => 'Updated'];

        $this->serviceCategoryRepository
            ->shouldReceive('update')
            ->once()
            ->with(999, $data)
            ->andReturn(null);

        $result = $this->serviceCategoryService->updateCategory(999, $data);

        $this->assertNull($result);
    }

    public function test_delete_category_deletes_successfully(): void
    {
        $this->serviceCategoryRepository
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->serviceCategoryService->deleteCategory(1);

        $this->assertTrue($result);
    }

    public function test_delete_category_returns_false_when_not_found(): void
    {
        $this->serviceCategoryRepository
            ->shouldReceive('delete')
            ->once()
            ->with(999)
            ->andReturn(false);

        $result = $this->serviceCategoryService->deleteCategory(999);

        $this->assertFalse($result);
    }

    public function test_get_categories_with_services_count_returns_collection(): void
    {
        $categories = new Collection([
            (object)['id' => 1, 'name' => 'Skincare', 'services_count' => 5],
            (object)['id' => 2, 'name' => 'Massage', 'services_count' => 3],
        ]);

        $this->serviceCategoryRepository
            ->shouldReceive('getWithServicesCount')
            ->once()
            ->andReturn($categories);

        $result = $this->serviceCategoryService->getCategoriesWithServicesCount();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_get_categories_with_services_count_includes_count(): void
    {
        $category1 = (object)['id' => 1, 'name' => 'Skincare', 'services_count' => 10];
        $category2 = (object)['id' => 2, 'name' => 'Massage', 'services_count' => 0];

        $categories = new Collection([$category1, $category2]);

        $this->serviceCategoryRepository
            ->shouldReceive('getWithServicesCount')
            ->once()
            ->andReturn($categories);

        $result = $this->serviceCategoryService->getCategoriesWithServicesCount();

        $this->assertEquals(10, $result->first()->services_count);
        $this->assertEquals(0, $result->last()->services_count);
    }

    public function test_reorder_categories_updates_display_order(): void
    {
        $categoryIds = [3, 1, 2];

        $this->serviceCategoryRepository
            ->shouldReceive('updateDisplayOrder')
            ->once()
            ->with(3, 1);

        $this->serviceCategoryRepository
            ->shouldReceive('updateDisplayOrder')
            ->once()
            ->with(1, 2);

        $this->serviceCategoryRepository
            ->shouldReceive('updateDisplayOrder')
            ->once()
            ->with(2, 3);

        $this->serviceCategoryService->reorderCategories($categoryIds);

        // Assert expectations were met
        $this->assertTrue(true);
    }

    public function test_reorder_categories_with_single_category(): void
    {
        $categoryIds = [1];

        $this->serviceCategoryRepository
            ->shouldReceive('updateDisplayOrder')
            ->once()
            ->with(1, 1);

        $this->serviceCategoryService->reorderCategories($categoryIds);

        $this->assertTrue(true);
    }

    public function test_reorder_categories_with_empty_array(): void
    {
        $categoryIds = [];

        $this->serviceCategoryRepository
            ->shouldNotReceive('updateDisplayOrder');

        $this->serviceCategoryService->reorderCategories($categoryIds);

        $this->assertTrue(true);
    }

    public function test_reorder_categories_maintains_sequential_order(): void
    {
        $categoryIds = [5, 2, 8, 1];

        foreach ($categoryIds as $index => $categoryId) {
            $this->serviceCategoryRepository
                ->shouldReceive('updateDisplayOrder')
                ->once()
                ->with($categoryId, $index + 1);
        }

        $this->serviceCategoryService->reorderCategories($categoryIds);

        $this->assertTrue(true);
    }
}
