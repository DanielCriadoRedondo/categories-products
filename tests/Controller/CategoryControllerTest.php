<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    private static $client;

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();
    }

    /**
     * Test creating a category and retrieving its details.
     */
    public function testCreateAndGetCategory(): void
    {
        $token = $this->getAuthToken(); // Obtain JWT token for authentication

        $data = [
            'title'   => 'Test Create',
            'details' => 'Some details'
        ];

        // Create category with authentication.
        self::$client->request(
            'POST',
            '/api/v1/categories',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode($data)
        );

        // Assert that the response status is 201 (Created)
        $this->assertEquals(201, self::$client->getResponse()->getStatusCode());
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);

        $id = $responseData['id'];

        // Retrieve category details.
        self::$client->request('GET', "/categories/{$id}");
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertEquals('Test Create', $responseData['title']);
    }

    /**
     * Test that deleting a category with associated products fails.
     */
    public function testDeleteCategoryWithProductsFails(): void
    {
        $token = $this->getAuthToken(); // Obtain JWT token for authentication

        // Create a category.
        $data = [
            'title'   => 'Test Delete Fail',
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
            json_encode($data)
        );
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);
        $categoryId = $responseData['id'];

        // Create a product in that category.
        $productData = [
            'name'        => 'Test Product Delete Fail',
            'price'       => 10.50,
            'category_id' => $categoryId
        ];
        self::$client->request(
            'POST',
            '/api/v1/products',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode($productData)
        );
        $this->assertEquals(201, self::$client->getResponse()->getStatusCode());

        // Attempt to delete the category.
        self::$client->request(
            'DELETE',
            "/api/v1/categories/{$categoryId}",
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );
        
        // Assert that deletion fails with status 400 (Bad Request)
        $this->assertEquals(400, self::$client->getResponse()->getStatusCode());
        $responseData = json_decode(self::$client->getResponse()->getContent(), true);
        $this->assertEquals('Cannot delete category with associated products.', $responseData['error']);
    }

    /**
     * Obtain JWT authentication token for API requests.
     */
    private function getAuthToken(): string
    {
        self::$client->request(
            'POST',
            '/api/login_check',
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
