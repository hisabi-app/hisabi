<?php

namespace App\Models;

class SmsTemplate
{
    protected $body;
    protected $type;
    protected $data;

    public function __construct(String $body, String $type, array $data)
    {
        $this->body = $body;
        $this->type = $type;
        $this->data = $data;
    }

    public static function make(String $body, String $type, array $data): SmsTemplate 
    {
        return new static($body, $type, $data);
    }

    public function body() 
    {
        return $this->body;
    }

    public function type()
    {
        return $this->type;
    }

    public function data()
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            'body' => $this->body,
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}
