<?php

class CUWallet
{
    /**
     * Current API endpoint.
     *
     * @var string
     */
    protected $endpoint = 'https://cryptounifier.io/api/v1/';

    /**
     * Wallet Key.
     *
     * @var string
     */
    protected $public;

    /**
     * Secret Key.
     *
     * @var string
     */
    protected $secret;

    /**
     * Construct keys to any request.
     */
    public function __construct(string $public, string $secret)
    {
        $this->public = $public;
        $this->secret = $secret;
    }

    /**
     * Real fee estimation.
     *
     * @return object
     */
    public function estimateFee(
        string $currency,
        string $destination,
        float $amount,
        int $fee_per_byte        = 0,
        string $payment_id       = '',
        string $token_identifier = '',
        int $token_decimals      = 18
    ) {
        return $this->_sendRequest($currency . '/estimate-fee', [
            'destination'      => $destination,
            'amount'           => $amount,
            'fee_per_byte'     => $fee_per_byte,
            'payment_id'       => $payment_id,
            'token_identifier' => $token_identifier,
            'token_decimals'   => $token_decimals,
        ]);
    }

    /**
     * Real fee estimation with multiple destinations.
     *
     * @return object
     */
    public function estimateFeeMultiple(string $currency, array $destinations, int $fee_per_byte = 0, string $token_identifier = '', int $token_decimals = 18)
    {
        return $this->_sendRequest($currency . '/estimate-fee-multiple', [
            'destinations'     => json_encode($destinations),
            'fee_per_byte'     => $fee_per_byte,
            'token_identifier' => $token_identifier,
            'token_decimals'   => $token_decimals,
        ]);
    }

    /**
     * Send currency transaction.
     *
     * @return object
     */
    public function sendTransaction(
        string $currency,
        string $destination,
        float $amount,
        int $fee_per_byte        = 0,
        string $payment_id       = '',
        string $token_identifier = '',
        int $token_decimals      = 18
    ) {
        return $this->_sendRequest($currency . '/send-transaction', [
            'destination'      => $destination,
            'amount'           => $amount,
            'fee_per_byte'     => $fee_per_byte,
            'payment_id'       => $payment_id,
            'token_identifier' => $token_identifier,
            'token_decimals'   => $token_decimals,
        ]);
    }

    /**
     * Send currency transaction with multiple destinations.
     *
     * @return object
     */
    public function sendTransactionMultiple(
        string $currency,
        array $destinations,
        int $fee_per_byte        = 0,
        string $token_identifier = '',
        int $token_decimals      = 18
    ) {
        return $this->_sendRequest($currency . '/send-transaction-multiple', [
            'destinations'     => json_encode($destinations),
            'fee_per_byte'     => $fee_per_byte,
            'token_identifier' => $token_identifier,
            'token_decimals'   => $token_decimals,
        ]);
    }

    /**
     * Recover currency private key.
     *
     * @return object
     */
    public function recoverPrivateKey(string $currency)
    {
        return $this->_sendRequest($currency . '/recover-private-key');
    }

    /**
     * Get currency transaction list.
     *
     * @return object
     */
    public function listTransaction(string $currency, int $limit = 0)
    {
        return $this->_sendRequest($currency . '/list-transactions', ['limit' => ($limit !== 0) ? $limit : null]);
    }

    /**
     * Get currency deposit address list.
     *
     * @return object
     */
    public function depositAddresses(string $currency)
    {
        return $this->_sendRequest($currency . '/deposit-addresses');
    }

    /**
     * Get currency blockchain information.
     *
     * @return object
     */
    public function blockchainInfo(string $currency)
    {
        return $this->_sendRequest($currency . '/blockchain-info');
    }

    /**
     * Validate currency address.
     *
     * @return object
     */
    public function validateAddress(string $currency, string $address)
    {
        return $this->_sendRequest($currency . '/validate-address', ['address' => $address]);
    }

    /**
     * Return key balance of a currency.
     *
     * @return object
     */
    public function balance(string $currency, string $token_identifier = '')
    {
        return $this->_sendRequest($currency . '/balance', ['token_identifier' => $token_identifier]);
    }

    /**
     * Send request to Crypto Unifier API.
     *
     * @return object
     */
    protected function _sendRequest(string $path, array $body = [])
    {
        $body = array_filter($body);

        $ch = curl_init($this->endpoint . $path);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array_merge($body, ['wallet_key' => $this->public, 'secret_key' => $this->secret])));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response == null) {
            return (object) ['status' => 503, 'message' => 'Connection failed.'];
        }

        return  json_decode((string) $response);
    }
}
