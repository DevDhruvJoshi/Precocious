<?php

namespace System\App;

class Data { // this is a Morden Encryption even more stronger than AES

    /**
     * @var string The encryption cipher to use.
     */

    private const CIPHER = 'chacha20-poly1305'; // Example: A modern algorithm

    /**
     * Encrypts the given data using the specified cipher and key.
     *
     * @param string $data The data to be encrypted.
     * @param string $key The encryption key.
     * @return string The encrypted data as a base64-encoded string.
     */
    public function Encrypt($data, $key) {
        $nonce = random_bytes(16); // Generate a random nonce
        $encrypted_data = sodium_crypto_chacha20_poly1305_encrypt($data, $nonce, $key);

        return base64_encode($nonce . $encrypted_data);
    }

    /**
     * Decrypts the given encrypted data using the specified cipher and key.
     *
     * @param string $encryptedData The encrypted data.
     * @param string $key The encryption key.
     * @return string The decrypted data.
     */
    public function Decrypt($encryptedData, $key) {
        $encrypted_data = base64_decode($encryptedData);
        $nonce = substr($encrypted_data, 0, 16);
        $encrypted_data = substr($encrypted_data, 16);

        return sodium_crypto_chacha20_poly1305_decrypt($encrypted_data, $nonce, $key);
    }
}

class AESData {

    /**
     * @var string The encryption key used for AES operations.
     */
    private $key;

    /**
     * @var string The initialization vector (IV) used for AES operations.
     */
    private $iv;

    /**
     * Constructs a new AES object with the specified key.
     *
     * @param string $key The encryption key.
     */
    public function __construct($key) {
        $this->key = $key;
        $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    }

    /**
     * Encrypts the given data using AES-256-CBC encryption.
     *
     * @param string $data The data to be encrypted.
     * @return string The encrypted data as a base64-encoded string.
     */
    public function Encrypt($data) {
        return base64_encode(openssl_encrypt($data, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $this->iv));
    }

    /**
     * Decrypts the given encrypted data using AES-256-CBC decryption.
     *
     * @param string $encryptedData The encrypted data.
     * @return string The decrypted data.
     */
    public function Decrypt($encryptedData) {
        return openssl_decrypt(base64_decode($encryptedData), 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $this->iv);
    }
}
