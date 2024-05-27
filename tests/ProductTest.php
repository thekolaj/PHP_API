<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Repository\ProductRepository;
use Symfony\Component\Serializer\Serializer;

class ProductTest extends ApiTestCase
{
    const ROUTE = '/api/product/';
    const FIXTURE = [
        0 => [
            'name' => 'First Product',
            'price' => 1,
        ],
        1 => [
            'name' => 'Second Product',
            'price' => 2.2,
        ],
        2 => [
            'name' => 'Third Product',
            'price' => 3.33,
        ],
    ];
    public static ProductRepository $repository;
    public static Serializer $serializer;

    public static function setUpBeforeClass(): void
    {
        self::$repository = static::getContainer()->get(ProductRepository::class);
        self::$serializer = static::getContainer()->get('serializer');
    }


    public function test_index(): void
    {
        $response = static::createClient()->request('GET', self::ROUTE);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(self::FIXTURE);
        $this->assertCount(3, $response->toArray());
    }

    public function test_show_succeeds(): void
    {
        $firstProduct = self::$repository->findOneBy(['name' => self::FIXTURE[0]['name']]);
        $firstJson = self::$serializer->serialize($firstProduct, 'json');
        $response = static::createClient()->request('GET', self::ROUTE.$firstProduct->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonEquals($firstJson);
    }

    public function test_show_invalid_id_fails(): void
    {
        $response = static::createClient()->request('GET', self::ROUTE. 0);

        $this->assertResponseStatusCodeSame(404);
    }

    public function test_new_succeeds(): void
    {
        $newProduct = [
            'name' => 'New Product',
            'price' => 9.99,
        ];
        $response = static::createClient()->request('POST', self::ROUTE, ['json' => $newProduct]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains($newProduct);
        $this->assertSame(4, self::$repository->count());
    }

    public function test_new_empty_data_fails(): void
    {
        $response = static::createClient()->request('POST', self::ROUTE, ['json' => []]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(
            '{"name":["This value should not be null."],"price":["This value should not be null."]}'
        );
    }

    public function test_new_invalid_data_fails(): void
    {
        $response = static::createClient()->request('POST', self::ROUTE, [
            'json' => ['name' => 'New', 'price' => -1],
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(
            '{"name":["This value is too short. It should have 5 characters or more."],"price":["This value should be either positive or zero."]}'
        );
    }

    public function test_update_succeeds(): void
    {
        $secondProduct = self::$repository->findOneBy(['name' => self::FIXTURE[1]['name']]);
        $updatedProduct = ['name' => 'Updated Product', 'price' => 8];

        $response = static::createClient()->request(
            'PUT',
            self::ROUTE.$secondProduct->getId(),
            ['json' => $updatedProduct]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains($updatedProduct);
        $this->assertSame(3, self::$repository->count());
    }

    public function test_update_PUT_empty_data_fails(): void
    {
        $secondProduct = self::$repository->findOneBy(['name' => self::FIXTURE[1]['name']]);
        $response = static::createClient()->request(
            'PUT',
            self::ROUTE.$secondProduct->getId(),
            ['json' => []]
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(
            '{"name":["This value should not be null."],"price":["This value should not be null."]}'
        );
    }

    public function test_update_PUT_partial_data_fails(): void
    {
        $secondProduct = self::$repository->findOneBy(['name' => self::FIXTURE[1]['name']]);
        $response = static::createClient()->request(
            'PUT',
            self::ROUTE.$secondProduct->getId(),
            ['json' => ['name' => 'New Product']]
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(
            '{"price":["This value should not be null."]}'
        );
    }

    public function test_update_PATCH_partial_data_succeeds(): void
    {
        $secondProduct = self::$repository->findOneBy(['name' => self::FIXTURE[1]['name']]);
        $updatedProduct = ['name' => 'Updated Product'];

        $response = static::createClient()->request(
            'PATCH',
            self::ROUTE.$secondProduct->getId(),
            ['json' => $updatedProduct]
        );
        $secondProduct->setName($updatedProduct['name']);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains($updatedProduct);
        $this->assertSame(3, self::$repository->count());
        $this->assertEquals(
            $secondProduct,
            self::$repository->findOneBy(['name' => $updatedProduct['name']])
        );
    }

    public function test_update_PATCH_invalid_data_fails(): void
    {
        $secondProduct = self::$repository->findOneBy(['name' => self::FIXTURE[1]['name']]);

        $response = static::createClient()->request(
            'PATCH',
            self::ROUTE.$secondProduct->getId(),
            ['json' => ['name' => 'New']]
        );

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(
            '{"name":["This value is too short. It should have 5 characters or more."]}'
        );
    }

    public function test_delete_succeeds(): void
    {
        $thirdProduct = self::$repository->findOneBy(['name' => self::FIXTURE[2]['name']]);
        $response = static::createClient()->request('DELETE', self::ROUTE.$thirdProduct->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJsonContains(
            ['name' => $thirdProduct->getName(), 'price' => $thirdProduct->getPrice()]
        );

        $this->assertNull(self::$repository->findOneBy(['name' => self::FIXTURE[2]['name']]));
        $this->assertSame(2, self::$repository->count());
    }
}
