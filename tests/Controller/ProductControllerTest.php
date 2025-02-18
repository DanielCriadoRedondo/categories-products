<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    private static $client;

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();
    }

    public function testCreateAndGetProduct(): void
    {
        $token = $this->getAuthToken();

        // First create a category.
        $categoryData = [
            'title'   => 'Category Test Create 2',
            'details' => 'Details'
        ];
        self::$client->request(
            'POST',
            '/api/v1/categories',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode($categoryData)
        );
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);
        $categoryId = $responseData['id'];

        // Create a product.
        $productData = [
            'name'        => 'Test Product Create',
            'price'       => 20.00,
            'category_id' => $categoryId,
            'description' => 'A test product description'
        ];
        self::$client->request(
            'POST',
            '/api/v1//products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token],
            json_encode($productData)
        );
        $this->assertEquals(201, self::$client->getResponse()->getStatusCode());
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);

        $productId = $responseData['id'];

        // Retrieve product details.
        self::$client->request('GET', "/products/{$productId}");
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertEquals('Test Product Create', $responseData['name']);
    }

    public function testPaginationInProducts(): void
    {
        $token = $this->getAuthToken();

        // Create a category.
        $categoryData = [
            'title'   => 'Pagination Category',
            'details' => 'Details'
        ];
        self::$client->request(
            'POST',
            '/api/v1/categories',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode($categoryData)
        );
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);
        $categoryId = $responseData['id'];

        // Create multiple products.
        for ($i = 1; $i <= 15; $i++) {
            $productData = [
                'name'        => 'Product ' . $i,
                'price'       => 10 + $i,
                'category_id' => $categoryId
            ];
            self::$client->request(
                'POST',
                '/api/v1//products',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($productData)
            );
        }

        // Retrieve first page (limit 10).
        self::$client->request('GET', '/products?page=1&limit=10');
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertCount(10, $responseData['products']);

        // Retrieve second page.
        self::$client->request('GET', '/products?page=2&limit=10');
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertGreaterThanOrEqual(5, count($responseData['products']));
    }

    /**
     * Obtain JWT authentication token for API requests.
     */
    private function getAuthToken(): string
    {
        self::$client->request(
            'POST',
            '/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'dcriador@gmail.com',
                'password' => 'iristrace'
            ])
        );

        $response = json_decode(self::$client->getResponse()->getContent(), true);

        if (!isset($response['token'])) {
            throw new \Exception("Authentication failed: " . json_encode($response));
        }

        return $response['token'];
    }
}
