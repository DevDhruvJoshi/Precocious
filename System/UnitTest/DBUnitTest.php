<?php

namespace System\UnitTest;

use PHPUnit\Framework\TestCase;

class DBUnitTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        // Initialize the DB connection before each test
        $this->db = new \System\Config\DB();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $this->db->Query("DROP DATABASE IF EXISTS `test_db`");
    }

    public function testCreateDatabase()
    {
        $result = $this->db->CheckDBExisted('test_db');
        $this->assertTrue($result);
    }

    public function testCreateTable()
    {
        $tableName = 'users';
        $columns = [
            'id INT PRIMARY KEY AUTO_INCREMENT',
            'name VARCHAR(255)',
            'email VARCHAR(255)',
            'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ];
        
        $result = $this->db->CreateTable($tableName, $columns);
        $this->assertTrue($result);

        // Check if the table exists
        $result = $this->db->Query("SHOW TABLES LIKE 'users'")->rowCount();
        $this->assertEquals(1, $result);
    }

    public function testInsert()
    {
        $this->db->CreateTable('users', [
            'id INT PRIMARY KEY AUTO_INCREMENT',
            'name VARCHAR(255)',
            'email VARCHAR(255)'
        ]);

        $data = ['name' => 'John Doe', 'email' => 'john.doe@example.com'];
        $insertId = $this->db->Insert('users', $data);
        
        $this->assertNotNull($insertId);
    }

    public function testUpdate()
    {
        $this->db->CreateTable('users', [
            'id INT PRIMARY KEY AUTO_INCREMENT',
            'name VARCHAR(255)',
            'email VARCHAR(255)'
        ]);

        $data = ['name' => 'John Doe', 'email' => 'john.doe@example.com'];
        $insertId = $this->db->Insert('users', $data);

        $this->db->Update('users', ['name' => 'Jane Doe'], ['id' => $insertId]);

        $result = $this->db->Select('users', ['name'], ['id' => $insertId]);
        $this->assertEquals('Jane Doe', $result[0]['name']);
    }

    public function testDelete()
    {
        $this->db->CreateTable('users', [
            'id INT PRIMARY KEY AUTO_INCREMENT',
            'name VARCHAR(255)',
            'email VARCHAR(255)'
        ]);

        $data = ['name' => 'John Doe', 'email' => 'john.doe@example.com'];
        $insertId = $this->db->Insert('users', $data);

        $this->db->Delete('users', ['id' => $insertId]);

        $result = $this->db->Select('users', ['*'], ['id' => $insertId]);
        $this->assertCount(0, $result);
    }

    public function testSelect()
    {
        $this->db->CreateTable('users', [
            'id INT PRIMARY KEY AUTO_INCREMENT',
            'name VARCHAR(255)',
            'email VARCHAR(255)'
        ]);

        $data = ['name' => 'John Doe', 'email' => 'john.doe@example.com'];
        $this->db->Insert('users', $data);

        $results = $this->db->Select('users', ['*']);
        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results[0]['name']);
    }

    public function testCustomQuery()
    {
        $this->db->CreateTable('users', [
            'id INT PRIMARY KEY AUTO_INCREMENT',
            'name VARCHAR(255)',
            'email VARCHAR(255)'
        ]);

        $data = ['name' => 'John Doe', 'email' => 'john.doe@example.com'];
        $this->db->Insert('users', $data);

        $stmt = $this->db->Query("SELECT * FROM users WHERE email = ?", ['john.doe@example.com']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results[0]['name']);
    }
}

// To run the tests, use: phpunit DBUnitTest.php
