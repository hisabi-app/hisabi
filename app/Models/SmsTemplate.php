<?php

namespace App\Models;

class SmsTemplate
{
    protected $body;
    protected $data;

    public function __construct(String $body, array $data)
    {
        $this->body = $body;
        $this->data = $data;
    }

    public static function make(String $body, array $data): SmsTemplate 
    {
        return new static($body, $data);
    }

    public function body() 
    {
        return $this->body;
    }

    public function data()
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            'body' => $this->body,
            'data' => $this->data,
        ];
    }
}
